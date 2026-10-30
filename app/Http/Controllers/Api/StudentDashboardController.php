<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BorrowingTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $account = $request->user();
        $student = $account?->student;

        if (! $account || ! $account->isStudent() || ! $student) {
            return response()->json(['message' => 'Student profile not found.'], 403);
        }

        $loans = $student->borrowingTransactions()
            ->with(['book.bookDetail.bookData.authors', 'borrowType', 'issuedCondition'])
            ->whereNull('return_date')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $recentReturns = $student->borrowingTransactions()
            ->with(['book.bookDetail.bookData.authors', 'borrowType', 'returnCondition'])
            ->whereNotNull('return_date')
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'school_id_number' => $student->school_id_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'email' => $account->email,
                'program' => $student->program,
                'year_level' => $student->year_level,
                'image_url' => $student->image_url,
                'library_status' => $student->libraryStatus?->status,
                'qr_payload' => $student->school_id_number ?: (string) $student->id,
            ],
            'counts' => [
                'active_loans' => $loans->count(),
                'overdue_loans' => $loans->filter(fn ($loan) => $loan->due_date && $loan->due_date->isPast())->count(),
                'total_borrowed' => $student->borrowingTransactions()->count(),
                'active_reservations' => $student->reservations()->whereIn('reservation_status_id', [1, 2])->count(),
                'unpaid_fines' => number_format((float) $student->fines()->where('fine_status', 'unpaid')->sum('amount'), 2, '.', ''),
            ],
            'active_loans' => $loans->map(fn (BorrowingTransaction $loan) => $this->loan($loan))->values(),
            'recent_returns' => $recentReturns->map(fn (BorrowingTransaction $loan) => $this->loan($loan))->values(),
        ]);
    }

    private function loan(BorrowingTransaction $loan): array
    {
        $copy = $loan->book;
        $detail = $copy?->bookDetail;
        $data = $detail?->bookData;

        return [
            'id' => $loan->id,
            'title' => $data?->book_title,
            'authors' => $data?->authors->map(fn ($author) => trim($author->first_name.' '.$author->last_name))->values() ?? [],
            'cover_url' => $detail?->cover_url,
            'accession_number' => $copy?->accession_number,
            'borrow_type' => $loan->borrowType?->name,
            'issued_date' => $loan->issued_date?->toIso8601String(),
            'due_date' => $loan->due_date?->toIso8601String(),
            'return_date' => $loan->return_date?->toIso8601String(),
        ];
    }
}
