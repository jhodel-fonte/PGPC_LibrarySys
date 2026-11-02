<?php

namespace Tests\Feature\Circulation;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookData;
use App\Models\BorrowingTransaction;
use App\Models\LibraryStatus;
use App\Models\BookCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pages\Dashboard\CheckInBook;
use Carbon\Carbon;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    protected $librarian;
    protected $student;
    protected $book;
    protected $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed core lookups
        LibraryStatus::create(['id' => 1, 'status' => 'Active']);
        LibraryStatus::create(['id' => 2, 'status' => 'Inactive']);
        BookCondition::create(['id' => 2, 'status' => 'Good']);

        // Create Librarian Account & Profile
        $this->librarian = Account::factory()->create(['role_id' => 3]); // Librarian role
        $this->librarian->librarian()->create([
            'first_name' => 'Librarian',
            'last_name' => 'User',
            'school_id_number' => 'EMP-TEST-001',
        ]);

        // Create Student
        $this->student = Student::create([
            'account_id' => Account::factory()->create(['role_id' => 4])->id,
            'school_id_number' => '2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'library_status_id' => 1, // Active
            'program' => 'BSCS',
            'year_level' => '4'
        ]);

        // Create Book Cataloging relations
        $bookData = BookData::create(['book_title' => 'Test Book', 'subtitle' => 'BK', 'language_id' => \App\Models\Language::firstOrCreate(['lang' => 'English'])->id]);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id, 'call_number' => 'CN-123', 'format' => 'Book', 'book_type_id' => \App\Models\BookType::first()->id, 'publisher_id' => \App\Models\Publisher::first()->id]);

        // Create physical book
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2, // Good
            'accession_number' => 'ACC-999',
            'code' => 'BAR-999',
            'status' => 'borrowed',
            'location' => 'Shelf A'
        ]);

        // Create active borrowing transaction (borrowed 3 days ago, due in 4 days)
        $this->transaction = BorrowingTransaction::create([
            'book_id' => $this->book->id,
            'school_id' => $this->student->id,
            'issued_by_id' => $this->librarian->librarian->id,
            'borrow_type_id' => 1,
            'issued_condition_id' => 2,
            'issued_date' => Carbon::now()->subDays(3),
            'due_date' => Carbon::now()->addDays(4),
            'return_date' => null
        ]);
    }

    public function test_librarian_can_access_checkin_page(): void
    {
        $response = $this->actingAs($this->librarian)->get('/librarian/circulation-desk/return');
        $response->assertStatus(200)
            ->assertSee('Borrowed Items on Account')
            ->assertSee('Return Summary')
            ->assertSee('No Student Profile');
    }

    public function test_active_loan_link_loads_its_student(): void
    {
        Livewire::actingAs($this->librarian)->withQueryParams(['loan' => $this->transaction->id])
            ->test(CheckInBook::class)
            ->assertSet('scannedMember.id', $this->student->id)
            ->assertCount('borrowedBooks', 1)
            ->assertCount('returnedBooks', 0);
    }

    public function test_returned_loan_link_does_not_load_a_student(): void
    {
        $this->transaction->update(['return_date' => now()]);
        Livewire::actingAs($this->librarian)->withQueryParams(['loan' => $this->transaction->id])
            ->test(CheckInBook::class)
            ->assertSet('scannedMember', null)
            ->assertSee('This active loan or its student could not be found');
    }

    public function test_scan_student_loads_profile_and_borrowed_books(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.school_id', '2026-0001')
            ->assertSet('scannedMember.name', 'John Doe')
            ->assertCount('borrowedBooks', 1);
    }

    public function test_overdue_days_are_whole_calendar_days(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));
        $this->transaction->update(['due_date' => now()->subDays(3)->addHours(2)]);
        Livewire::actingAs($this->librarian)->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('borrowedBooks.0.overdue_days', 3);
    }

    public function test_return_failure_hides_database_details_and_rolls_back(): void
    {
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            if (str_contains(strtolower($query->sql), 'update "borrowing_transactions"')) {
                throw new \RuntimeException('secret SQL database details');
            }
        });

        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)->call('confirmReturn')
            ->assertSet('errorMessage', 'The return could not be completed. No changes were saved. Please try again or contact the system administrator.')
            ->assertDontSee('secret SQL database details')->assertDispatched('return-failed');

        $this->assertNull($this->transaction->fresh()->return_date);
        $this->assertSame('borrowed', $this->book->fresh()->status);
    }

    public function test_scan_book_adds_to_returned_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('returnedBooks', 1)
            ->assertSet('lastReturnedBook.accession', 'ACC-999');
    }

    public function test_undo_return_removes_book_from_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('returnedBooks', 1)
            ->call('undoReturn', 'ACC-999')
            ->assertCount('returnedBooks', 0)
            ->assertSet('lastReturnedBook.is_returned', false);
    }

    public function test_return_review_uses_librarian_route(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('reviewReturn')
            ->assertRedirect(route('librarian.circulation-desk.return.confirm'));
    }

    public function test_confirming_return_records_it_and_releases_copy(): void
    {
        session([
            'confirm_return_student_id' => $this->student->id,
            'confirm_return_transaction_ids' => [$this->transaction->id],
        ]);

        Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)
            ->call('confirmReturn')
            ->assertDispatched('return-completed', redirectUrl: route('librarian.circulation-desk.return'));

        $this->assertNotNull($this->transaction->fresh()->return_date);
        $this->assertSame(2, $this->transaction->fresh()->return_condition_id);
        $this->assertSame('available', $this->book->fresh()->status);
    }

    public function test_late_inside_library_return_has_no_fine(): void
    {
        $type = \App\Models\BorrowType::firstOrCreate(['name' => 'Room Use Only']);
        $this->transaction->update(['borrow_type_id' => $type->id, 'due_date' => now()->subDays(3)]);
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);

        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->assertSet('totalFine', 0)
            ->set('returnConditions.'.$this->transaction->id, 2)
            ->call('confirmReturn')
            ->assertDispatched('return-completed');

        $this->assertNotNull($this->transaction->fresh()->return_date);
        $this->assertDatabaseMissing('fines', ['borrowing_transaction_id' => $this->transaction->id]);
    }

    public function test_reservation_blacklist_does_not_prevent_return(): void
    {
        $this->student->account->forceFill(['reservations_blocked' => true])->save();
        Livewire::actingAs($this->librarian)->test(CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.reservations_blocked', true)
            ->call('handleSearchCode', 'BAR-999')
            ->call('reviewReturn')
            ->assertRedirect(route('librarian.circulation-desk.return.confirm'));
    }

    public function test_return_confirmation_rejects_a_loan_from_another_student(): void
    {
        $other = Student::create([
            'account_id' => Account::factory()->create(['role_id' => 4])->id,
            'school_id_number' => '2026-0002', 'first_name' => 'Jane', 'last_name' => 'Doe', 'library_status_id' => 1,
        ]);
        session(['confirm_return_student_id' => $other->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);

        Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)
            ->call('confirmReturn')
            ->assertDispatched('return-failed');

        $this->assertNull($this->transaction->fresh()->return_date);
        $this->assertSame('borrowed', $this->book->fresh()->status);
    }

    public function test_damaged_return_records_condition_and_is_not_available(): void
    {
        $damaged = BookCondition::firstOrCreate(['status' => 'Damaged']);
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, $damaged->id)
            ->set('returnNotes.'.$this->transaction->id, 'Back cover torn')
            ->call('confirmReturn')->assertDispatched('return-completed');
        $this->assertSame($damaged->id, $this->transaction->fresh()->return_condition_id);
        $this->assertSame('Back cover torn', $this->transaction->fresh()->return_condition_note);
        $this->assertSame($damaged->id, $this->book->fresh()->book_condition_id);
        $this->assertSame('damaged', $this->book->fresh()->status);
    }

    public function test_damaged_return_requires_a_comment_and_saves_nothing_on_failure(): void
    {
        $damaged = BookCondition::firstOrCreate(['status' => 'Damaged']);
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, $damaged->id)
            ->set('returnNotes.'.$this->transaction->id, '   ')
            ->call('confirmReturn')->assertHasErrors(['returnNotes.'.$this->transaction->id]);
        $this->assertNull($this->transaction->fresh()->return_condition_note);
        $this->assertNull($this->transaction->fresh()->return_date);
        $this->assertSame('borrowed', $this->book->fresh()->status);
    }

    public function test_good_return_allows_an_optional_comment_and_hides_it_from_serialization(): void
    {
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)
            ->set('returnNotes.'.$this->transaction->id, 'Minor scuff')
            ->call('confirmReturn')->assertDispatched('return-completed');
        $this->assertSame('Minor scuff', $this->transaction->fresh()->return_condition_note);
        $this->assertArrayNotHasKey('return_condition_note', $this->transaction->fresh()->toArray());
    }

    public function test_multiple_return_comments_stay_with_their_own_loans(): void
    {
        $secondBook = $this->book->replicate();
        $secondBook->accession_number = 'ACC-997';
        $secondBook->code = 'BAR-997';
        $secondBook->save();
        $secondLoan = $this->transaction->replicate();
        $secondLoan->book_id = $secondBook->id;
        $secondLoan->save();
        $damaged = BookCondition::firstOrCreate(['status' => 'Damaged']);
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id, $secondLoan->id]]);

        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)
            ->set('returnNotes.'.$this->transaction->id, 'Minor scuff')
            ->set('returnConditions.'.$secondLoan->id, $damaged->id)
            ->set('returnNotes.'.$secondLoan->id, 'Torn cover')
            ->call('confirmReturn')->assertDispatched('return-completed');
        $this->assertSame('Minor scuff', $this->transaction->fresh()->return_condition_note);
        $this->assertSame('Torn cover', $secondLoan->fresh()->return_condition_note);
    }

    public function test_return_requires_a_valid_condition(): void
    {
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id]]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->call('confirmReturn')->assertHasErrors(['returnConditions']);
        $this->assertNull($this->transaction->fresh()->return_date);
        $this->assertSame('borrowed', $this->book->fresh()->status);
    }

    public function test_active_loan_cannot_be_made_available_by_editing_the_copy(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->book->validateCopyState(2, 'available');
    }

    public function test_multiple_returns_require_a_condition_for_each_copy(): void
    {
        $secondBook = $this->book->replicate();
        $secondBook->accession_number = 'ACC-998';
        $secondBook->code = 'BAR-998';
        $secondBook->save();
        $secondLoan = $this->transaction->replicate();
        $secondLoan->book_id = $secondBook->id;
        $secondLoan->save();
        session(['confirm_return_student_id' => $this->student->id, 'confirm_return_transaction_ids' => [$this->transaction->id, $secondLoan->id]]);

        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->set('returnConditions.'.$this->transaction->id, 2)->call('confirmReturn')
            ->assertHasErrors(['returnConditions.'.$secondLoan->id]);
        $this->assertNull($this->transaction->fresh()->return_date);
        $this->assertNull($secondLoan->fresh()->return_date);
    }

    public function test_damaged_copy_cannot_be_marked_available(): void
    {
        $damaged = BookCondition::firstOrCreate(['status' => 'Damaged']);
        $this->transaction->update(['return_date' => now()]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->book->validateCopyState($damaged->id, 'available');
    }
}
