<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlEvaluationRun extends Model
{
    protected $table = 'ml_evaluation_runs';

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
     * Store a snapshot of normalized metrics (keyed by display label).
     *
     * If the latest snapshot has identical values, the model has not changed,
     * so only its "last checked" timestamp is bumped instead of adding a duplicate row.
     */
    public static function record(array $metrics, array $payload = []): self
    {
        $values = [];
        foreach (self::METRICS as $label => $column) {
            $values[$column] = isset($metrics[$label]) ? round((float) $metrics[$label], 4) : null;
        }

        $fingerprint = hash('sha256', json_encode($values));
        $latest = static::query()->latest('evaluated_at')->latest('id')->first();

        if ($latest && $latest->fingerprint === $fingerprint) {
            $latest->forceFill([
                'times_checked' => $latest->times_checked + 1,
                'last_checked_at' => now(),
            ])->save();

            return $latest;
        }

        return static::create($values + [
            'payload' => $payload,
            'fingerprint' => $fingerprint,
            'evaluated_at' => now(),
            'last_checked_at' => now(),
        ]);
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
}

