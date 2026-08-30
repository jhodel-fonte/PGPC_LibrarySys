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

        // Create physical book (available for checkout)
        $bookData = BookData::create(['book_title' => 'Test Book', 'subtitle' => 'BK']);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id, 'call_number' => 'CN-123']);
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2, // Good
            'accession_number' => 'ACC-999',
            'barcode' => 'BAR-999',
            'status' => 'available',
            'location' => 'Shelf A'
        ]);
    }

    public function test_librarian_can_access_checkout_page(): void
    {
        $response = $this->actingAs($this->librarian)->get('/admin/circulation-desk/checkout');
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
            ->assertHasNoErrors();

        $this->assertDatabaseHas('borrowing_transactions', [
            'book_id' => $this->book->id,
            'school_id' => $this->student->id,
            'return_date' => null
        ]);

        $this->assertEquals('borrowed', $this->book->fresh()->status);
    }
}

