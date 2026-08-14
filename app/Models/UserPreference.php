<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'cookies_enable',
        'email_overdue',
        'email_reservation',
        'due_reminder',
        'in_app_announcements',
    ];

    /**
     * Get the account that owns the preference.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
