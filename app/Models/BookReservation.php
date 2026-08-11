<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'reservation_status_id',
        'reservation_date',
        'due_date',
        'approved_date',
        'fulfilled_date',
        'cancelled_date',
        'comment',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'due_date' => 'datetime',
        'approved_date' => 'datetime',
        'fulfilled_date' => 'datetime',
        'cancelled_date' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function reservationStatus(): BelongsTo
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id');
    }

    public function borrowingTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'book_reservation_id');
    }
}
