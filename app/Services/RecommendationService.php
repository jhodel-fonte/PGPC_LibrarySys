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
    public function refreshFor(Account $account, ?string $viewedIsbn = null, ?int $rating = null): UserRecommendation
    {
        $this->lastRecommendDebug = null;
        $histories = $account->userHistories()
            ->where('is_seeded', false)
            ->select(['isbn', 'rating'])
            ->get();
        $userKey = $account->ml_user_token;
        $borrowedIsbns = $histories->where('rating', 2)->pluck('isbn')
            ->map(fn ($isbn) => trim((string) $isbn))->filter()->unique()->values()->all();
        $student = $account->student;

        $endpointUrl = $this->endpoint('/api/recommend');

        if (! $userKey) {
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => 'Encrypted user identifier is unavailable.',
                'reason' => 'missing_user_identifier',
            ];
            throw new RuntimeException('Encrypted user identifier is unavailable.');
        }

        try {
            $response = $this->client()
                ->connectTimeout(1)
                ->timeout(min((int) config('services.recommender.timeout', 30), 5))
                ->post($endpointUrl, array_filter([
                    'user_key' => (string) $userKey,
                    'borrowed_books' => $borrowedIsbns,
                    'student_context' => $student ? array_filter([
                        'program' => $student->program,
                        'year_level' => $student->year_level,
                    ], fn ($value) => filled($value)) : null,
                    'interaction' => $viewedIsbn ? ['isbn' => $viewedIsbn, 'rating' => $rating] : null,
                    'data' => $viewedIsbn ? ['student_id' => (string) $userKey, 'book_id' => $viewedIsbn, 'rating' => $rating] : null,
                ], fn ($value) => $value !== null));
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => $exception->getMessage(),
                'reason' => 'connection_error',
            ];
            throw new RuntimeException('Recommendation service is temporarily unavailable.', previous: $exception);
        } catch (Throwable $exception) {
            report($exception);
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => null,
                'error' => $exception->getMessage(),
                'reason' => 'connection_error',
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
                'reason' => 'http_error',
            ];
            report(new RuntimeException('Recommendation request failed with HTTP '.$response->status().'.'));
            throw new RuntimeException($this->safeRecommendationError($response->status()));
        }

        $payload = $response->json();
        $recommendations = $response->json('recommendations');
        if (! is_array($recommendations) || ! array_is_list($recommendations)) {
            $this->lastRecommendDebug = [
                'endpoint' => $endpointUrl,
                'available' => false,
                'status' => $response->status(),
                'raw' => $payload ?? $response->body(),
                'error' => 'Recommendation response did not contain a recommendations array.',
                'reason' => 'invalid_response',
            ];
            report(new RuntimeException('Recommendation response did not contain a recommendations array.'));
            throw new RuntimeException('Recommendation service returned an invalid response.');
        }

        // Keep the response available even when an individual item is malformed.
        $this->lastRecommendDebug = [
            'available' => true,
            'status' => $response->status(),
            'raw' => $payload,
        ];

        $invalidCount = 0;
        $isbns = collect($recommendations)->map(function ($recommendation) use (&$invalidCount): ?string {
            $value = is_array($recommendation) ? ($recommendation['isbn'] ?? $recommendation['book_id'] ?? null) : null;
            // Some model versions serialize ISBNs as JSON integers. Convert
            // integers to strings before matching; never accept floats, which
            // can silently lose digits or use scientific notation.
            if (is_int($value)) {
                $value = (string) $value;
            }
            if (! is_string($value)) {
                $invalidCount++;
                return null;
            }

            $isbn = trim($value);
            if ($isbn === '' || strlen($isbn) > 32) {
                $invalidCount++;
                return null;
            }

            return $isbn;
        })->filter()->unique()->take(10)->values()->all();

        if ($recommendations !== [] && $isbns === []) {
            $this->lastRecommendDebug = ['available' => false, 'status' => $response->status(), 'raw' => $payload, 'reason' => 'invalid_response'];
            throw new RuntimeException('Recommendation service returned no valid ISBNs.');
        }

        $this->lastRecommendDebug = [
            'endpoint' => $endpointUrl,
            'available' => true,
            'status' => $response->status(),
            'raw' => $payload,
            'isbns' => $isbns,
            'invalid_count' => $invalidCount,
        ];

        return UserRecommendation::storeRecommendations($account->id, $isbns);
    }

    /** Return semantically ranked catalog ISBNs; an empty list lets OPAC use its local search. */
    public function searchBookIsbns(string $query): array
    {
        try {
            $response = $this->client()->connectTimeout(1)->timeout(5)
                ->post($this->endpoint('/api/search'), ['query' => $query, 'top_n' => 100]);
            if (! $response->successful()) {
                return [];
            }
            $results = $response->json('results');
            if (! is_array($results)) {
                return [];
            }
            return collect($results)->pluck('isbn')->filter(fn ($isbn) => is_string($isbn) && $isbn !== '')
                ->unique()->take(100)->values()->all();
        } catch (Throwable $exception) {
            report($exception);
            return [];
        }
    }

    /**
     * Read the recommender's evaluation snapshot and persistent history for the admin analytics page.
     *
     * Syncs all evaluation runs from `GET /api/metrics/history` (`model_metrics_history.jsonl`)
     * as well as the latest snapshot from `GET /api/metrics` (`model_metrics.json`) into
     * `ml_evaluation_runs` (and a local JSON fallback) so the dashboard always has saved data
     * even when the ML service is offline.
     *
     * @return array<string, mixed>
     */
    public function evaluation(): array
    {
        return Cache::remember($this->evaluationCacheKey(), now()->addSeconds(60), function (): array {
            return $this->fetchEvaluation();
        });
    }

    public function clearEvaluationCache(): void
    {
        Cache::forget('recommender:evaluation');
        Cache::forget($this->evaluationCacheKey());
    }

    protected function evaluationCacheKey(): string
    {
        $metricsEndpoint = $this->endpoint((string) config('services.recommender.evaluation_path', '/api/metrics'));
        $historyEndpoint = $this->endpoint((string) config('services.recommender.evaluation_history_path', '/api/metrics/history'));

        return 'recommender:evaluation:' . md5($metricsEndpoint . '|' . $historyEndpoint);
    }

    protected function fetchEvaluation(): array
    {
        $checkedAt = now()->toIso8601String();
        $endpointUrl = $this->endpoint((string) config('services.recommender.evaluation_path', '/api/metrics'));
        $historyEndpointUrl = $this->endpoint((string) config('services.recommender.evaluation_history_path', '/api/metrics/history'));

        $metricsResponse = null;
        $historyResponse = null;
        $connectionError = null;

        try {
            $metricsResponse = $this->client()
                ->connectTimeout(1)
                ->timeout(3)
                ->get($endpointUrl);
        } catch (ConnectionException $exception) {
            report($exception);
            $connectionError = 'The recommendation service could not be reached.';
        } catch (Throwable $exception) {
            report($exception);
            $connectionError = 'The recommendation service is temporarily unavailable.';
        }

        try {
            $historyResponse = $this->client()
                ->connectTimeout(1)
                ->timeout(3)
                ->get($historyEndpointUrl);
        } catch (Throwable $exception) {
            report($exception);
            if ($connectionError === null) {
                $connectionError = 'The recommendation service could not be reached.';
            }
        }

        $historyPayload = $historyResponse && $historyResponse->successful() ? $historyResponse->json() : null;
        $historyEntries = $this->extractHistoryEntries($historyPayload);
        $syncedRunsCount = 0;
        $latestHistoryMetrics = [];
        $latestHistoryEvaluatedAt = null;

        if ($historyEntries !== []) {
            // `/api/metrics/history` returns newest evaluations first; sync oldest-to-newest.
            $chronologicalEntries = array_reverse($historyEntries);
            $lastIndex = count($chronologicalEntries) - 1;

            foreach ($chronologicalEntries as $idx => $entry) {
                $entryMetrics = $this->normalizeMetrics($entry);
                if ($entryMetrics === []) {
                    continue;
                }

                $entryEvaluatedAt = $entry['evaluated_at']
                    ?? $entry['timestamp']
                    ?? $entry['created_at']
                    ?? ($entry['metrics']['evaluated_at'] ?? null);

                try {
                    $shouldBump = ($idx === $lastIndex) && (! $metricsResponse || ! $metricsResponse->successful());
                    MlEvaluationRun::record($entryMetrics, $entry, $entryEvaluatedAt, bumpCheck: $shouldBump);
                    $syncedRunsCount++;
                } catch (Throwable $exception) {
                    report($exception);
                }

                if ($idx === $lastIndex) {
                    $latestHistoryMetrics = $entryMetrics;
                    $latestHistoryEvaluatedAt = $entryEvaluatedAt;
                }
            }
        }

        $metricsPayload = $metricsResponse && $metricsResponse->successful() ? $metricsResponse->json() : null;
        $metricsPayload = is_array($metricsPayload) ? $metricsPayload : [];
        $liveMetrics = $metricsPayload !== []
            ? $this->normalizeMetrics($metricsPayload)
            : [];
        $liveEvaluatedAt = $metricsPayload['evaluated_at']
            ?? $metricsPayload['timestamp']
            ?? ($metricsPayload['metrics']['evaluated_at'] ?? null)
            ?? $latestHistoryEvaluatedAt;

        if ($liveMetrics !== []) {
            try {
                MlEvaluationRun::record($liveMetrics, $metricsPayload, $liveEvaluatedAt, bumpCheck: true);
                $syncedRunsCount = max(1, $syncedRunsCount);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $resolvedMetrics = $liveMetrics !== [] ? $liveMetrics : $latestHistoryMetrics;

        if ($resolvedMetrics !== []) {
            MlEvaluationRun::saveLocalFallback();

            return [
                'available' => true,
                'endpoint' => $endpointUrl,
                'history_endpoint' => $historyEndpointUrl,
                'status' => $metricsResponse?->status(),
                'history_status' => $historyResponse?->status(),
                'evaluated_at' => $liveEvaluatedAt,
                'synced_runs' => $syncedRunsCount,
                'raw' => $metricsPayload ?: ($metricsResponse?->json() ?? $metricsResponse?->body()),
                'raw_history' => $historyPayload ?? ($historyResponse?->json() ?? $historyResponse?->body()),
                'metrics' => $resolvedMetrics,
                'checked_at' => $checkedAt,
                'fallback_available' => true,
                'error' => null,
            ];
        }

        // Neither endpoint returned live metrics — ensure local fallback is restored into DB if needed.
        MlEvaluationRun::restoreFromLocalFallbackIfEmpty();

        if ($metricsResponse === null && $historyResponse === null) {
            return $this->unavailableEvaluation(
                $connectionError ?? 'The recommendation service could not be reached.',
                $checkedAt,
                $endpointUrl,
                null,
                null,
                $historyEndpointUrl,
                null,
                null
            );
        }

        $status = $metricsResponse?->status() ?? $historyResponse?->status();
        $rawBody = $metricsResponse ? ($metricsResponse->json() ?? $metricsResponse->body()) : null;
        $rawHistoryBody = $historyResponse ? ($historyResponse->json() ?? $historyResponse->body()) : null;

        if ($metricsResponse && ! $metricsResponse->successful()) {
            report(new RuntimeException('Recommendation evaluation failed with HTTP ' . $metricsResponse->status() . '.'));

            $apiErrorMsg = is_array($rawBody) && isset($rawBody['error']) && is_string($rawBody['error'])
                ? $rawBody['error']
                : null;

            $errorMessage = match ($metricsResponse->status()) {
                404 => $apiErrorMsg ?: 'The recommender is reachable, but its configured evaluation endpoint was not found.',
                401, 403 => 'The recommender rejected the API key. Check the Laravel and recommender API key configuration.',
                default => $apiErrorMsg ?: 'The recommendation service is temporarily unavailable.',
            };

            return $this->unavailableEvaluation(
                $errorMessage,
                $checkedAt,
                $endpointUrl,
                $status,
                $rawBody,
                $historyEndpointUrl,
                $historyResponse?->status(),
                $rawHistoryBody
            );
        }

        return $this->unavailableEvaluation(
            'The recommendation service responded without any evaluation metrics.',
            $checkedAt,
            $endpointUrl,
            $status,
            $rawBody,
            $historyEndpointUrl,
            $historyResponse?->status(),
            $rawHistoryBody
        );
    }

    /**
     * Extract the array of history run objects from `/api/metrics/history`.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function extractHistoryEntries(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        foreach (['history', 'evaluations', 'runs', 'records', 'items', 'data', 'metrics_history'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key]) && array_is_list($payload[$key])) {
                return array_values(array_filter($payload[$key], 'is_array'));
            }
        }

        return [];
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
        $readMetric = function (array $values) use (&$readMetric, &$metrics, $aliases): void {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $readMetric($value);
                    continue;
                }

                $normalizedKey = str_replace([' ', '_', '-'], '', strtolower((string) $key));
                $label = $aliases[$normalizedKey] ?? null;

                if ($label !== null && is_numeric($value) && ! array_key_exists($label, $metrics)) {
                    $metrics[$label] = round((float) $value, 4);
                }
            }
        };

        $readMetric($raw);

        // The recommender reports these descriptive metrics in its absolute
        // error statistics object rather than at the response root.
        $errorStatistics = $raw['absolute_error_statistics'] ?? null;
        if (is_array($errorStatistics)) {
            foreach ([
                'mean' => 'Mean',
                'median' => 'Median',
                'standard_deviation' => 'Standard deviation',
            ] as $key => $label) {
                if (! array_key_exists($label, $metrics) && is_numeric($errorStatistics[$key] ?? null)) {
                    $metrics[$label] = round((float) $errorStatistics[$key], 4);
                }
            }
        }

        // Some service versions wrap the payload under `metrics`.
        if (is_array($raw['metrics'] ?? null)) {
            $readMetric($raw['metrics']);
        }

        /*
         * Re-apply the preferred order after recursively reading nested
         * payloads. This also prevents unrelated statistics from becoming
         * dashboard metrics.
         */
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
        mixed $raw = null,
        ?string $historyEndpoint = null,
        ?int $historyStatus = null,
        mixed $rawHistory = null
    ): array {
        $latestSaved = null;
        $savedCount = 0;

        try {
            $latestSaved = MlEvaluationRun::query()->latest('evaluated_at')->latest('id')->first();
            $savedCount = MlEvaluationRun::query()->count();
        } catch (Throwable) {
            // Ignore DB lookup errors when building fallback status.
        }

        return [
            'available' => false,
            'endpoint' => $endpoint,
            'history_endpoint' => $historyEndpoint,
            'status' => $status,
            'history_status' => $historyStatus,
            'raw' => $raw,
            'raw_history' => $rawHistory,
            'metrics' => $latestSaved ? $latestSaved->metrics() : [],
            'evaluated_at' => $latestSaved?->evaluated_at?->copy()->utc()->toIso8601String(),
            'fallback_available' => $latestSaved !== null,
            'saved_runs_count' => $savedCount,
            'checked_at' => $checkedAt,
            'error' => $error,
        ];
    }

    protected function client()
    {
        return Http::acceptJson()->withHeaders([
            'X-API-Key' => (string) config('services.recommender.key'),
        ])->withToken((string) config('services.recommender.key'));
    }

    protected function endpoint(string $path): string
    {
        $url = rtrim((string) config('services.recommender.url'), '/');
        $configuredEvaluationPath = trim((string) config('services.recommender.evaluation_path', '/api/metrics'), '/');
        $configuredHistoryPath = trim((string) config('services.recommender.evaluation_history_path', '/api/metrics/history'), '/');
        $urlPath = trim((string) parse_url($url, PHP_URL_PATH), '/');

        foreach ([$configuredHistoryPath, $configuredEvaluationPath] as $stripPath) {
            if ($stripPath !== '' && $urlPath === $stripPath) {
                $url = substr($url, 0, -strlen($stripPath) - 1);
                break;
            }
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
