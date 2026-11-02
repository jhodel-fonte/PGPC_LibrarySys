<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Book extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $hidden = ['last_return_condition_note'];

    /**
     * Get the indexable data array for Scout.
     */
    public function toSearchableArray(): array
    {
        return [
            'accession_number' => $this->accession_number,
            'code' => $this->code,
            'location' => $this->location,
            'status' => $this->status,
        ];
    }

    protected $fillable = [
        'book_detail_id',
        'book_condition_id',
        'accession_number',
        'code',
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

    public function validateCopyState(int $conditionId, string $status, string $field = 'status'): void
    {
        $condition = BookCondition::findOrFail($conditionId);
        $status = strtolower($status);
        $conditionName = strtolower($condition->status);
        $activeLoan = $this->exists && $this->borrowingTransactions()->whereNull('return_date')->whereNull('lost_at')->exists();
        $activeReservation = $this->exists && $this->reservations()->whereIn('reservation_status_id', [1, 2])->exists();

        if (($activeLoan && $status !== 'borrowed') || ($activeReservation && $status !== 'reserved')
            || ($status === 'borrowed' && ! $activeLoan) || ($status === 'reserved' && ! $activeReservation)
            || ($conditionName === 'damaged' && ! in_array($status, ['damaged', 'maintenance'], true))
            || ($conditionName === 'lost' && $status !== 'lost')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => 'The condition or status conflicts with an active loan, reservation, or damaged/lost copy.',
            ]);
        }
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function borrowingTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_books', 'book_id', 'language_id')
            ->withTimestamps();
    }

    public function languageBooks(): HasMany
    {
        return $this->hasMany(Language_Book::class, 'book_id');
    }

    public function materialMetadata(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MaterialItemMetadata::class);
    }
}
