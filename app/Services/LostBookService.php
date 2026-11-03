<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BorrowingTransaction;
use App\Models\Fine;
use App\Models\Librarian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class LostBookService
{
    public function report(int $studentId, int $loanId, Librarian $librarian, string $note): array
    {
        if (trim($note) === '' || mb_strlen($note) > 500) {
            throw new \DomainException('Enter a report note of up to 500 characters.');
        }

        return AuditTrail::transaction('Book reported lost', function () use ($studentId, $loanId, $librarian, $note) {
            Student::lockForUpdate()->findOrFail($studentId);
            $loan = BorrowingTransaction::with(['book.bookDetail', 'borrowType'])->where('school_id', $studentId)
                ->whereNull('return_date')->whereNull('lost_at')->lockForUpdate()->findOrFail($loanId);
            $book = Book::lockForUpdate()->findOrFail($loan->book_id);
            $conditionId = BookCondition::where('status', 'Lost')->value('id');
            if (! $conditionId) {
                throw new \DomainException('The Lost book condition is not configured.');
            }
            $now = now();
            $amount = $loan->isInsideLibrary() ? 0 : app(FineService::class)->overdueAmount($loan->due_date, $loan->book?->bookDetail);
            $loan->update(['lost_at' => $now, 'lost_reported_by_id' => $librarian->id, 'lost_note' => trim($note)]);
            $book->update(['status' => 'lost', 'book_condition_id' => $conditionId]);
            $outcomes = app(ReservationWaitlistService::class)->handleLostReservations($book);
            app(ReservationWaitlistService::class)->suspendUnavailable($book->book_detail_id);
            if ($amount > 0) {
                $type = DB::table('fine_types')->where('name', 'Overdue')->value('id');
                if (! $type) {
                    throw new \DomainException('The Overdue fine type is not configured.');
                }
                Fine::create(['borrowing_transaction_id' => $loan->id, 'student_id' => $studentId,
                    'fine_type_id' => $type, 'fine_due_date' => $now->copy()->addDays(7), 'amount' => $amount,
                    'note' => 'Overdue charges through the lost report date.', 'fine_status' => 'unpaid']);
            }
            return $outcomes;
        });
    }

    public function resolve(int $studentId, int $loanId, Librarian $librarian, string $note, ?int $conditionId = null): void
    {
        if (trim($note) === '' || mb_strlen($note) > 500) {
            throw new \DomainException('Enter a resolution note of up to 500 characters.');
        }
        AuditTrail::transaction('Lost book case resolved', function () use ($studentId, $loanId, $librarian, $note, $conditionId) {
            Student::lockForUpdate()->findOrFail($studentId);
            $loan = BorrowingTransaction::where('school_id', $studentId)->whereNotNull('lost_at')
                ->lockForUpdate()->findOrFail($loanId);
            if ($loan->lost_resolved_at && ($conditionId === null || $loan->return_date)) {
                throw new \DomainException('This lost-book case has already been resolved.');
            }
            $updates = $loan->lost_resolved_at ? [] : ['lost_resolved_at' => now(), 'lost_resolved_by_id' => $librarian->id, 'lost_resolution_note' => trim($note)];
            if ($conditionId !== null) {
                $condition = BookCondition::whereIn('status', ['New', 'Good', 'Fair', 'Damaged'])->findOrFail($conditionId);
                $book = Book::lockForUpdate()->findOrFail($loan->book_id);
                if ($book->status !== 'lost') {
                    throw new \DomainException('The copy is no longer marked lost. Review its record.');
                }
                $updates += ['return_date' => now(), 'received_by_id' => $librarian->id, 'return_condition_id' => $condition->id, 'return_condition_note' => trim($note)];
                $loan->update($updates);
                $book->update(['status' => $condition->status === 'Damaged' ? 'damaged' : 'available', 'book_condition_id' => $condition->id]);
            } else {
                $loan->update($updates);
            }
        });
    }
}
