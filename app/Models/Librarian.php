<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Librarian extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'librarians';

    protected $fillable = [
        'account_id',
        'school_id_number',
        'first_name',
        'middle_name',
        'last_name',
        'contact_num',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function issuedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'issued_by_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'received_by_id');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(FinePayment::class, 'received_by_id');
    }
}
