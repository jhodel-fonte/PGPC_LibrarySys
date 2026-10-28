<?php

namespace App\Services;

use App\Models\Account;
use App\Models\MlEvaluationRun;
use App\Models\UserRecommendation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

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

    /**
     * Read the recommender's evaluation snapshot for the admin analytics page.
     *
     * The endpoint is intentionally read-only and returns a normalized shape so
     * the dashboard remains stable when the model service adds extra fields.
     * Every successful read is saved to `ml_evaluation_runs` so the page can
     * show a history, and fall back to the last saved result when offline.
     *
     * @return array{available: bool, metrics: array<string, float>, checked_at: string, error: ?string}
     */
    public function evaluation(): array
    {
        return Cache::remember('recommender:evaluation', now()->addSeconds(60), function (): array {
            return $this->fetchEvaluation();
        });
    }

    public function clearEvaluationCache(): void
    {
        Cache::forget('recommender:evaluation');
    }

    protected function fetchEvaluation(): array
    {
        $checkedAt = now()->toIso8601String();

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-API-Key' => (string) config('services.recommender.key')])
                ->connectTimeout(1)
                ->timeout(2)
                ->get(rtrim((string) config('services.recommender.url'), '/').(string) config('services.recommender.evaluation_path', '/api/metrics'));
        } catch (ConnectionException $exception) {
            report($exception);

            return $this->unavailableEvaluation('The recommendation service could not be reached.', $checkedAt);
        }

        if (! $response->successful()) {
            report(new RuntimeException('Recommendation evaluation returned an unexpected response.'));

            return $this->unavailableEvaluation('The recommendation service returned an error (HTTP '.$response->status().').', $checkedAt);
        }

        $payload = $response->json();
        $payload = is_array($payload) ? $payload : [];
        $metrics = $this->normalizeMetrics(is_array($payload['metrics'] ?? null) ? $payload['metrics'] : $payload);

        if ($metrics === []) {
            return $this->unavailableEvaluation('The recommendation service responded without any evaluation metrics.', $checkedAt);
        }

        try {
            MlEvaluationRun::record($metrics, $payload);
        } catch (Throwable $exception) {
            // Never break the dashboard because the history could not be written.
            report($exception);
        }

        return [
            'available' => true,
            'metrics' => $metrics,
            'checked_at' => $checkedAt,
            'error' => null,
        ];
    }

    /**
     * Map the model service's metric keys (any casing / naming style) to display labels.
     */
    protected function normalizeMetrics(array $raw): array
    {
        $aliases = [
            'rmse' => 'RMSE',
            'mae' => 'MAE',
            'mean' => 'Mean',
            'median' => 'Median',
            'standarddeviation' => 'Standard deviation',
            'std' => 'Standard deviation',
            'stddev' => 'Standard deviation',
            'ndcg@10' => 'NDCG@10',
            'ndcgat10' => 'NDCG@10',
            'ndcg10' => 'NDCG@10',
        ];

        $metrics = [];
        foreach ($raw as $key => $value) {
            $normalizedKey = str_replace([' ', '_', '-'], '', strtolower((string) $key));
            $label = $aliases[$normalizedKey] ?? null;

            if ($label !== null && is_numeric($value)) {
                $metrics[$label] = round((float) $value, 4);
            }
        }

        // Keep a stable display order.
        return collect(array_keys(MlEvaluationRun::METRICS))
            ->filter(fn ($label) => array_key_exists($label, $metrics))
            ->mapWithKeys(fn ($label) => [$label => $metrics[$label]])
            ->all();
    }

    protected function unavailableEvaluation(string $error, string $checkedAt): array
    {
        return [
            'available' => false,
            'metrics' => [],
            'checked_at' => $checkedAt,
            'error' => $error,
        ];
    }
}
