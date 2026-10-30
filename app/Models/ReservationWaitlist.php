<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationWaitlist extends Model
{
    protected $table = 'reservation_waitlist';

    protected $fillable = ['book_detail_id', 'account_id', 'student_id', 'status', 'book_reservation_id'];

    public function bookDetail(): BelongsTo
    {
        return $this->belongsTo(BookDetail::class);
    }
}
