<?php

namespace App\Services;

use App\Models\Account;
use App\Models\UserRecommendation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RecommendationService
{
    /** Request recommendations using only the account's encrypted identifier. */
    public function refreshFor(Account $account): UserRecommendation
    {
        $borrowedIsbns = $account->userHistories()->where('rating', 2)->pluck('isbn')
            ->map(fn ($isbn) => trim((string) $isbn))->filter()->unique()->values()->all();

        try {
            $response = Http::acceptJson()->withHeaders(['X-API-Key' => (string) config('services.recommender.key')])
                ->timeout((int) config('services.recommender.timeout', 30))
                ->post(rtrim((string) config('services.recommender.url'), '/').'/api/recommend', [
                    'encrypter_id' => Account::encodeId($account->id),
                    'borrowed_books' => $borrowedIsbns,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Recommendation service is unavailable.', previous: $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Recommendation service returned an unexpected response.');
        }

        $isbns = collect($response->json('recommendations', []))->pluck('isbn')
            ->filter(fn ($isbn) => is_string($isbn) && trim($isbn) !== '')->all();

        return UserRecommendation::storeRecommendations($account->id, $isbns);
    }
}
