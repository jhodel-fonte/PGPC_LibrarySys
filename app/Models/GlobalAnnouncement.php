<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'global_announcements';

    protected $fillable = [
        'title',
        'message',
        'display_style',
        'priority',
        'target_dashboard',
        'target_in_app',
        'target_email',
        'status',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'target_dashboard' => 'boolean',
        'target_in_app' => 'boolean',
        'target_email' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }
}
