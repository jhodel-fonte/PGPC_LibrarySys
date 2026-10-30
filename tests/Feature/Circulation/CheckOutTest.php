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
use App\Livewire\Pages\Dashboard\CheckOutBook;
use Carbon\Carbon;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    protected $librarian;
    protected $student;
    protected $book;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed core lookups
        LibraryStatus::create(['id' => 1, 'status' => 'Active']);
        LibraryStatus::create(['id' => 2, 'status' => 'Inactive']);
        BookCondition::create(['id' => 2, 'status' => 'Good']);

        // Create Librarian
        $this->librarian = Account::factory()->create(['role_id' => 3]);
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

        // Create physical book (available for checkout)
        $bookData = BookData::create(['book_title' => 'Test Book', 'subtitle' => 'BK', 'language_id' => \App\Models\Language::firstOrCreate(['lang' => 'English'])->id]);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id, 'call_number' => 'CN-123', 'format' => 'Book', 'book_type_id' => \App\Models\BookType::first()->id, 'publisher_id' => \App\Models\Publisher::first()->id]);
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2, // Good
            'accession_number' => 'ACC-999',
            'code' => 'BAR-999',
            'status' => 'available',
            'location' => 'Shelf A'
        ]);
    }

    public function test_librarian_can_access_checkout_page(): void
    {
        $response = $this->actingAs($this->librarian)->get('/librarian/circulation-desk/borrow');
        $response->assertStatus(200);
    }

    public function test_scan_student_loads_profile_for_checkout(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.school_id', '2026-0001')
            ->assertSet('scannedMember.name', 'John Doe');
    }

    public function test_scan_available_book_adds_to_checkout_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->assertSet('checkoutBooks.0.accession', 'ACC-999');
    }

    public function test_cannot_checkout_unavailable_book(): void
    {
        $this->book->update(['status' => 'borrowed']);

        $component = Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999');

        $component->assertCount('checkoutBooks', 0);
        $this->assertStringContainsString('not available', $component->get('errorMessage'));
    }

    public function test_remove_book_from_checkout_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->call('removeCheckoutBook', 'ACC-999')
            ->assertCount('checkoutBooks', 0);
    }

    public function test_confirm_checkout_transaction_creates_records(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertHasNoErrors()
            ->assertDispatched('checkout-completed', redirectUrl: route('librarian.circulation-desk.index'));

        $this->assertDatabaseHas('borrowing_transactions', [
            'book_id' => $this->book->id,
            'school_id' => $this->student->id,
            'return_date' => null
        ]);

        $this->assertEquals('borrowed', $this->book->fresh()->status);
    }

    public function test_checkout_rejects_copy_with_active_loan_even_if_status_is_available(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');

        $this->book->fresh()->update(['status' => 'available']);

        Student::create([
            'account_id' => Account::factory()->create(['role_id' => 4])->id,
            'school_id_number' => '2026-0002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'library_status_id' => 1,
            'program' => 'BSCS',
            'year_level' => '4',
        ]);

        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0002')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->call('confirmCheckout')
            ->assertDispatched('checkout-failed')
            ->assertNotDispatched('checkout-completed');

        $this->assertSame(1, BorrowingTransaction::where('book_id', $this->book->id)->whereNull('return_date')->count());
    }
    public function test_staff_cannot_reserve_reference_only_issue(): void
    {
        $this->book->bookDetail->update(['catalog_kind' => 'journal']);
        $this->book->bookDetail->periodicalIssue()->create(['borrowable' => false]);
        Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->set('createSelectedStudentId', $this->student->id)
            ->set('createSelectedBookId', $this->book->id)
            ->call('submitManualReservation')
            ->assertDispatched('toast', fn ($event, $params) => str_contains($params['message'], 'library use only'));
        $this->assertDatabaseMissing('book_reservations', ['book_id' => $this->book->id]);
    }

    public function test_reference_issue_can_be_borrowed_inside_library_until_closing(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 10:00', 'Asia/Manila'));
        $this->book->bookDetail->periodicalIssue()->create(['borrowable' => false]);

        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->set('loanLocation', 'inside_library')
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');

        $loan = BorrowingTransaction::where('book_id', $this->book->id)->firstOrFail();
        $this->assertTrue($loan->isInsideLibrary());
        $this->assertSame('2026-10-30 19:00', $loan->due_date->copy()->setTimezone('Asia/Manila')->format('Y-m-d H:i'));
    }

    public function test_reservation_blacklist_does_not_prevent_checkout(): void
    {
        $this->student->account->forceFill(['reservations_blocked' => true])->save();
        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.reservations_blocked', true)
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');
    }

    public function test_cancelling_fulfilled_reservation_does_not_release_borrowed_copy(): void
    {
        $this->book->update(['status' => 'borrowed']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 3,
            'reservation_date' => now()->subDay(), 'due_date' => now()->addDay(),
        ]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->set('cancelReservationId', $reservation->id)
            ->call('confirmCancelReservation')
            ->assertDispatched('toast', fn ($event, $params) => str_contains($params['message'], 'Only active reservations'));
        $this->assertSame('borrowed', $this->book->fresh()->status);
        $this->assertSame(3, (int) $reservation->fresh()->reservation_status_id);
    }
}
