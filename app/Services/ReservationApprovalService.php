<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReservation;
use Illuminate\Support\Facades\DB;

class ReservationApprovalService
{
    public function approve(int $id): void
    {
        $reservation = \App\Services\AuditTrail::transaction('Reservation approved', function () use ($id) {
            $reservation = BookReservation::lockForUpdate()->findOrFail($id);
            $book = Book::withTrashed()->with(['bookDetail' => fn ($q) => $q->withTrashed(), 'bookDetail.bookData' => fn ($q) => $q->withTrashed()])->lockForUpdate()->find($reservation->book_id);
            if ((int) $reservation->reservation_status_id !== 1) {
                throw new \RuntimeException('Only pending reservations can be approved.');
            }
            if (! $reservation->due_date || $reservation->due_date->isPast()) {
                throw new \RuntimeException('The pickup deadline has passed.');
            }
            if (! $book || $book->trashed() || ! $book->bookDetail || $book->bookDetail->trashed() || ! $book->bookDetail->bookData || $book->bookDetail->bookData->trashed() || $book->status !== 'reserved') {
                throw new \RuntimeException('This copy is no longer available for this reservation.');
            }
            $account = $reservation->student?->account;
            if (! $account || ! $account->isApprovedStudent() || $account->reservations_blocked) {
                throw new \RuntimeException('This account cannot place reservations.');
            }
            if ($book->bookDetail->periodicalIssue && ! $book->bookDetail->periodicalIssue->borrowable) {
                throw new \RuntimeException('This issue is for library use only.');
            }
            $reservation->update(['reservation_status_id' => 2, 'approved_date' => now()]);
            return $reservation;
        });

        app(ReservationNotificationService::class)->statusChanged($reservation);
    }

    public function approveDue(): void
    {
        if (! ReservationSettings::autoApprove()) {
            return;
        }
        BookReservation::where('reservation_status_id', 1)
            ->where('reservation_date', '<=', now()->subMinutes(ReservationSettings::delayMinutes()))
            ->where('due_date', '>', now())->orderBy('id')->chunkById(100, function ($reservations) {
                foreach ($reservations as $reservation) {
                    try {
                        $this->approve($reservation->id);
                    } catch (\Illuminate\Database\QueryException $e) {
                        throw $e;
                    } catch (\RuntimeException $e) {
                        // Eligibility can change while a reservation waits for approval.
                    }
                }
            });
    }
}
