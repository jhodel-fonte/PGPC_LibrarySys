<?php

namespace App\Livewire\Pages\Student;

use App\Models\BorrowingTransaction;
use App\Models\Fine;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Linkxtr\QrCode\Facades\QrCode;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Find student profile for authenticated user, with fallback for admin/staff preview
        $student = $user?->student ?? Student::with(['libraryStatus', 'account'])->first();
        $studentId = $student?->id;
        $studentIdValue = $student?->school_id_number ?: ($student?->id ? (string) $student->id : null);

        $qrCodeSvg = null;
        if ($studentIdValue) {
            try {
                $qrCodeSvg = \App\Services\QrCodeServices::svg($studentIdValue);
            } catch (\Throwable $e) {
                $qrCodeSvg = null;
            }
        }

        // Active loans (books currently checked out: return_date is null)
        $activeLoans = $studentId
            ? BorrowingTransaction::with([
                    'book.bookDetail.bookData.authors',
                    'issuedCondition',
                    'borrowType',
                ])
                ->where('school_id', $studentId)
                ->whereNull('return_date')
                ->orderBy('due_date', 'asc')
                ->get()
            : collect();

        // Count metrics
        $activeCount = $activeLoans->count();
        $overdueLoans = $activeLoans->filter(fn ($loan) => $loan->due_date && $loan->due_date->isPast());
        $overdueCount = $overdueLoans->count();

        $totalBorrowed = $studentId
            ? BorrowingTransaction::where('school_id', $studentId)->count()
            : 0;

        $unpaidFines = $studentId
            ? (float) Fine::where('student_id', $studentId)
                ->whereNull('paid_at')
                ->sum('amount')
            : 0.00;

        // Recent borrowing history (last 5 returned transactions)
        $recentTransactions = $studentId
            ? BorrowingTransaction::with([
                    'book.bookDetail.bookData.authors',
                    'returnCondition',
                    'borrowType',
                ])
                ->where('school_id', $studentId)
                ->whereNotNull('return_date')
                ->orderBy('return_date', 'desc')
                ->take(5)
                ->get()
            : collect();

        $activeReservationsCount = $studentId
            ? \App\Models\BookReservation::where('student_id', $studentId)
                ->whereIn('reservation_status_id', [1, 2])
                ->count()
            : 0;

        return view('livewire.pages.student.dashboard', [
            'user' => $user,
            'student' => $student,
            'studentIdValue' => $studentIdValue,
            'qrCodeSvg' => $qrCodeSvg,
            'activeLoans' => $activeLoans,
            'activeCount' => $activeCount,
            'overdueCount' => $overdueCount,
            'totalBorrowed' => $totalBorrowed,
            'unpaidFines' => $unpaidFines,
            'activeReservationsCount' => $activeReservationsCount,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
