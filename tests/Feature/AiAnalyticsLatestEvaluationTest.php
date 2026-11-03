<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\AiAnalytics;
use App\Models\MlEvaluationRun;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AiAnalyticsLatestEvaluationTest extends TestCase
{
    public function test_history_is_used_when_latest_endpoint_has_no_metrics(): void
    {
        Schema::dropIfExists('ml_evaluation_runs');
        (require database_path('migrations/2026_10_29_000004_create_ml_evaluation_runs_table.php'))->up();
        Storage::fake('local');
        Cache::flush();
        Http::fake([
            '*/api/metrics/history' => Http::response(['history' => [[
                'evaluated_at' => '2026-11-03T00:00:00Z', 'rmse' => 0.2,
                'recommendation_evaluation' => ['candidate_catalog_books' => 1768],
            ]]]),
            '*/api/metrics' => Http::response(['error' => 'No metrics', 'timestamp' => '2030-01-01T00:00:00Z']),
        ]);

        $result = app(RecommendationService::class)->evaluation();

        $this->assertTrue($result['available']);
        $this->assertSame('2026-11-03T00:00:00Z', $result['evaluated_at']);
        $this->assertSame(1768, $result['raw']['recommendation_evaluation']['candidate_catalog_books']);
    }

    public function test_connected_service_result_takes_priority_over_newer_saved_history(): void
    {
        Schema::dropIfExists('ml_evaluation_runs');
        (require database_path('migrations/2026_10_29_000004_create_ml_evaluation_runs_table.php'))->up();

        MlEvaluationRun::record(['RMSE' => 0.9], [
            'recommendation_evaluation' => ['candidate_catalog_books' => 10],
        ], '2030-01-01T00:00:00Z');

        $service = Mockery::mock(RecommendationService::class);
        $service->shouldReceive('evaluation')->once()->andReturn([
            'available' => true,
            'checked_at' => '2026-11-03T01:00:00Z',
            'evaluated_at' => '2026-11-03T00:00:00Z',
            'metrics' => ['RMSE' => 0.2],
            'raw' => ['training_data' => ['training_interactions' => 800],
                'recommendation_evaluation' => ['candidate_catalog_books' => 1768]],
        ]);
        $this->app->instance(RecommendationService::class, $service);

        $view = (new AiAnalytics)->render()->getData();

        $this->assertSame(1768, $view['latestRun']->payload['recommendation_evaluation']['candidate_catalog_books']);
        $this->assertSame(800, $view['evaluationDetails']['training_interactions']);
        $this->assertSame(10, $view['history']->first()->payload['recommendation_evaluation']['candidate_catalog_books']);
    }
}
