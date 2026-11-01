<?php

namespace Tests\Feature;

use App\Http\Controllers\OpacController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class OpacResponseCacheTest extends TestCase
{
    public function test_catalog_json_responses_are_not_stored(): void
    {
        $data = array_fill_keys([
            'items', 'totalResults', 'paginationData', 'availabilities', 'resourceTypes',
            'subjects', 'search', 'selectedType', 'selectedAvailabilities', 'selectedSubjects',
            'yearFrom', 'yearTo', 'sortBy', 'perPage',
        ], null);
        $data['items'] = [];
        $controller = Mockery::mock(OpacController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('executeCatalogQuery')->twice()->andReturn($data);
        $request = Request::create('/opac?search=coding&page=2', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);

        foreach (['index', 'advancedSearch'] as $method) {
            $response = $controller->$method($request);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame([], $response->getData(true)['results']);
        }
    }
}
