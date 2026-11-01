<?php

namespace Tests\Feature;

use App\Services\RecommendationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpacSemanticSearchCacheTest extends TestCase
{
    public function test_results_are_reused_per_query_until_expiry(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['results' => [['isbn' => '123'], ['isbn' => '456'], ['isbn' => '123']]])]);
        $service = app(RecommendationService::class);

        $this->assertSame(['123', '456'], $service->searchBookIsbns('coding'));
        $this->assertSame(['123', '456'], $service->searchBookIsbns('coding'));
        Http::assertSentCount(1);
        $service->searchBookIsbns('databases');
        Http::assertSentCount(2);
        $this->travel(61)->minutes();
        $service->searchBookIsbns('coding');
        Http::assertSentCount(3);
    }

    public function test_failures_and_empty_results_are_not_cached(): void
    {
        Cache::flush();
        Http::fakeSequence()->push([], 503)->push(['results' => []])->push(['results' => [['isbn' => '123']]]);
        $service = app(RecommendationService::class);

        $this->assertSame([], $service->searchBookIsbns('coding'));
        $this->assertSame([], $service->searchBookIsbns('coding'));
        $this->assertSame(['123'], $service->searchBookIsbns('coding'));
        Http::assertSentCount(3);
    }
}
