<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }
}
