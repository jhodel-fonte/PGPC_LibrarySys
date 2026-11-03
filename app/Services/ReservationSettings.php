<?php

namespace App\Services;

class ReservationSettings
{
    public static function hasCapacity(int $studentId, ?int $excludeReservation = null): bool
    {
        $loans = \App\Models\BorrowingTransaction::where('school_id', $studentId)
            ->whereNull('return_date')->whereNull('lost_at');
        if ((clone $loans)->where('due_date', '<', now())->exists()) {
            return false;
        }

        $reservations = \App\Models\BookReservation::where('student_id', $studentId)
            ->whereIn('reservation_status_id', [1, 2])
            ->when($excludeReservation, fn ($query) => $query->where('id', '!=', $excludeReservation));

        return $loans->count() + $reservations->count() < (int) LibrarySettings::get('library_config.max_loan_limit');
    }

    private static function value(string $key): mixed
    {
        return LibrarySettings::get('reservation.' . $key);
    }

    public static function maxPickupDays(): int
    {
        return max(1, (int) self::value('max_pickup_days'));
    }

    public static function autoApprove(): bool
    {
        return filter_var(self::value('auto_approve'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function delayMinutes(): int
    {
        return max(0, (int) self::value('auto_approve_delay_minutes'));
    }

    public static function deadline(int $days): \Carbon\Carbon
    {
        return now('Asia/Manila')->addDays($days)->setTime(17, 0)->setTimezone(config('app.timezone'));
    }
}
