<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use App\Models\ReservationWaitlist;
use Illuminate\Support\Facades\DB;

class CatalogDeletionService
{
    public function inspect(string $type, array $ids): array
    {
        $ids = $this->expandPeriodicalTitles($type, $ids);
        $copies = in_array($type, ['title', 'issue'], true)
            ? Book::whereIn('book_detail_id', $ids)->get()
            : Book::whereIn('id', $ids)->get();
        $copyIds = $copies->modelKeys();
        $loans = BorrowingTransaction::whereIn('book_id', $copyIds)->whereNull('return_date')->count();
        $reservations = BookReservation::whereIn('book_id', $copyIds)->whereIn('reservation_status_id', [1, 2])->count();
        $loanRecords = BorrowingTransaction::whereIn('book_id', $copyIds)->count();
        $reservationRecords = BookReservation::withTrashed()->whereIn('book_id', $copyIds)->count();
        $waitlist = in_array($type, ['title', 'issue'], true) ? DB::table('reservation_waitlist')->whereIn('book_detail_id', $ids)->where('status', 'waiting')->count() : 0;
        $other = $copies->filter(fn ($copy) => $copy->status !== 'available')->count();

        return ['copies' => $copies->count(), 'loans' => $loans, 'reservations' => $reservations, 'other' => $other,
            'loan_records' => $loanRecords, 'reservation_records' => $reservationRecords, 'waitlist' => $waitlist,
            'requires_confirmation' => $loanRecords > 0 || $reservationRecords > 0 || $waitlist > 0 || $other > 0];
    }

    public function delete(string $type, array $ids, bool $confirmed): void
    {
        $ids = $this->expandPeriodicalTitles($type, $ids);
        DB::transaction(function () use ($type, $ids, $confirmed) {
            if (in_array($type, ['title', 'issue'], true)) {
                $details = BookDetail::whereIn('id', $ids)->lockForUpdate()->get();
                if ($details->count() !== count($ids)) {
                    throw new \RuntimeException('A selected title is no longer available.');
                }
                $copies = Book::whereIn('book_detail_id', $ids)->lockForUpdate()->get();
            } else {
                $copies = Book::whereIn('id', $ids)->lockForUpdate()->get();
                if ($copies->count() !== count($ids)) {
                    throw new \RuntimeException('A selected copy is no longer available.');
                }
            }
            if ($this->inspect($type, $ids)['requires_confirmation'] && ! $confirmed) {
                throw new \RuntimeException('The records have changed. Type DELETE to confirm.');
            }

            $copyIds = $copies->modelKeys();
            BookReservation::whereIn('book_id', $copyIds)->whereIn('reservation_status_id', [1, 2])
                ->get()->each(function (BookReservation $reservation) {
                    $reservation->update([
                        'reservation_status_id' => 4,
                        'cancelled_date' => now(),
                        'comment' => trim(($reservation->comment ?? '') . ' [Auto-cancelled: Copy deleted]'),
                    ]);
                });

            foreach ($copies as $copy) {
                $copy->delete();
            }
            if (in_array($type, ['title', 'issue'], true)) {
                foreach ($details as $detail) {
                    $detail->delete();
                }
            }

            $deletedDetailIds = in_array($type, ['title', 'issue'], true)
                ? $ids
                : $copies->pluck('book_detail_id')->unique()->all();
            foreach ($deletedDetailIds as $detailId) {
                if (! Book::where('book_detail_id', $detailId)->exists()) {
                    ReservationWaitlist::where('book_detail_id', $detailId)->where('status', 'waiting')
                        ->update(['status' => 'unavailable']);
                }
            }
        }, 3);
    }
    private function expandPeriodicalTitles(string $type, array $ids): array
    {
        if ($type !== 'title') {
            return $ids;
        }
        $titleIds = BookDetail::whereIn('id', $ids)->whereIn('catalog_kind', ['magazine', 'journal'])->pluck('book_data_id');
        return array_values(array_unique(array_merge($ids, BookDetail::whereIn('book_data_id', $titleIds)->whereIn('catalog_kind', ['magazine', 'journal'])->pluck('id')->all())));
    }
}
