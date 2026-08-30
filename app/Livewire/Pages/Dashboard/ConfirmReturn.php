<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\BorrowingTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Layout('layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Confirm Return', 'activepageRoute' => 'admin.circulation-desk.index'])]
class ConfirmReturn extends Component
{
    public $studentId;
    public $transactionIds = [];
    public $student;

    // Mapped properties for display
    public $transactionsData = [];
    public $totalFine = 0.00;
    public $overdueCount = 0;

    public function mount()
    {
        $this->studentId = session('confirm_return_student_id');
        $this->transactionIds = session('confirm_return_transaction_ids', []);

        if (!$this->studentId || empty($this->transactionIds)) {
            return $this->redirect(route('admin.circulation-desk.return'), navigate: true);
        }

        $this->student = Student::with('libraryStatus')->find($this->studentId);
        if (!$this->student) {
            return $this->redirect(route('admin.circulation-desk.return'), navigate: true);
        }

        $transactions = BorrowingTransaction::with([
            'book.bookDetail.bookData',
            'book.bookDetail.bookData.authors',
            'issuedCondition'
        ])->whereIn('id', $this->transactionIds)->get();

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
            $fineAmount = $this->getFineAmount($t->due_date);

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
                'issued_date' => $t->issued_date->format('M d, Y'),
                'due_date' => $t->due_date->format('M d, Y'),
                'overdue_days' => $overdueDays,
                'fine_amount' => $fineAmount,
            ];
        }
    }

    public function getOverdueDays($dueDate)
    {
        $due = Carbon::parse($dueDate);
        if ($due->isPast()) {
            return $due->diffInDays(Carbon::now());
        }
        return 0;
    }

    public function getFineAmount($dueDate)
    {
        $overdueDays = $this->getOverdueDays($dueDate);
        return $overdueDays * 20.00; // 20 PHP fine per day
    }

    public function confirmReturn()
    {
        if (empty($this->transactionIds)) {
            return redirect()->route('admin.circulation-desk.return');
        }

        // Backend duplicate check: if transactions are already marked as returned, do not process again
        $alreadyProcessed = BorrowingTransaction::whereIn('id', $this->transactionIds)
            ->whereNotNull('return_date')
            ->exists();

        if ($alreadyProcessed) {
            session()->forget(['confirm_return_student_id', 'confirm_return_transaction_ids']);
            session()->flash('success_message', 'Books returned successfully (already processed).');
            $this->dispatch('return-completed', redirectUrl: route('admin.circulation-desk.return'));
            return;
        }

        try {
            DB::transaction(function () {
                $now = Carbon::now();
                $librarianId = auth()->user() && auth()->user()->librarian ? auth()->user()->librarian->id : 1;

                $transactions = BorrowingTransaction::with('book')
                    ->whereIn('id', $this->transactionIds)
                    ->get();

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
                    $fineAmount = $this->getFineAmount($transaction->due_date);
                    if ($fineAmount > 0) {
                        DB::table('fines')->insert([
                            'borrowing_transaction_id' => $transaction->id,
                            'student_id' => $this->studentId,
                            'fine_type_id' => 1, // Overdue fine type
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
            Log::error('Return Database Error: ' . $e->getMessage());
            $this->dispatch('return-failed', message: 'A database error occurred during return processing. Please try again.');
            return;
        }

        // Clean up temporary session data
        session()->forget(['confirm_return_student_id', 'confirm_return_transaction_ids']);

        // Flash message
        session()->flash('success_message', 'Books returned and transaction confirmed successfully!');

        // Dispatch return-completed event to show the frontend countdown success modal
        $this->dispatch('return-completed', redirectUrl: route('admin.circulation-desk.return'));
    }

    public function render()
    {
        return view('livewire.pages.dashboard.confirm-return');
    }
}
