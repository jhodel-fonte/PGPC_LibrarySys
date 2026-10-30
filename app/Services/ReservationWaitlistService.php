<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\ReservationWaitlist;
use App\Notifications\Alerts;
use Illuminate\Support\Facades\DB;

class ReservationWaitlistService
{
    public function expireReservations(): void
    {
        BookReservation::whereIn('reservation_status_id', [1, 2])->where('due_date', '<', now())
            ->orderBy('id')->chunkById(100, function ($reservations) {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation) {
                        $reservation = BookReservation::lockForUpdate()->find($reservation->id);
                        if (! $reservation || ! in_array((int) $reservation->reservation_status_id, [1, 2], true) || ! $reservation->due_date || ! $reservation->due_date->isPast()) {
                            return;
                        }
                        $reservation->update(['reservation_status_id' => 5, 'cancelled_date' => now(),
                            'comment' => trim(($reservation->comment ?? '') . ' [Auto-expired: Pickup deadline passed]')]);
                        $book = Book::lockForUpdate()->find($reservation->book_id);
                        if ($book && $book->status === 'reserved' && ! BookReservation::where('book_id', $book->id)->whereIn('reservation_status_id', [1, 2])->exists()) {
                            $book->update(['status' => 'available']);
                        }
                    });
                }
            });
    }

    public function join(int $detailId, Account $account): void
    {
        DB::transaction(function () use ($detailId, $account) {
            $detail = BookDetail::whereHas('bookData')->lockForUpdate()->findOrFail($detailId);
            if ($detail->periodicalIssue && ! $detail->periodicalIssue->borrowable) {
                throw new \RuntimeException('This issue is for library use only.');
            }
            $student = $account->student;
            if (! $student || ! $account->isApprovedStudent() || $account->reservations_blocked) {
                throw new \RuntimeException('An approved student account is required to join the waitlist.');
            }
            if (ReservationWaitlist::where('book_detail_id', $detail->id)->where('account_id', $account->id)->where('status', 'waiting')->exists()) {
                throw new \RuntimeException('You are already on the waitlist for this book.');
            }
            if (BookReservation::where('account_id', $account->id)->whereIn('reservation_status_id', [1, 2])
                ->whereHas('book', fn ($query) => $query->where('book_detail_id', $detail->id))->exists()) {
                throw new \RuntimeException('You already have an active reservation for this book.');
            }
            if (! $detail->books()->whereIn('status', ['borrowed', 'reserved'])->exists()) {
                throw new \RuntimeException('There are no borrowed or reserved copies to wait for.');
            }
            ReservationWaitlist::create(['book_detail_id' => $detail->id, 'account_id' => $account->id, 'student_id' => $student->id]);
            $this->allocateLocked($detail);
        });
    }

    public function allocate(int $detailId): void
    {
        DB::transaction(function () use ($detailId) {
            $detail = BookDetail::lockForUpdate()->find($detailId);
            if ($detail && $detail->bookData && (! $detail->periodicalIssue || $detail->periodicalIssue->borrowable)) {
                $this->allocateLocked($detail);
            }
        });
    }

    public function hasEligibleWaiter(int $detailId): bool
    {
        return ReservationWaitlist::where('book_detail_id', $detailId)->where('status', 'waiting')->orderBy('id')->get()
            ->contains(function ($entry) {
                $account = Account::find($entry->account_id);
                return $account && $account->student && $account->isApprovedStudent() && ! $account->reservations_blocked
                    && BookReservation::where('student_id', $entry->student_id)->whereIn('reservation_status_id', [1, 2])->count() < 3;
            });
    }

    private function allocateLocked(BookDetail $detail): void
    {
        $entries = ReservationWaitlist::where('book_detail_id', $detail->id)->where('status', 'waiting')->orderBy('id')->lockForUpdate()->get();
        foreach ($entries as $entry) {
            $book = Book::where('book_detail_id', $detail->id)->where('status', 'available')->orderBy('id')->lockForUpdate()->first();
            if (! $book) {
                return;
            }
            $account = Account::find($entry->account_id);
            if (! $account || ! $account->student || ! $account->isApprovedStudent()) {
                $entry->update(['status' => 'ineligible']);
                continue;
            }
            if ($account->reservations_blocked) {
                continue;
            }
            if (BookReservation::where('student_id', $entry->student_id)->whereIn('reservation_status_id', [1, 2])->count() >= 3) {
                continue;
            }
            $reservation = BookReservation::create([
                'book_id' => $book->id, 'student_id' => $entry->student_id, 'account_id' => $account->id,
                'reservation_status_id' => 2, 'reservation_date' => now(), 'approved_date' => now(),
                'due_date' => now()->addDays(3)->setTime(17, 0), 'comment' => 'Automatically reserved from the waitlist',
            ]);
            $book->update(['status' => 'reserved']);
            $entry->update(['status' => 'allocated', 'book_reservation_id' => $reservation->id]);
            $notification = new Alerts(title: 'Waitlisted book reserved', message: 'A copy is now reserved for you. Please collect it by '.$reservation->due_date->format('M d, Y').'.', bookTitle: $detail->bookData?->book_title, dueDate: $reservation->due_date->format('M d, Y'), sendSms: true);
            $account->notify($notification->afterCommit());
        }
    }
}
