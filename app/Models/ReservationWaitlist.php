<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationWaitlist extends Model
{
    protected $table = 'reservation_waitlist';

    protected $fillable = ['book_detail_id', 'account_id', 'student_id', 'status', 'book_reservation_id'];
}
