<?php

namespace App\Livewire\Pages\Student;

use App\Models\BorrowingTransaction;
use App\Models\Fine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'Borrow'])]
class Borrow extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'active'; // 'active', 'history', 'fines'

    public function updatedSearch(): void
    {
        $this->resetPage('active_page');
        $this->resetPage('history_page');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $user = Auth::user();
        $student = $user?->student;
        $studentId = $student?->id;

        $searchTerm = trim($this->search);

        // 1. Active Loans Query
        $activeQuery = BorrowingTransaction::query()
            ->with([
                'book.bookDetail.bookData.authors',
                'issuedCondition',
                'borrowType',
            ])
            ->where('school_id', $studentId ?? 0)
            ->whereNull('return_date');

        if ($searchTerm !== '') {
            $activeQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('book.bookDetail.bookData', function ($q) use ($searchTerm) {
                    $q->where('book_title', 'like', "%{$searchTerm}%");
                })->orWhereHas('book', function ($q) use ($searchTerm) {
                    $q->where('accession_number', 'like', "%{$searchTerm}%");
                });
            });
        }

        $activeLoans = $activeQuery->orderBy('due_date', 'asc')->orderBy('id', 'asc')->cursorPaginate(10, ['*'], 'active_page');

        // 2. History Loans Query
        $historyQuery = BorrowingTransaction::query()
            ->with([
                'book.bookDetail.bookData.authors',
                'returnCondition',
                'borrowType',
            ])
            ->where('school_id', $studentId ?? 0)
            ->whereNotNull('return_date');

        if ($searchTerm !== '') {
            $historyQuery->where(function ($query) use ($searchTerm) {
                $query->whereHas('book.bookDetail.bookData', function ($q) use ($searchTerm) {
                    $q->where('book_title', 'like', "%{$searchTerm}%");
                })->orWhereHas('book', function ($q) use ($searchTerm) {
                    $q->where('accession_number', 'like', "%{$searchTerm}%");
                });
            });
        }

        $historyLoans = $historyQuery->orderBy('return_date', 'desc')->orderBy('id', 'desc')->cursorPaginate(10, ['*'], 'history_page');

        // 3. Fines Query
        $fines = $studentId
            ? Fine::with(['transaction.book.bookDetail.bookData', 'fineType'])
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        // Totals
        $activeCount = $studentId ? BorrowingTransaction::where('school_id', $studentId)->whereNull('return_date')->count() : 0;
        $overdueCount = $studentId ? BorrowingTransaction::where('school_id', $studentId)->whereNull('return_date')->where('due_date', '<', now())->count() : 0;
        $historyCount = $studentId ? BorrowingTransaction::where('school_id', $studentId)->whereNotNull('return_date')->count() : 0;
        $unpaidFinesTotal = (float) $fines->where('fine_status', 'unpaid')->sum('amount');

        return view('livewire.pages.student.borrow', [
            'student' => $student,
            'activeLoans' => $activeLoans,
            'historyLoans' => $historyLoans,
            'fines' => $fines,
            'activeCount' => $activeCount,
            'overdueCount' => $overdueCount,
            'historyCount' => $historyCount,
            'unpaidFinesTotal' => $unpaidFinesTotal,
        ]);
    }
}
