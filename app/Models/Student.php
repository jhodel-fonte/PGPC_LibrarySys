<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Student extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * Get the indexable data array for Scout.
     */
    public function toSearchableArray(): array
    {
        return [
            'school_id_number' => $this->school_id_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'contact_num' => $this->contact_num,
            'program' => $this->program,
            'year_level' => $this->year_level,
            'enrollment_status' => $this->enrollment_status,
            'enrollment_year' => $this->enrollment_year,
        ];
    }

    protected $fillable = [
        'account_id',
        'school_id_number',
        'first_name',
        'middle_name',
        'last_name',
        'contact_num',
        'date_of_birth',
        'image_url',
        'library_status_id',
        'note',
        'program',
        'year_level',
        'enrollment_status',
        'enrollment_year',
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
