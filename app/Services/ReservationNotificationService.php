<?php

namespace App\Services;

use App\Models\BookReservation;
use App\Notifications\Alerts;
use App\Support\EmailUrl;
use Throwable;

class ReservationNotificationService
{
    public function created(BookReservation $reservation): void
    {
        $this->statusChanged($reservation);
    }

    public function statusChanged(
        BookReservation $reservation,
        ?string $titleOverride = null,
        ?string $messageOverride = null,
        ?string $typeOverride = null,
    ): void
    {
        $reservation->loadMissing([
            'account', 'student.account', 'reservationStatus',
            'book' => fn ($query) => $query->withTrashed(),
            'book.bookDetail' => fn ($query) => $query->withTrashed(),
            'book.bookDetail.bookData' => fn ($query) => $query->withTrashed(),
        ]);
        $account = $reservation->account ?? $reservation->student?->account;
        if (! $account) {
            return;
        }

        $status = $reservation->reservationStatus?->status ?? match ((int) $reservation->reservation_status_id) {
            1 => 'Pending', 2 => 'Approved', 3 => 'Fulfilled', 4 => 'Cancelled', 5 => 'Expired', default => 'Updated',
        };
        [$title, $message, $type] = match (strtolower($status)) {
            'pending' => ['Reservation request received', 'Your reservation request was submitted and is pending library approval.', 'info'],
            'approved' => ['Reservation approved', 'Your reservation was approved. Please collect it by the pickup deadline below.', 'success'],
            'fulfilled' => ['Reservation fulfilled', 'The reserved book has been borrowed and your reservation is complete.', 'success'],
            'cancelled' => ['Reservation cancelled', 'Your reservation has been cancelled. Contact the library if you need more information.', 'warning'],
            'expired' => ['Reservation expired', 'Your reservation expired because the pickup deadline passed.', 'warning'],
            default => ['Reservation updated', 'Your reservation details have changed.', 'info'],
        };
        $title = $titleOverride ?? $title;
        $message = $messageOverride ?? $message;
        $type = $typeOverride ?? $type;
        $dueDate = $reservation->due_date?->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A');
        $detail = $reservation->book?->bookDetail;
        $reference = 'RES-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);

        try {
            $account->notify((new Alerts(
                title: $title,
                message: $message,
                type: $type,
                actionUrl: EmailUrl::route('student.reservations'),
                actionText: 'View reservations',
                bookTitle: $detail?->bookData?->book_title,
                dueDate: $dueDate,
                channels: ['database', 'mail'],
                sendSms: true,
                smsMessage: "PGPC Library: {$reference} for " . ($detail?->bookData?->book_title ?? 'your book') . " — {$title}." . (strtolower($status) === 'approved' || $titleOverride === 'Pickup deadline extended' ? " Collect by {$dueDate}." : ''),
                details: [
                    'Reservation reference' => $reference,
                    'Reserved on' => $reservation->reservation_date?->format('M d, Y h:i A'),
                    'Pickup deadline' => strtolower($status) === 'approved' || $titleOverride === 'Pickup deadline extended' ? $dueDate : null,
                    'Accession number' => $reservation->book?->accession_number,
                    'Call number' => $detail?->call_number,
                    'ISBN' => $detail?->isbn,
                    'Item type' => ucfirst($detail?->catalog_kind ?? 'book'),
                    'Status' => $status,
                ],
            ))->onConnection('database')->afterCommit());
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function pickupDeadlineExtended(BookReservation $reservation): void
    {
        $this->statusChanged(
            $reservation,
            'Pickup deadline extended',
            'The pickup deadline for your reservation has been extended.',
            'info',
        );
    }
}
