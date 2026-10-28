<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHistory extends Model
{
    use HasFactory;

    public const RATING_NO_INTERACTION = 0;
    public const RATING_VIEW = 1;
    public const RATING_BORROWED = 2;

    protected $table = 'user_histories';

    protected $fillable = [
        'user_id',
        'encrypter_id',
        'isbn',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Relationship to the user Account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    /**
     * Upsert a user-book interaction preserving the highest rating.
     * (e.g., if a book was already borrowed [rating 2], a later view [rating 1] won't downgrade it).
     */
    public static function upsertHighestRating(int $userId, string $isbn, int $newRating, ?string $encrypterId = null): self
    {
        $isbn = trim($isbn);
        $encrypterId = $encrypterId ?? Account::encodeId($userId);

        $history = static::firstOrNew([
            'user_id' => $userId,
            'isbn' => $isbn,
        ]);

        if (! $history->exists) {
            $history->encrypter_id = $encrypterId;
            $history->rating = $newRating;
            $history->save();
        } else {
            $needsSave = false;

            // Only upgrade to higher rating
            if ($newRating > (int) $history->rating) {
                $history->rating = $newRating;
                $needsSave = true;
            }

            if (empty($history->encrypter_id) && $encrypterId) {
                $history->encrypter_id = $encrypterId;
                $needsSave = true;
            }

            if ($needsSave) {
                $history->save();
            }
        }

        return $history;
    }
}
