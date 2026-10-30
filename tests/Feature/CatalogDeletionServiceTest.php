<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookDetail;
use App\Services\CatalogDeletionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogDeletionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'null']);
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_data_id')->default(1); $t->string('catalog_kind')->default('book'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('status'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('borrowing_transactions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_id'); $t->dateTime('return_date')->nullable(); });
        Schema::create('book_reservations', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_id'); $t->integer('reservation_status_id'); $t->softDeletes(); });
        Schema::create('reservation_waitlist', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('status'); });
        Schema::create('fines', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('borrowing_transaction_id'); });
        DB::table('book_details')->insert(['id' => 1]);
        DB::table('books')->insert([['id' => 1, 'book_detail_id' => 1, 'status' => 'borrowed'], ['id' => 2, 'book_detail_id' => 1, 'status' => 'available']]);
        DB::table('borrowing_transactions')->insert(['id' => 1, 'book_id' => 1, 'return_date' => null]);
        DB::table('fines')->insert(['id' => 1, 'borrowing_transaction_id' => 1]);
    }

    public function test_copy_deletion_requires_confirmation_and_preserves_title_and_loan(): void
    {
        $service = app(CatalogDeletionService::class);
        try {
            $service->delete('copy', [1], false);
            $this->fail('A borrowed copy requires confirmation.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('DELETE', $e->getMessage());
        }
        $this->assertFalse(Book::withTrashed()->findOrFail(1)->trashed());
        $service->delete('copy', [1], true);
        $this->assertTrue(Book::withTrashed()->findOrFail(1)->trashed());
        $this->assertFalse(Book::findOrFail(2)->trashed());
        $this->assertNotNull(BookDetail::find(1));
        $this->assertDatabaseHas('borrowing_transactions', ['id' => 1, 'book_id' => 1]);
        $this->assertDatabaseHas('fines', ['id' => 1, 'borrowing_transaction_id' => 1]);
    }

    public function test_title_deletion_preserves_records_and_deletes_all_active_copies(): void
    {
        app(CatalogDeletionService::class)->delete('title', [1], true);
        $this->assertTrue(BookDetail::withTrashed()->findOrFail(1)->trashed());
        $this->assertSame(2, Book::onlyTrashed()->count());
        $this->assertDatabaseHas('borrowing_transactions', ['id' => 1, 'book_id' => 1]);
        $this->assertDatabaseHas('fines', ['id' => 1, 'borrowing_transaction_id' => 1]);
        $returnedCopy = Book::withTrashed()->with(['bookDetail' => fn ($query) => $query->withTrashed()])->findOrFail(1);
        $this->assertTrue($returnedCopy->bookDetail->trashed());
        $returnedCopy->update(['status' => 'available']);
        $this->assertTrue($returnedCopy->fresh()->trashed());
    }
    public function test_periodical_title_deletion_preserves_loans_and_fines_for_all_issues(): void
    {
        DB::table('book_details')->where('id', 1)->update(['catalog_kind' => 'journal']);
        DB::table('book_details')->insert(['id' => 2, 'book_data_id' => 1, 'catalog_kind' => 'journal']);
        DB::table('books')->insert(['id' => 3, 'book_detail_id' => 2, 'status' => 'available']);
        $service = app(CatalogDeletionService::class);
        $this->assertSame(3, $service->inspect('title', [1])['copies']);
        $service->delete('title', [1], true);
        $this->assertSame(2, BookDetail::onlyTrashed()->count());
        $this->assertSame(3, Book::onlyTrashed()->count());
        $this->assertDatabaseHas('borrowing_transactions', ['id' => 1, 'book_id' => 1]);
        $this->assertDatabaseHas('fines', ['id' => 1, 'borrowing_transaction_id' => 1]);
    }
}
