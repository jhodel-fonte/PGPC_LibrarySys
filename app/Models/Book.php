<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_detail_id',
        'book_condition_id',
        'accession_number',
        'barcode',
        'location',
        'status',
        'date_acquired',
    ];

    protected $casts = [
        'date_acquired' => 'date',
    ];

    public function bookDetail(): BelongsTo
    {
        return $this->belongsTo(BookDetail::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(BookCondition::class, 'book_condition_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function borrowingTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class);
    }
}
