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
    protected ?array $lastRecommendDebug = null;

    public function getLastRecommendDebug(): ?array
    {
        return $this->lastRecommendDebug;
    }

    /** Request recommendations using only the account's encrypted identifier. */
    public function refreshFor(Account $account): UserRecommendation
    {
        $histories = $account->userHistories()
            ->select(['encrypter_id', 'isbn', 'rating'])
            ->get();
        $encrypterId = $histories->pluck('encrypter_id')->filter()->first();
        $borrowedIsbns = $histories->where('rating', 2)->pluck('isbn')
            ->map(fn ($isbn) => trim((string) $isbn))->filter()->unique()->values()->all();

        $endpointUrl = $this->endpoint('/api/recommend');

        if (! $encrypterId) {
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => 'Encrypted user identifier is unavailable.',
            ];
            throw new RuntimeException('Encrypted user identifier is unavailable.');
        }

        try {
            $response = $this->client()
                ->connectTimeout(1)
                ->timeout(min((int) config('services.recommender.timeout', 30), 5))
                ->post($endpointUrl, [
                    'encrypter_id' => (string) $encrypterId,
                    'borrowed_books' => $borrowedIsbns,
                ]);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => $exception->getMessage(),
            ];
            throw new RuntimeException('Recommendation service is temporarily unavailable.', previous: $exception);
        } catch (Throwable $exception) {
            report($exception);
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => $exception->getMessage(),
            ];
            throw new RuntimeException('Recommendation service is temporarily unavailable.', previous: $exception);
        }

        if (! $response->successful()) {
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => $response->status(),
                'raw' => $response->json() ?? $response->body(),
                'error' => 'HTTP ' . $response->status(),
            ];
            report(new RuntimeException('Recommendation request failed with HTTP '.$response->status().'.'));
            throw new RuntimeException($this->safeRecommendationError($response->status()));
        }

        $payload = $response->json();
        $recommendations = $response->json('recommendations');
        if (! is_array($recommendations)) {
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => $response->status(),
                'raw' => $payload ?? $response->body(),
                'error' => 'Recommendation response did not contain a recommendations array.',
            ];
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

        $this->lastRecommendDebug = [
            'endpoint' => $endpointUrl,
            'available' => true,
            'status' => $response->status(),
            'raw' => $payload,
            'isbns' => $isbns,
        ];

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
        $endpointUrl = $this->endpoint((string) config('services.recommender.evaluation_path', '/api/metrics'));

        try {
            $response = $this->client()
                ->connectTimeout(1)
                ->timeout(2)
                ->get($endpointUrl);
        } catch (ConnectionException $exception) {
            report($exception);

            return $this->unavailableEvaluation(
                'The recommendation service could not be reached.',
                $checkedAt,
                $endpointUrl,
                null,
                ['exception' => $exception->getMessage()]
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->unavailableEvaluation(
                'The recommendation service is temporarily unavailable.',
                $checkedAt,
                $endpointUrl,
                null,
                ['exception' => $exception->getMessage()]
            );
        }

        if (! $response->successful()) {
            report(new RuntimeException('Recommendation evaluation failed with HTTP '.$response->status().'.'));

            return $this->unavailableEvaluation(
                $response->status() === 404
                    ? 'The recommender is reachable, but its configured evaluation endpoint was not found.'
                    : ($response->status() === 401
                        ? 'The recommender rejected the API key. Check the Laravel and recommender API key configuration.'
                        : 'The recommendation service is temporarily unavailable.'),
                $checkedAt,
                $endpointUrl,
                $response->status(),
                $response->json() ?? $response->body()
            );
        }

        $payload = $response->json();
        $payload = is_array($payload) ? $payload : [];
        $metrics = $this->normalizeMetrics(is_array($payload['metrics'] ?? null) ? $payload['metrics'] : $payload);

        if ($metrics === []) {
            return $this->unavailableEvaluation(
                'The recommendation service responded without any evaluation metrics.',
                $checkedAt,
                $endpointUrl,
                $response->status(),
                $payload ?: $response->body()
            );
        }

        try {
            MlEvaluationRun::record($metrics, $payload);
        } catch (Throwable $exception) {
            // Never break the dashboard because the history could not be written.
            report($exception);
        }

        return [
            'available' => true,
            'endpoint' => $endpointUrl,
            'status' => $response->status(),
            'raw' => $payload,
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

    protected function unavailableEvaluation(
        string $error,
        string $checkedAt,
        ?string $endpoint = null,
        ?int $status = null,
        mixed $raw = null
    ): array {
        return [
            'available' => false,
            'endpoint' => $endpoint,
            'status' => $status,
            'raw' => $raw,
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

    protected function endpoint(string $path): string
    {
        $url = rtrim((string) config('services.recommender.url'), '/');
        $configuredEvaluationPath = trim((string) config('services.recommender.evaluation_path', '/api/metrics'), '/');
        $urlPath = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($urlPath !== '' && $urlPath === $configuredEvaluationPath) {
            $url = substr($url, 0, -strlen($urlPath) - 1);
        }

        if (
            is_file('/.dockerenv')
            && in_array(parse_url($url, PHP_URL_HOST), ['127.0.0.1', 'localhost'], true)
        ) {
            $url = rtrim(
                (string) config(
                    'services.recommender.container_url',
                    'http://recommender-api:5000'
                ),
                '/'
            );
        }

        return $url . '/' . ltrim($path, '/');
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
