<?php

namespace App\Services;

use App\Models\Account;
use App\Models\MlEvaluationRun;
use App\Models\UserRecommendation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
            $response = $this->client()
                ->connectTimeout(1)
                ->timeout(min((int) config('services.recommender.timeout', 30), 5))
                ->post(rtrim((string) config('services.recommender.url'), '/').'/api/recommend', [
                    'encrypter_id' => Account::encodeId($account->id),
                    'borrowed_books' => $borrowedIsbns,
                ]);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            throw new RuntimeException('Recommendation service is temporarily unavailable.', previous: $exception);
        }

        if (! $response->successful()) {
            report(new RuntimeException('Recommendation request failed with HTTP '.$response->status().'.'));
            throw new RuntimeException($this->safeRecommendationError($response->status()));
        }

        $recommendations = $response->json('recommendations');
        if (! is_array($recommendations)) {
            report(new RuntimeException('Recommendation response did not contain a recommendations array.'));
            throw new RuntimeException('Recommendation service returned an invalid response.');
        }

        $isbns = collect($recommendations)->map(function ($recommendation): string {
            if (! is_array($recommendation) || ! isset($recommendation['isbn']) || ! is_string($recommendation['isbn'])) {
                throw new RuntimeException('Recommendation service returned a malformed recommendation.');
            }

            $isbn = trim($recommendation['isbn']);
            if ($isbn === '' || strlen($isbn) > 32) {
                throw new RuntimeException('Recommendation service returned a malformed recommendation.');
            }

            return $isbn;
        })->unique()->take(10)->values()->all();

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
            $response = $this->client()
                ->connectTimeout(1)
                ->timeout(2)
                ->get(rtrim((string) config('services.recommender.url'), '/').(string) config('services.recommender.evaluation_path', '/api/metrics'));
        } catch (ConnectionException $exception) {
            report($exception);

            return $this->unavailableEvaluation('The recommendation service could not be reached.', $checkedAt);
        }

        if (! $response->successful()) {
            report(new RuntimeException('Recommendation evaluation failed with HTTP '.$response->status().'.'));

            return $this->unavailableEvaluation('The recommendation service is temporarily unavailable.', $checkedAt);
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

    protected function client()
    {
        return Http::acceptJson()->withHeaders([
            'X-API-Key' => (string) config('services.recommender.key'),
        ]);
    }

    protected function safeRecommendationError(int $status): string
    {
        return match ($status) {
            401 => 'Your session is not authorized to use recommendations. Please sign in again.',
            429 => 'Recommendations were requested too frequently. Please try again shortly.',
            503, 504 => 'The recommendation service is temporarily unavailable. Please try again later.',
            default => 'We could not generate recommendations right now. Please try again later.',
        };
    }
}
