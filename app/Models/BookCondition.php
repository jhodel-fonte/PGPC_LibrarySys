<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'book_condition_id');
    }

    public function issuedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'issued_condition_id');
    }

    public function returnedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'return_condition_id');
    }
}
