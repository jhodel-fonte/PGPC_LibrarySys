<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BorrowingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'school_id', // foreign key pointing to students table
        'issued_by_id',
        'book_reservation_id',
        'borrow_type_id',
        'issued_condition_id',
        'return_condition_id',
        'issued_date',
        'due_date',
        'return_date',
        'received_by_id',
    ];

    protected $casts = [
        'issued_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'school_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(Librarian::class, 'issued_by_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(BookReservation::class, 'book_reservation_id');
    }

    public function borrowType(): BelongsTo
    {
        return $this->belongsTo(BorrowType::class);
    }

    public function issuedCondition(): BelongsTo
    {
        return $this->belongsTo(BookCondition::class, 'issued_condition_id');
    }

    public function returnCondition(): BelongsTo
    {
        return $this->belongsTo(BookCondition::class, 'return_condition_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Librarian::class, 'received_by_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
