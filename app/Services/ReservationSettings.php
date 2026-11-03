<?php

namespace App\Services;

class ReservationSettings
{
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
