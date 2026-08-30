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
            'employee_id_number' => 'EMP-001',
        ]);

        // Create Student
        $this->student = Student::create([
            'school_id_number' => '2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'library_status_id' => 1, // Active
            'program' => 'BSCS',
            'year_level' => '4'
        ]);

        // Create Book Cataloging relations
        $bookData = BookData::create(['book_title' => 'Test Book', 'subtitle' => 'BK']);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id, 'call_number' => 'CN-123']);

        // Create physical book
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2, // Good
            'accession_number' => 'ACC-999',
            'barcode' => 'BAR-999',
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
        $response = $this->actingAs($this->librarian)->get('/admin/circulation-desk/return');
        $response->assertStatus(200);
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
            ->assertNull('lastReturnedBook');
    }
}

