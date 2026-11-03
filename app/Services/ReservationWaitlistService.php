<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\ReservationWaitlist;
use Illuminate\Support\Facades\DB;

class ReservationWaitlistService
{
    public function handleLostReservations(Book $lostBook): array
    {
        $detail = BookDetail::lockForUpdate()->findOrFail($lostBook->book_detail_id);
        $reservations = BookReservation::with('student')->where('book_id', $lostBook->id)
            ->whereIn('reservation_status_id', [1, 2])->orderBy('reservation_date')->orderBy('id')->lockForUpdate()->get();
        $reservations = $reservations->sortBy(function ($reservation) use ($detail) {
            $original = ReservationWaitlist::where('book_detail_id', $detail->id)->where('book_reservation_id', $reservation->id)->value('created_at');
            return min((string) ($reservation->reservation_date ?? $reservation->created_at), (string) ($original ?? $reservation->reservation_date ?? $reservation->created_at));
        });
        $outcomes = [];

        if ($detail->books()->where('status', 'available')->exists()) {
            $paused = ReservationWaitlist::where('book_detail_id', $detail->id)->where('status', 'lost_unavailable')->lockForUpdate()->get();
            foreach ($paused as $entry) {
                $entry->update(['status' => 'waiting']);
                $this->notifyWaiter($entry, 'Waitlist reopened', 'A copy may be available and your place has been restored.');
            }
        }

        foreach ($reservations as $reservation) {
            $existing = ReservationWaitlist::where('book_detail_id', $detail->id)
                ->where('account_id', $reservation->account_id ?? $reservation->student?->account_id)
                ->where(fn ($q) => $q->whereIn('status', ['waiting', 'lost_unavailable'])->orWhere('book_reservation_id', $reservation->id))
                ->orderBy('created_at')->orderBy('id')->lockForUpdate()->first();
            $priority = $reservation->reservation_date ?? $reservation->created_at ?? now();
            if ($existing?->created_at && $existing->created_at->lt($priority)) $priority = $existing->created_at;
            $this->allocateLocked($detail, $priority, $existing?->id);
            $copy = Book::where('book_detail_id', $detail->id)->where('status', 'available')
                ->whereDoesntHave('borrowingTransactions', fn ($q) => $q->whereNull('return_date')->whereNull('lost_at'))
                ->whereDoesntHave('reservations', fn ($q) => $q->whereIn('reservation_status_id', [1, 2]))
                ->orderBy('id')->lockForUpdate()->first();

            if ($copy) {
                $reservation->update(['book_id' => $copy->id, 'comment' => trim(($reservation->comment ?? '') . " [Reassigned from lost copy #{$lostBook->id}]")]);
                $copy->update(['status' => 'reserved']);
                if ($existing) $existing->update(['status' => 'allocated', 'book_reservation_id' => $reservation->id]);
                $outcomes[] = 'reassigned';
                $title = 'Reserved copy changed';
                $message = 'Your reserved copy was reported lost. Another copy has been assigned to your reservation.';
            } else {
                $accountId = $reservation->account_id ?? $reservation->student?->account_id;
                if (! $accountId || ! $reservation->student_id) {
                    $reservation->update(['reservation_status_id' => 4, 'cancelled_date' => now(),
                        'comment' => trim(($reservation->comment ?? '') . ' [Cancelled: copy lost; staff follow-up required: missing student account]')]);
                    $outcomes[] = 'followup';
                    continue;
                }
                $entry = $existing ?? new ReservationWaitlist(['book_detail_id' => $detail->id, 'account_id' => $accountId, 'student_id' => $reservation->student_id]);
                $entry->status = 'waiting';
                $entry->book_reservation_id = $reservation->id;
                if (! $entry->exists || ! $entry->created_at || $entry->created_at->gt($priority)) $entry->created_at = $priority;
                $entry->save();
                $reservation->update(['reservation_status_id' => 4, 'cancelled_date' => now(),
                    'comment' => trim(($reservation->comment ?? '') . " [Cancelled: copy lost; waitlist #{$entry->id}]")]);
                $outcomes[] = 'waitlisted';
                $title = 'Reservation moved to waitlist';
                $message = 'Your reserved copy was reported lost. Your original request time will be used for your waitlist position.';
            }
            DB::afterCommit(function () use ($reservation, $title, $message) {
                try {
                    if ($current = $reservation->fresh()) app(ReservationNotificationService::class)->statusChanged($current, $title, $message, 'warning');
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Lost-copy reservation notification failed', ['exception' => $e]);
                }
            });
        }
        $this->allocateLocked($detail);
        return $outcomes;
    }

    public function suspendUnavailable(int $detailId): void
    {
        $detail = BookDetail::lockForUpdate()->findOrFail($detailId);
        if ($detail->books()->whereIn('status', ['available', 'borrowed', 'reserved'])->exists()) {
            return;
        }
        $entries = ReservationWaitlist::where('book_detail_id', $detailId)->where('status', 'waiting')->lockForUpdate()->get();
        foreach ($entries as $entry) {
            $entry->update(['status' => 'lost_unavailable']);
            $this->notifyWaiter($entry, 'Waitlist unavailable', 'There are no copies available to wait for. The library will notify you if the waitlist reopens.');
        }
    }

    public function reopen(int $detailId): void
    {
        AuditTrail::transaction('Waitlist reopened', function () use ($detailId) {
            $detail = BookDetail::lockForUpdate()->findOrFail($detailId);
            if (! $detail->books()->whereIn('status', ['available', 'borrowed', 'reserved'])->exists()) {
                throw new \DomainException('There are no available, borrowed, or reserved copies to wait for.');
            }
            $entries = ReservationWaitlist::where('book_detail_id', $detailId)->where('status', 'lost_unavailable')->orderBy('id')->lockForUpdate()->get();
            foreach ($entries as $entry) {
                $entry->update(['status' => 'waiting']);
                $this->notifyWaiter($entry, 'Waitlist reopened', 'The waitlist has reopened and your place has been restored.');
            }
            $this->allocateLocked($detail);
        });
    }

    private function notifyWaiter(ReservationWaitlist $entry, string $title, string $message): void
    {
        $account = Account::find($entry->account_id);
        if ($account) {
            DB::afterCommit(function () use ($account, $title, $message) {
                try {
                    $account->notify(new \App\Notifications\Alerts(title: $title, message: $message, type: 'info', channels: ['database']));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Waitlist notification failed', ['exception' => $e]);
                }
            });
        }
    }

    public function expireReservations(): void
    {
        $orphanedReservations = BookReservation::whereIn('reservation_status_id', [1, 2])
            ->where(function ($query) {
                $query->whereDoesntHave('book')
                    ->orWhereHas('book', fn ($book) => $book->whereHas('bookDetail', fn ($detail) => $detail->onlyTrashed()))
                    ->orWhereHas('book', fn ($book) => $book->whereHas('bookDetail.bookData', fn ($data) => $data->onlyTrashed()));
            })->get();
        foreach ($orphanedReservations as $reservation) {
            $reservation->update([
                'reservation_status_id' => 4,
                'cancelled_date' => now(),
                'comment' => trim(($reservation->comment ?? '') . ' [Auto-cancelled: Catalog item deleted]'),
            ]);
            app(ReservationNotificationService::class)->statusChanged($reservation);
        }

        ReservationWaitlist::where('status', 'waiting')
            ->where(function ($query) {
                $query->whereDoesntHave('bookDetail')
                    ->orWhereHas('bookDetail', fn ($detail) => $detail->onlyTrashed())
                    ->orWhereHas('bookDetail', fn ($detail) => $detail->whereDoesntHave('bookData'))
                    ->orWhereDoesntHave('bookDetail.books');
            })->update(['status' => 'unavailable']);

        BookReservation::whereIn('reservation_status_id', [1, 2])->where('due_date', '<', now())
            ->orderBy('id')->chunkById(100, function ($reservations) {
                foreach ($reservations as $reservation) {
                    $expired = \App\Services\AuditTrail::transaction('Reservation expired', function () use ($reservation) {
                        $reservation = BookReservation::lockForUpdate()->find($reservation->id);
                        if (! $reservation || ! in_array((int) $reservation->reservation_status_id, [1, 2], true) || ! $reservation->due_date || ! $reservation->due_date->isPast()) {
                            return null;
                        }
                        $reservation->update(['reservation_status_id' => 5, 'cancelled_date' => now(),
                            'comment' => trim(($reservation->comment ?? '') . ' [Auto-expired: Pickup deadline passed]')]);
                        $book = Book::lockForUpdate()->find($reservation->book_id);
                        if ($book && $book->status === 'reserved' && ! BookReservation::where('book_id', $book->id)->whereIn('reservation_status_id', [1, 2])->exists()) {
                            $book->update(['status' => 'available']);
                        }
                        return $reservation;
                    });
                    if ($expired) {
                        app(ReservationNotificationService::class)->statusChanged($expired);
                    }
                }
            });
    }

    public function join(int $detailId, Account $account): void
    {
        \App\Services\AuditTrail::transaction('Reservation waitlist updated', function () use ($detailId, $account) {
            $detail = BookDetail::whereHas('bookData')->lockForUpdate()->findOrFail($detailId);
            if ($detail->periodicalIssue && ! $detail->periodicalIssue->borrowable) {
                throw new \RuntimeException('This issue is for library use only.');
            }
            $student = $account->student;
            if (! $student || ! $account->isApprovedStudent() || $account->reservations_blocked) {
                throw new \RuntimeException('An approved student account is required to join the waitlist.');
            }
            if (ReservationWaitlist::where('book_detail_id', $detail->id)->where('account_id', $account->id)->whereIn('status', ['waiting', 'lost_unavailable'])->exists()) {
                throw new \RuntimeException('You are already on the waitlist for this book.');
            }
            if (ReservationWaitlist::where('book_detail_id', $detail->id)->where('status', 'lost_unavailable')->exists()) {
                throw new \RuntimeException('This waitlist is paused. Please contact the library.');
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
        \App\Services\AuditTrail::transaction('Reservation waitlist allocated', function () use ($detailId) {
            $detail = BookDetail::lockForUpdate()->find($detailId);
            if ($detail && $detail->bookData && (! $detail->periodicalIssue || $detail->periodicalIssue->borrowable)) {
                $this->allocateLocked($detail);
            }
        });
    }

    public function hasEligibleWaiter(int $detailId): bool
    {
        return ReservationWaitlist::where('book_detail_id', $detailId)->whereIn('status', ['waiting', 'lost_unavailable'])->orderBy('id')->get()
            ->contains(function ($entry) {
                $account = Account::find($entry->account_id);
                return $account && $account->student && $account->isApprovedStudent() && ! $account->reservations_blocked
                    && BookReservation::where('student_id', $entry->student_id)->whereIn('reservation_status_id', [1, 2])->count() < 3
                    && ReservationSettings::hasCapacity($entry->student_id);
            });
    }

    public function allocateLocked(BookDetail $detail, ?\Carbon\CarbonInterface $before = null, ?int $exceptId = null): void
    {
        $entries = ReservationWaitlist::where('book_detail_id', $detail->id)->where('status', 'waiting')
            ->when($before, fn ($query) => $query->where(fn ($q) => $q->whereNull('created_at')->orWhere('created_at', '<=', $before)))
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->orderByRaw('CASE WHEN created_at IS NULL THEN 0 ELSE 1 END')->orderBy('created_at')->orderBy('id')->lockForUpdate()->get();
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
            if (! \App\Models\Student::whereKey($entry->student_id)->lockForUpdate()->first()) {
                $entry->update(['status' => 'ineligible']);
                continue;
            }
            if (BookReservation::where('student_id', $entry->student_id)->whereIn('reservation_status_id', [1, 2])->count() >= 3) {
                continue;
            }
            if (! ReservationSettings::hasCapacity($entry->student_id)) {
                continue;
            }
            $reservation = BookReservation::create([
                'book_id' => $book->id, 'student_id' => $entry->student_id, 'account_id' => $account->id,
                'reservation_status_id' => 1, 'reservation_date' => now(),
                'due_date' => ReservationSettings::deadline(ReservationSettings::maxPickupDays()), 'comment' => 'Automatically reserved from the waitlist',
            ]);
            $book->update(['status' => 'reserved']);
            $entry->update(['status' => 'allocated', 'book_reservation_id' => $reservation->id]);
            app(ReservationNotificationService::class)->notifyStaff($reservation);
            app(ReservationNotificationService::class)->created($reservation);
        }
    }
}
