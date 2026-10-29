<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RegistrationEmailBlocklist extends Model
{
    protected $fillable = [
        'email',
        'banned_at',
        'expires_at',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function removeExpired(): void
    {
        static::where('expires_at', '<=', now())->delete();
    }

    public static function isBlocked(string $email): bool
    {
        static::removeExpired();

        return static::where('email', strtolower(trim($email)))
            ->where('expires_at', '>', now())
            ->exists();
    }

    public static function ban(string $email): self
    {
        $now = now();
        $expiresAt = $now->copy()->addDays((int) config('settings.registration.email_block_days', 3));

        return static::updateOrCreate(
            ['email' => strtolower(trim($email))],
            [
                'banned_at' => $now,
                'expires_at' => $expiresAt,
            ],
        );
    }
}
