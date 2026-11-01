<?php

namespace Tests\Feature;

use Database\Seeders\BookConditionSeeder;
use Database\Seeders\BookTypeSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SpreadsheetBookSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpreadsheetBookSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_workbook_copies_without_duplicates(): void
    {
        $this->seed([BookConditionSeeder::class, BookTypeSeeder::class, LanguageSeeder::class]);
        $this->seed(SpreadsheetBookSeeder::class);
        $count = DB::table('books')->count();
        $this->seed(SpreadsheetBookSeeder::class);

        $this->assertDatabaseHas('books', ['accession_number' => '2452']);
        $this->assertLessThanOrEqual(1000, $count);
        $this->assertSame($count, DB::table('books')->count());
        $this->assertSame(1, DB::table('book_details')->where('isbn', '978-621-418-026-4')->count());
        $this->assertSame(4, DB::table('books')->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
            ->where('book_details.isbn', '978-621-418-026-4')->count());
    }
}
