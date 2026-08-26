<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'school_id_number',
        'first_name',
        'middle_name',
        'last_name',
        'contact_num',
        'library_status_id',
        'note',
        'program',
        'year_level',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function libraryStatus(): BelongsTo
    {
        return $this->belongsTo(LibraryStatus::class, 'library_status_id');
    }

    public function borrowingTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'school_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
