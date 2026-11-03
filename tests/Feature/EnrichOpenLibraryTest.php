<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnrichOpenLibraryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('book_datas', function (Blueprint $table) {
            $table->id(); $table->string('subtitle')->nullable(); $table->text('description')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('book_details', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('book_data_id'); $table->string('isbn')->nullable();
            $table->integer('pages')->nullable(); $table->integer('publication_year')->nullable();
            $table->string('cover_image')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        DB::table('book_datas')->insert(['id' => 1, 'description' => 'Existing description']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1, 'isbn' => '978-0140328721', 'pages' => 99]);
        Http::fake(['openlibrary.org/api/books*' => Http::response([
            'ISBN:9780140328721' => ['details' => ['description' => 'New description', 'subtitle' => 'A subtitle', 'number_of_pages' => 50, 'publish_date' => '1996'], 'thumbnail_url' => 'https://covers.openlibrary.org/b/id/123-S.jpg'],
        ])]);
    }

    public function test_fills_only_missing_fields_and_dry_run_does_not_save(): void
    {
        $this->artisan('catalog:enrich-openlibrary', ['email' => 'library@example.org', '--dry-run' => true])->assertExitCode(0);
        $this->assertNull(DB::table('book_datas')->value('subtitle'));
        $this->artisan('catalog:enrich-openlibrary', ['email' => 'library@example.org'])->assertExitCode(0);
        $this->assertSame('Existing description', DB::table('book_datas')->value('description'));
        $this->assertSame('A subtitle', DB::table('book_datas')->value('subtitle'));
        $this->assertSame(99, DB::table('book_details')->value('pages'));
        $this->assertSame(1996, DB::table('book_details')->value('publication_year'));
        Http::assertSentCount(2);
    }

    public function test_http_failure_stops_without_updating(): void
    {
        Http::fake(['*' => Http::response([], 429)]);
        $this->artisan('catalog:enrich-openlibrary', ['email' => 'library@example.org'])->assertExitCode(1);
        $this->assertNull(DB::table('book_datas')->value('subtitle'));
    }
}
