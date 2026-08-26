<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Student;
use App\Models\BorrowingTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Confirm Return', 'activepageRoute' => 'admin.circulation-desk.index'])]
class ConfirmReturn extends Component
{
    public $studentId;
    public $transactionIds = [];
    public $student;
    public $transactions = [];

    public function mount()
    {
        $this->studentId = session('confirm_return_student_id');
        $this->transactionIds = session('confirm_return_transaction_ids', []);

        if (!$this->studentId || empty($this->transactionIds)) {
            return redirect()->route('admin.circulation-desk.return');
        }

        $this->student = Student::with('libraryStatus')->find($this->studentId);
        if (!$this->student) {
            return redirect()->route('admin.circulation-desk.return');
        }

        $this->transactions = BorrowingTransaction::with([
            'book.bookDetail.bookData',
            'book.bookDetail.bookData.authors',
            'issuedCondition'
        ])->whereIn('id', $this->transactionIds)->get();
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
        if (empty($this->transactions)) {
            return redirect()->route('admin.circulation-desk.return');
        }

        DB::transaction(function () {
            $now = Carbon::now();
            $librarianId = auth()->user() && auth()->user()->librarian ? auth()->user()->librarian->id : 1;

            foreach ($this->transactions as $transaction) {
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

        // Clean up temporary session data
        session()->forget(['confirm_return_student_id', 'confirm_return_transaction_ids']);

        // Flash message
        session()->flash('success_message', 'Books returned and transaction confirmed successfully!');

        // Redirect back to check-in return workstation page
        return $this->redirect(route('admin.circulation-desk.return'));
    }

    public function render()
    {
        return view('livewire.components.circulation.confirm-return');
    }
}
