<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserRecommendation extends Model
{
    use HasFactory;

    protected $table = 'user_recommendations';

    protected $fillable = [
        'user_id',
        'isbns',
        'generated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'isbns' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Relationship to the user Account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    /**
     * Save or update top recommendations for a user.
     * Takes up to 10 ISBNs and stores them as a clean array.
     */
    public static function storeRecommendations(int $userId, array $isbns, ?Carbon $generatedAt = null): self
    {
        // Clean array and limit to top 10 unique non-empty ISBNs
        $cleanedIsbns = array_values(array_slice(
            array_unique(
                array_filter(
                    array_map(fn ($item) => is_string($item) ? trim($item) : '', $isbns)
                )
            ),
            0,
            10
        ));

        return static::updateOrCreate(
            ['user_id' => $userId],
            [
                'isbns' => $cleanedIsbns,
                'generated_at' => $generatedAt ?? now(),
            ]
        );
    }

    /**
     * Fetch active (non-deleted) BookDetail records matching the recommended ISBNs.
     * Note: If an ISBN was deleted or does not exist, it simply won't be returned.
     */
    public function getActiveBooks(): Collection
    {
        $isbns = $this->isbns ?? [];

        if (empty($isbns)) {
            return new Collection();
        }

        return BookDetail::with([
            'bookData.authors',
            'bookData.categories',
            'publisher',
            'bookType',
            'books' => fn ($query) => $query
                ->whereNull('deleted_at')
                ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived']),
        ])
            ->whereIn('isbn', $isbns)
            ->whereNull('deleted_at')
            ->get();
    }
}
