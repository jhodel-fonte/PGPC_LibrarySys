<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MlEvaluationRun extends Model
{
    protected $table = 'ml_evaluation_runs';

    protected const FALLBACK_FILE = 'ml_evaluation_fallback.json';

    /**
     * Display label => database column for every tracked metric.
     */
    public const METRICS = [
        'RMSE' => 'rmse',
        'MAE' => 'mae',
        'Mean' => 'mean',
        'Median' => 'median',
        'Standard deviation' => 'standard_deviation',
        'NDCG@10' => 'ndcg_at_10',
    ];

    protected $fillable = [
        'rmse',
        'mae',
        'mean',
        'median',
        'standard_deviation',
        'ndcg_at_10',
        'payload',
        'fingerprint',
        'times_checked',
        'evaluated_at',
        'last_checked_at',
    ];

    protected $casts = [
        'rmse' => 'float',
        'mae' => 'float',
        'mean' => 'float',
        'median' => 'float',
        'standard_deviation' => 'float',
        'ndcg_at_10' => 'float',
        'payload' => 'array',
        'times_checked' => 'integer',
        'evaluated_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Store or update an evaluation run snapshot.
     *
     * When the ML server provides an `evaluated_at` UTC timestamp (from
     * `model_metrics.json` or `model_metrics_history.jsonl`), each distinct
     * `evaluated_at` timestamp is stored as its own history record. Repeated
     * polls of the same `evaluated_at` run update `last_checked_at` and
     * `times_checked` without duplicating the row.
     */
    public static function record(
        array $metrics,
        array $payload = [],
        Carbon|string|null $evaluatedAt = null,
        bool $bumpCheck = true
    ): self {
        $metrics = self::completeMetricsFromPayload($metrics, $payload);
        $values = [];
        foreach (self::METRICS as $label => $column) {
            $values[$column] = isset($metrics[$label]) ? round((float) $metrics[$label], 4) : null;
        }

        $rawEvaluatedAt = $evaluatedAt
            ?? ($payload['evaluated_at'] ?? null)
            ?? ($payload['timestamp'] ?? null)
            ?? ($payload['created_at'] ?? null)
            ?? ($payload['metrics']['evaluated_at'] ?? null);

        $parsedEvaluatedAt = null;
        if ($rawEvaluatedAt instanceof Carbon) {
            $parsedEvaluatedAt = $rawEvaluatedAt->copy()->utc();
        } elseif (is_string($rawEvaluatedAt) && trim($rawEvaluatedAt) !== '') {
            try {
                $parsedEvaluatedAt = Carbon::parse($rawEvaluatedAt)->utc();
            } catch (Throwable) {
                $parsedEvaluatedAt = null;
            }
        }

        if ($parsedEvaluatedAt !== null) {
            $canonicalTime = $parsedEvaluatedAt->format('Y-m-d\TH:i:s.u\Z');
            $fingerprint = hash('sha256', json_encode([
                'evaluated_at' => $canonicalTime,
                'metrics' => $values,
            ]));

            $existing = static::query()
                ->where('fingerprint', $fingerprint)
                ->orWhere('evaluated_at', $parsedEvaluatedAt->toDateTimeString())
                ->first();

            if ($existing) {
                $updates = [
                    'fingerprint' => $fingerprint,
                    'last_checked_at' => now(),
                ];
                foreach ($values as $column => $value) {
                    if ($existing->{$column} === null && $value !== null) {
                        $updates[$column] = $value;
                    }
                }
                if ($bumpCheck) {
                    $updates['times_checked'] = $existing->times_checked + 1;
                }
                if (! empty($payload) && empty($existing->payload)) {
                    $updates['payload'] = $payload;
                }

                $existing->forceFill($updates)->save();

                return $existing;
            }

            return static::create($values + [
                'payload' => $payload,
                'fingerprint' => $fingerprint,
                'times_checked' => 1,
                'evaluated_at' => $parsedEvaluatedAt,
                'last_checked_at' => now(),
            ]);
        }

        $fingerprint = hash('sha256', json_encode($values));
        $latest = static::query()->latest('evaluated_at')->latest('id')->first();

        if ($latest && $latest->fingerprint === $fingerprint) {
            $latest->forceFill([
                'times_checked' => $bumpCheck ? $latest->times_checked + 1 : $latest->times_checked,
                'last_checked_at' => now(),
            ])->save();

            return $latest;
        }

        return static::create($values + [
            'payload' => $payload,
            'fingerprint' => $fingerprint,
            'times_checked' => 1,
            'evaluated_at' => now(),
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Fill descriptive metrics that older recommender responses nested inside
     * their absolute-error statistics object.
     */
    protected static function completeMetricsFromPayload(array $metrics, array $payload): array
    {
        $statistics = $payload['absolute_error_statistics'] ?? null;
        if (! is_array($statistics)) {
            return $metrics;
        }

        foreach ([
            'mean' => 'Mean',
            'median' => 'Median',
            'standard_deviation' => 'Standard deviation',
        ] as $key => $label) {
            if (! array_key_exists($label, $metrics) && is_numeric($statistics[$key] ?? null)) {
                $metrics[$label] = (float) $statistics[$key];
            }
        }

        return $metrics;
    }

    /**
     * Persist a local JSON backup of the recent evaluation runs so that if the
     * ML service is unreachable and the database was reset, history can still be restored.
     */
    public static function saveLocalFallback(): void
    {
        try {
            $runs = static::query()
                ->latest('evaluated_at')
                ->latest('id')
                ->limit(100)
                ->get()
                ->map(fn (self $run) => [
                    'metrics' => $run->metrics(),
                    'payload' => $run->payload ?? [],
                    'evaluated_at' => $run->evaluated_at?->copy()->utc()->toIso8601String(),
                    'last_checked_at' => $run->last_checked_at?->copy()->utc()->toIso8601String(),
                    'times_checked' => $run->times_checked,
                ])
                ->all();

            if ($runs !== []) {
                Storage::disk('local')->put(self::FALLBACK_FILE, json_encode([
                    'saved_at' => now()->utc()->toIso8601String(),
                    'runs' => $runs,
                ], JSON_PRETTY_PRINT));
            }
        } catch (Throwable) {
            // Ignore filesystem errors on fallback write.
        }
    }

    /**
     * Restore saved runs from the local JSON fallback file when the table is empty.
     */
    public static function restoreFromLocalFallbackIfEmpty(): int
    {
        try {
            if (static::query()->exists()) {
                return 0;
            }

            if (! Storage::disk('local')->exists(self::FALLBACK_FILE)) {
                return 0;
            }

            $decoded = json_decode((string) Storage::disk('local')->get(self::FALLBACK_FILE), true);
            $runs = is_array($decoded['runs'] ?? null) ? array_reverse($decoded['runs']) : [];
            $restored = 0;

            foreach ($runs as $item) {
                if (! is_array($item) || ! is_array($item['metrics'] ?? null) || $item['metrics'] === []) {
                    continue;
                }

                static::record(
                    $item['metrics'],
                    is_array($item['payload'] ?? null) ? $item['payload'] : [],
                    $item['evaluated_at'] ?? null,
                    bumpCheck: false
                );
                $restored++;
            }

            return $restored;
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Metric values keyed by display label, skipping metrics the model did not report.
     */
    public function metrics(): array
    {
        $metrics = [];
        foreach (self::METRICS as $label => $column) {
            if ($this->{$column} !== null) {
                $metrics[$label] = $this->{$column};
            }
        }

        return $metrics;
    }

    /**
     * Dataset and score-distribution details returned by the recommender.
     *
     * @return array<string, mixed>
     */
    public function evaluationDetails(): array
    {
        $payload = is_array($this->payload) ? $this->payload : [];
        $training = is_array($payload['training_data'] ?? null) ? $payload['training_data'] : [];
        $evaluation = is_array($payload['evaluation'] ?? null) ? $payload['evaluation'] : [];

        return [
            'training_interactions' => $this->integerValue($training['training_interactions'] ?? null),
            'training_users' => $this->integerValue($training['training_users'] ?? null),
            'test_interactions' => $this->integerValue($evaluation['test_interactions'] ?? null),
            'test_users' => $this->integerValue($evaluation['test_users'] ?? null),
            'rating_scale' => is_array($evaluation['rating_scale'] ?? null) ? $evaluation['rating_scale'] : [],
            'actual' => $this->statistics($payload['actual_rating_statistics'] ?? null),
            'predicted' => $this->statistics($payload['predicted_rating_statistics'] ?? null),
        ];
    }

    /**
     * @return array<string, float|int|null>
     */
    protected function statistics(mixed $statistics): array
    {
        if (! is_array($statistics)) {
            return [];
        }

        return collect(['count', 'mean', 'median', 'min', 'max', 'standard_deviation'])
            ->mapWithKeys(fn (string $key) => [
                $key => is_numeric($statistics[$key] ?? null)
                    ? (in_array($key, ['count'], true) ? (int) $statistics[$key] : (float) $statistics[$key])
                    : null,
            ])
            ->all();
    }

    protected function integerValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
