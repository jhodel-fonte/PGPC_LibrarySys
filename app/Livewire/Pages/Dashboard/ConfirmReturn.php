<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\BorrowingTransaction;
use App\Models\Librarian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Services\EmployeeRoleService;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Confirm Return', 'activepageRoute' => 'admin.circulation-desk.index'])]
class ConfirmReturn extends Component
{
    public $studentId;
    public $transactionIds = [];
    public $student;

    // Mapped properties for display
    public $transactionsData = [];
    public $totalFine = 0.00;
    public $overdueCount = 0;
    public ?string $errorMessage = null;

    public function mount()
    {
        EmployeeRoleService::authorizeReturn('Unauthorized action. You do not have permission to confirm book returns.');

        $this->studentId = session('confirm_return_student_id');
        $this->transactionIds = session('confirm_return_transaction_ids', []);

        $prefix = EmployeeRoleService::getRoutePrefix();

        if (!$this->studentId || empty($this->transactionIds)) {
            return $this->redirect(route($prefix . 'circulation-desk.return'), navigate: true);
        }

        $this->student = Student::with('libraryStatus')->find($this->studentId);
        if (!$this->student) {
            return $this->redirect(route($prefix . 'circulation-desk.return'), navigate: true);
        }

        $transactions = BorrowingTransaction::with([
            'book' => fn ($q) => $q->withTrashed(),
            'book.bookDetail' => fn ($q) => $q->withTrashed(),
            'book.bookDetail.bookData',
            'book.bookDetail.bookData.authors',
            'issuedCondition'
        ])->where('school_id', $this->studentId)->whereNull('return_date')->whereIn('id', $this->transactionIds)->get();

        $this->totalFine = 0.00;
        $this->overdueCount = 0;
        $this->transactionsData = [];

        foreach ($transactions as $t) {
            $book = $t->book;
            $detail = $book ? $book->bookDetail : null;
            $data = $detail ? $detail->bookData : null;

            $authorName = 'Unknown Author';
            if ($data && $data->authors->isNotEmpty()) {
                $authorName = $data->authors->map(function($a) {
                    return trim($a->first_name . ' ' . $a->last_name);
                })->implode(', ');
            }

            $overdueDays = $this->getOverdueDays($t->due_date);
            $fineAmount = $this->getFineAmount($t->due_date, $detail);

            if ($fineAmount > 0) {
                $this->overdueCount++;
                $this->totalFine += $fineAmount;
            }

            $this->transactionsData[] = [
                'id' => $t->id,
                'title' => $data ? $data->book_title : 'Unknown Book',
                'author' => $authorName,
                'accession' => $book ? $book->accession_number : 'N/A',
                'cover_image' => $detail ? $detail->cover_image : null,
                'cover_url' => $detail ? $detail->cover_url : null,
                'issued_date' => $t->issued_date?->format('M d, Y') ?? 'N/A',
                'due_date' => $t->due_date?->format('M d, Y') ?? 'N/A',
                'overdue_days' => $overdueDays,
                'fine_amount' => $fineAmount,
            ];
        }
    }

    public function getOverdueDays($dueDate)
    {
        return app(\App\Services\FineService::class)->overdueDays($dueDate);
    }

    public function getFineAmount($dueDate, ?\App\Models\BookDetail $detail = null)
    {
        return app(\App\Services\FineService::class)->overdueAmount($dueDate, $detail);
    }

    public function confirmReturn()
    {
        EmployeeRoleService::authorizeReturn('Unauthorized action. You do not have permission to confirm book returns.');
        $this->errorMessage = null;
        $prefix = EmployeeRoleService::getRoutePrefix();
        $returnUrl = route($prefix . 'circulation-desk.return');

        if (empty($this->transactionIds)) {
            return $this->redirect($returnUrl, navigate: true);
        }

        try {
            DB::transaction(function () {
                $now = Carbon::now();
                $librarianId = auth()->user()?->librarian?->id ?? Librarian::first()?->id ?? null;

                $transactions = BorrowingTransaction::with(['book' => fn ($q) => $q->withTrashed(), 'book.bookDetail' => fn ($q) => $q->withTrashed()])
                    ->where('school_id', $this->studentId)
                    ->whereIn('id', $this->transactionIds)
                    ->whereNull('return_date')
                    ->lockForUpdate()
                    ->get();

                if ($transactions->count() !== count(array_unique($this->transactionIds))) {
                    throw new \RuntimeException('Some selected items were already returned or do not belong to this student. Please review the return again.');
                }

                // Safely resolve Overdue fine type ID
                $overdueFineTypeId = DB::table('fine_types')->where('name', 'Overdue')->value('id')
                    ?? DB::table('fine_types')->value('id')
                    ?? 1;

                foreach ($transactions as $transaction) {
                    // Update borrowing transaction in database
                    $transaction->update([
                        'return_date' => $now,
                        'received_by_id' => $librarianId,
                    ]);

                    // Update physical book copy status to available
                    if ($transaction->book) {
                        $transaction->book->update([
                            'status' => 'available',
                        ]);
                    }

                    // Generate overdue fine record if applicable
                    $fineAmount = $this->getFineAmount($transaction->due_date, $transaction->book?->bookDetail);
                    if ($fineAmount > 0) {
                        DB::table('fines')->insert([
                            'borrowing_transaction_id' => $transaction->id,
                            'student_id' => $this->studentId,
                            'fine_type_id' => $overdueFineTypeId,
                            'fine_due_date' => (clone $now)->addDays(7),
                            'amount' => $fineAmount,
                            'note' => 'Overdue fine accumulated for ' . $this->getOverdueDays($transaction->due_date) . ' days.',
                            'fine_status' => 'unpaid',
                            'paid_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Return Database Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->errorMessage = 'A database error occurred during return processing: ' . $e->getMessage();
            $this->dispatch('return-failed', message: $this->errorMessage);
            return;
        }

        // Clean up temporary session data
        session()->forget(['confirm_return_student_id', 'confirm_return_transaction_ids']);

        // Flash message
        session()->flash('success_message', 'Books returned and transaction confirmed successfully!');

        // Dispatch return-completed event to show the frontend countdown success modal
        $this->dispatch('return-completed', redirectUrl: $returnUrl);
    }

    public function render()
    {
        $prefix = EmployeeRoleService::getRoutePrefix();
        return view('livewire.pages.dashboard.confirm-return', [
            'returnUrl' => route($prefix . 'circulation-desk.return'),
        ]);
    }
}
