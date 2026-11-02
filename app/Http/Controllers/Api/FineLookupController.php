<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class FineLookupController extends Controller
{
    public function student(string $schoolId): JsonResponse
    {
        $student = Student::where('school_id_number', $schoolId)->firstOrFail();
        $fines = $student->fines()->with(['student', 'fineType', 'transaction.book.bookDetail.bookData'])->orderBy('id')->get();

        return response()->json(['data' => $fines->map(fn (Fine $fine) => $this->details($fine))]);
    }

    public function show(Fine $fine): JsonResponse
    {
        $fine->load(['student', 'fineType', 'transaction.book.bookDetail.bookData']);

        return response()->json(['data' => $this->details($fine)]);
    }

    private function details(Fine $fine): array
    {
        return [
            'id' => $fine->id,
            'student_id' => $fine->student?->school_id_number,
            'loan_id' => $fine->borrowing_transaction_id,
            'book_title' => $fine->transaction?->book?->bookDetail?->bookData?->book_title,
            'accession_number' => $fine->transaction?->book?->accession_number,
            'type' => $fine->fineType?->name,
            'due_date' => $fine->transaction?->due_date?->toIso8601String(),
            'return_date' => $fine->transaction?->return_date?->toIso8601String(),
            'fine_due_date' => $fine->fine_due_date?->toDateString(),
            'amount' => $fine->amount,
            'status' => $fine->fine_status,
            'paid_at' => $fine->paid_at?->toIso8601String(),
        ];
    }
}
