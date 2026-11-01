<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationSettings
{
    private static function value(string $key): mixed
    {
        $default = config('settings.reservation.' . $key);
        try {
            if (Schema::hasTable('system_settings')) {
                return DB::table('system_settings')->where('setting_key', 'reservation.' . $key)->value('setting_value') ?? $default;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
        }

        return $default;
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
