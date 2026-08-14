<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotifTemplate extends Model
{
    use HasFactory;

    protected $table = 'notif_templates';

    protected $fillable = [
        'type',
        'email_subject',
        'message',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'template_id');
    }

    public function getMessagesByType(string $type)
    {
        return static::where('type', $type)->get();
    }
}
