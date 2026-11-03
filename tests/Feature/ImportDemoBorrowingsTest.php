<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportDemoBorrowingsTest extends TestCase
{
    public function test_import_is_seeded_repeatable_and_preserves_real_history(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ml_user_token')->nullable();
            $table->softDeletes();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->softDeletes();
        });
        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->string('isbn');
            $table->string('call_number');
            $table->string('classification')->nullable();
            $table->softDeletes();
        });
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_detail_id');
            $table->string('accession_number');
            $table->softDeletes();
        });
        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('encrypter_id')->nullable();
            $table->string('isbn');
            $table->integer('rating');
            $table->boolean('is_seeded')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'isbn']);
        });

        DB::table('accounts')->insert(['id' => 1, 'ml_user_token' => 'token']);
        DB::table('students')->insert(['account_id' => 1]);
        DB::table('book_details')->insert([
            ['id' => 1, 'isbn' => '111', 'call_number' => 'QA 1'],
            ['id' => 2, 'isbn' => '222', 'call_number' => 'QA 2'],
            ['id' => 3, 'isbn' => '333', 'call_number' => 'QA 3'],
            ['id' => 4, 'isbn' => '444', 'call_number' => 'QA 4'],
            ['id' => 5, 'isbn' => '555', 'call_number' => 'QA 5'],
        ]);
        DB::table('books')->insert([
            ['book_detail_id' => 1, 'accession_number' => 'A1'],
            ['book_detail_id' => 2, 'accession_number' => 'A2'],
            ['book_detail_id' => 3, 'accession_number' => '5549'],
            ['book_detail_id' => 4, 'accession_number' => '1234'],
            ['book_detail_id' => 5, 'accession_number' => '01234'],
        ]);
        DB::table('user_histories')->insert(['user_id' => 1, 'isbn' => '111', 'rating' => 1, 'is_seeded' => false]);
        $path = tempnam(sys_get_temp_dir(), 'loans');
        file_put_contents($path, "date,accession_number,source_image,page,review_note\n2025-01-01,A1,image.jpg,60,review\n2025-02-01,A2,image.jpg,60,review\n2025-02-01,05549,image.jpg,60,review\n2025-02-01,001234,image.jpg,60,review\n2025-02-01,missing,image.jpg,60,review\n");

        try {
            $this->artisan('demo:import-borrowings', ['csv' => $path])->assertSuccessful();
            $this->artisan('demo:import-borrowings', ['csv' => $path])->assertSuccessful();
            $this->assertDatabaseCount('user_histories', 3);
            $this->assertDatabaseHas('user_histories', ['isbn' => '111', 'rating' => 1, 'is_seeded' => false]);
            $this->assertDatabaseHas('user_histories', ['isbn' => '222', 'rating' => 2, 'is_seeded' => true]);
            $this->assertDatabaseHas('user_histories', ['isbn' => '333', 'rating' => 2, 'is_seeded' => true]);
            $this->assertDatabaseMissing('user_histories', ['isbn' => '444']);
            $this->assertDatabaseMissing('user_histories', ['isbn' => '555']);
        } finally {
            unlink($path);
        }
    }
}
