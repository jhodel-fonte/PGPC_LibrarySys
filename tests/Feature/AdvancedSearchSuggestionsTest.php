<?php

namespace Tests\Feature;

use App\Http\Controllers\OpacController;
use App\Models\OpacCatalogView;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdvancedSearchSuggestionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('book_details', function (Blueprint $table) {
            $table->id(); $table->string('catalog_kind'); $table->softDeletes();
        });
        Schema::create('opac_catalog_view', function (Blueprint $table) {
            $table->integer('book_detail_id'); $table->integer('total_copies');
            $table->string('book_title'); $table->string('subtitle')->nullable();
            $table->string('authors'); $table->string('resource_type'); $table->string('format');
        });
        DB::table('book_details')->insert([['id' => 1, 'catalog_kind' => 'book'], ['id' => 2, 'catalog_kind' => 'book']]);
        DB::table('opac_catalog_view')->insert([
            ['book_detail_id' => 1, 'total_copies' => 1, 'book_title' => 'Introduction to Algorithms', 'authors' => 'Alice', 'resource_type' => 'Book', 'format' => 'Book'],
            ['book_detail_id' => 2, 'total_copies' => 1, 'book_title' => 'Introduction to Algebra', 'authors' => 'Bob', 'resource_type' => 'Book', 'format' => 'Book'],
        ]);
    }

    public function test_match_modes_and_title_suggestions_respect_filters(): void
    {
        $criteria = ['title' => 'Introduction to Algorithms', 'author' => 'Bob'];
        $this->assertSame(0, OpacCatalogView::advancedSearch($criteria, 'all')->count());
        $this->assertSame(2, OpacCatalogView::advancedSearch($criteria, 'any')->count());
        $this->assertSame(2, OpacCatalogView::advancedSearch(['type' => 'books'], 'all')->count());

        $filters = [
            'advTitle' => 'Introdution to Algorithms', 'advAuthor' => 'Alice', 'advSubject' => '',
            'advIsbn' => '', 'selectedType' => 'all', 'selectedAvailabilities' => [],
            'yearFrom' => null, 'yearTo' => null, 'advLocation' => '', 'matchType' => 'all', 'isAdvancedSearch' => true,
        ];
        $controller = new class extends OpacController {
            public function suggestions(array $filters): array { return $this->suggestTitles($filters); }
        };
        $this->assertSame(['Introduction to Algorithms'], $controller->suggestions($filters));
        $filters['advAuthor'] = 'Nobody';
        $this->assertSame([], $controller->suggestions($filters));
        $filters['matchType'] = 'any';
        $this->assertContains('Introduction to Algorithms', $controller->suggestions($filters));
        $filters['advTitle'] = 'zzzzzzzzzzzzzzzzzz';
        $this->assertSame([], $controller->suggestions($filters));
        $filters['isAdvancedSearch'] = false;
        $filters['search'] = 'Introdution to Algorithms';
        $filters['selectedSubjects'] = [];
        $this->assertContains('Introduction to Algorithms', $controller->suggestions($filters));
    }
}
