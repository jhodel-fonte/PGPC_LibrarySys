<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\MlEvaluationRun;
use App\Services\RecommendationService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'AI Analytics', 'subpage' => 'Machine Learning Evaluation'])]
class AiAnalytics extends Component
{
    use WithPagination;

    /** Number of saved runs plotted in each metric's trend line. */
    protected const TREND_POINTS = 20;

    /**
     * How to read each metric. `better` is 'lower', 'higher', or null when the
     * value is descriptive and has no good/bad direction.
     */
    protected const METRIC_INFO = [
        'RMSE' => ['better' => 'lower', 'description' => 'Root mean squared error of predicted ratings.'],
        'MAE' => ['better' => 'lower', 'description' => 'Average absolute gap between predicted and actual ratings.'],
        'Mean' => ['better' => null, 'description' => 'Average of the evaluated scores.'],
        'Median' => ['better' => null, 'description' => 'Middle value of the evaluated scores.'],
        'Standard deviation' => ['better' => null, 'description' => 'How widely the evaluated scores are spread.'],
        'NDCG@10' => ['better' => 'higher', 'description' => 'Ranking quality of the top 10 recommendations (0 to 1).'],
    ];

    public function refreshEvaluation(): void
    {
        $service = app(RecommendationService::class);
        $service->clearEvaluationCache();
        $evaluation = $service->evaluation();
        $this->dispatch('ml-api-evaluation', result: $evaluation);
        $this->resetPage();
    }

    public function render()
    {
        // Fetching also saves a new snapshot to the history when the values change.
        $evaluation = app(RecommendationService::class)->evaluation();

        $recentRuns = MlEvaluationRun::query()
            ->latest('evaluated_at')
            ->latest('id')
            ->limit(self::TREND_POINTS)
            ->get();

        $latestRun = $recentRuns->first();
        $previousRun = $recentRuns->get(1);
        $evaluationDetails = $latestRun?->evaluationDetails() ?? [
            'training_interactions' => null,
            'training_users' => null,
            'test_interactions' => null,
            'test_users' => null,
            'rating_scale' => [],
            'actual' => [],
            'predicted' => [],
        ];

        // Prefer live values; when the service is offline show the last saved run.
        $currentMetrics = ! empty($evaluation['metrics'])
            ? $evaluation['metrics']
            : ($latestRun?->metrics() ?? []);
        $comparisonRun = $evaluation['available'] && $latestRun && $latestRun->metrics() != $currentMetrics
            ? $latestRun
            : $previousRun;

        $chronological = $recentRuns->reverse()->values();
        $evaluationTrend = [
            'labels' => $chronological->map(fn (MlEvaluationRun $run) => $run->evaluated_at->format('M j'))->all(),
            'series' => collect(MlEvaluationRun::METRICS)
                ->map(function (string $column, string $label) use ($chronological) {
                    return [
                        'name' => $label,
                        'data' => $chronological->map(fn (MlEvaluationRun $run) => $run->{$column})->all(),
                    ];
                })
                ->filter(fn (array $series) => collect($series['data'])->contains(fn ($value) => $value !== null))
                ->values()
                ->all(),
        ];

        $metrics = collect($currentMetrics)->map(function (float $value, string $label) use ($comparisonRun, $chronological) {
            $info = self::METRIC_INFO[$label] ?? ['better' => null, 'description' => ''];
            $previous = $comparisonRun?->metrics()[$label] ?? null;
            $delta = $previous !== null ? round($value - $previous, 4) : null;

            $trend = 'neutral';
            if ($delta !== null && $delta != 0.0 && $info['better'] !== null) {
                $improved = $info['better'] === 'lower' ? $delta < 0 : $delta > 0;
                $trend = $improved ? 'better' : 'worse';
            }

            return [
                'label' => $label,
                'value' => $value,
                'description' => $info['description'],
                'better' => $info['better'],
                'delta' => $delta,
                'trend' => $trend,
                'sparkline' => $this->sparkline(
                    $chronological->map(fn (MlEvaluationRun $run) => $run->metrics()[$label] ?? null)->filter(fn ($v) => $v !== null)->values()->all()
                ),
            ];
        })->values();

        return view('livewire.pages.dashboard.ai-analytics', [
            'evaluation' => $evaluation,
            'checkedAt' => Carbon::parse($evaluation['checked_at']),
            'latestRun' => $latestRun,
            'evaluationDetails' => $evaluationDetails,
            'comparisonRun' => $comparisonRun,
            'metrics' => $metrics,
            'metricColumns' => MlEvaluationRun::METRICS,
            'evaluationTrend' => $evaluationTrend,
            'history' => MlEvaluationRun::query()->latest('evaluated_at')->latest('id')->cursorPaginate(10, ['*'], 'page'),
        ]);
    }

    /**
     * Build SVG polyline points (viewBox 0 0 120 32) scaled to the series' own range,
     * so small decimal changes stay visible.
     */
    protected function sparkline(array $values): ?string
    {
        if (count($values) < 2) {
            return null;
        }

        $width = 120;
        $height = 32;
        $padding = 3;
        $min = min($values);
        $max = max($values);
        $range = $max - $min;
        $step = $width / (count($values) - 1);

        return collect($values)->map(function ($value, $index) use ($step, $height, $padding, $min, $range) {
            $y = $range > 0
                ? $padding + (1 - (($value - $min) / $range)) * ($height - $padding * 2)
                : $height / 2;

            return round($index * $step, 2).','.round($y, 2);
        })->implode(' ');
    }
}
