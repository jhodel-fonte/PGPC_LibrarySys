<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\BookManager;
use App\Models\Account;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BorrowingTransaction;
use App\Models\Language;
use App\Models\BookType;
use App\Models\Publisher;
use App\Models\Student;
use App\Models\LibraryStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemModuleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Account $admin;
    private Account $studentAccount;
    private Student $student;
    private Book $book;
    private BorrowingTransaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Account::factory()->create(['role_id' => 1]);
        $this->admin->librarian()->create([
            'school_id_number' => 'EMP-TEST-001',
            'first_name' => 'System',
            'last_name' => 'Administrator',
        ]);

        $this->studentAccount = Account::factory()->create(['role_id' => 4]);
        $this->student = Student::create([
            'account_id' => $this->studentAccount->id,
            'school_id_number' => '2026-TEST-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'library_status_id' => LibraryStatus::query()->value('id') ?? 1,
            'program' => 'BSCS',
            'year_level' => '4th Year',
        ]);

        $bookData = BookData::create([
            'book_title' => 'Workflow Test Book',
            'subtitle' => 'TEST',
            'language_id' => Language::query()->value('id') ?? 1,
        ]);
        $bookDetail = BookDetail::create([
            'book_data_id' => $bookData->id,
            'call_number' => 'QA-001',
            'format' => 'Print',
            'book_type_id' => BookType::query()->value('id') ?? 1,
            'publisher_id' => Publisher::query()->value('id') ?? 1,
        ]);
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => BookCondition::query()->value('id') ?? 1,
            'accession_number' => 'ACC-WORKFLOW-001',
            'code' => 'BAR-WORKFLOW-001',
            'status' => 'borrowed',
            'location' => 'QA Shelf',
        ]);

        $librarian = $this->admin->librarian;
        $this->transaction = BorrowingTransaction::create([
            'book_id' => $this->book->id,
            'school_id' => $this->student->id,
            'issued_by_id' => $librarian->id,
            'borrow_type_id' => 1,
            'issued_condition_id' => BookCondition::query()->value('id') ?? 1,
            'issued_date' => Carbon::now()->subDays(2),
            'due_date' => Carbon::now()->addDays(5),
            'return_date' => null,
        ]);
    }

    public function test_guest_is_redirected_from_protected_system_modules(): void
    {
        $this->get(route('admin.book-management.index'))
            ->assertRedirect(route('employee.login'));

        $this->get(route('admin.circulation-desk.borrow'))
            ->assertRedirect(route('employee.login'));
    }

    public function test_admin_can_open_the_core_management_modules(): void
    {
        $this->actingAs($this->admin);

        foreach ([
            'admin.dashboard',
            'admin.user-management',
            'admin.book-management.index',
            'admin.circulation-desk.index',
            'admin.circulation-desk.borrow',
            'admin.settings',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_catalog_copy_can_be_modified_from_the_component(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('editCopy', $this->book->id)
            ->set('editCode', 'BAR-WORKFLOW-UPDATED')
            ->set('editLocation', 'Updated Shelf')
            ->set('editConditionId', $this->book->book_condition_id)
            ->set('editStatus', 'available')
            ->call('saveCopy')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
            'code' => 'BAR-WORKFLOW-UPDATED',
            'location' => 'Updated Shelf',
            'status' => 'available',
        ]);
    }

    public function test_catalog_delete_action_dispatches_confirmation_request(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BookManager::class)
            ->call('deleteCopy', $this->book->id)
            ->assertDispatched('request-catalog-delete', type: 'title', ids: [$this->book->book_detail_id]);
    }

    public function test_return_confirmation_marks_transaction_and_copy_available(): void
    {
        $this->actingAs($this->admin)->withSession([
            'confirm_return_student_id' => $this->student->id,
            'confirm_return_transaction_ids' => [$this->transaction->id],
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Pages\Dashboard\ConfirmReturn::class)
            ->call('confirmReturn')
            ->assertHasNoErrors();

        $this->assertNotNull($this->transaction->fresh()->return_date);
        $this->assertSame('available', $this->book->fresh()->status);
        $this->assertDatabaseHas('borrowing_transactions', [
            'id' => $this->transaction->id,
            'received_by_id' => $this->admin->librarian->id,
        ]);
    }
}
