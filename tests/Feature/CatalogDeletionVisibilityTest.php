<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\BookManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogDeletionVisibilityTest extends TestCase
{
    public function test_all_inventory_excludes_deleted_copies_and_deleted_title_metadata(): void
    {
        Schema::create('book_datas', function (Blueprint $table) { $table->id(); $table->softDeletes(); });
        Schema::create('book_details', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('book_data_id'); $table->softDeletes(); });
        Schema::create('books', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('book_detail_id'); $table->string('status'); $table->softDeletes(); });
        DB::table('book_datas')->insert([
            ['id' => 1, 'deleted_at' => null], ['id' => 2, 'deleted_at' => now()],
        ]);
        DB::table('book_details')->insert([
            ['id' => 1, 'book_data_id' => 1, 'deleted_at' => null],
            ['id' => 2, 'book_data_id' => 1, 'deleted_at' => now()],
            ['id' => 3, 'book_data_id' => 2, 'deleted_at' => null],
        ]);
        DB::table('books')->insert([
            ['id' => 1, 'book_detail_id' => 1, 'status' => 'available', 'deleted_at' => null],
            ['id' => 2, 'book_detail_id' => 1, 'status' => 'available', 'deleted_at' => now()],
            ['id' => 3, 'book_detail_id' => 2, 'status' => 'available', 'deleted_at' => null],
            ['id' => 4, 'book_detail_id' => 3, 'status' => 'available', 'deleted_at' => null],
        ]);

        $manager = new class extends BookManager {
            public function inventoryIds(): array { return $this->buildQuery()->pluck('books.id')->all(); }
        };
        $manager->inventoryView = 'Copies';
        $this->assertSame([1], $manager->inventoryIds());
        $manager->inventoryView = 'Titles';
        $this->assertSame([1], $manager->inventoryIds());
    }
}
