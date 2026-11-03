<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\StudentInterestAnalytics;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentInterestAnalyticsTest extends TestCase
{
    public function test_interest_counts_use_distinct_real_students_and_normalized_isbns(): void
    {
        Schema::create('accounts', function (Blueprint $table) { $table->id(); $table->softDeletes(); });
        Schema::create('students', function (Blueprint $table) { $table->id(); $table->integer('account_id'); $table->softDeletes(); });
        Schema::create('user_histories', function (Blueprint $table) { $table->id(); $table->integer('user_id'); $table->string('isbn'); $table->integer('rating'); $table->boolean('is_seeded'); });
        Schema::create('book_datas', function (Blueprint $table) { $table->id(); $table->string('book_title'); $table->softDeletes(); });
        Schema::create('book_details', function (Blueprint $table) { $table->id(); $table->integer('book_data_id'); $table->string('isbn'); $table->string('catalog_kind'); $table->softDeletes(); });
        Schema::create('categories', function (Blueprint $table) { $table->id(); $table->string('name'); $table->softDeletes(); });
        Schema::create('book_data_category', function (Blueprint $table) { $table->integer('book_data_id'); $table->integer('category_id'); });

        foreach (range(1, 4) as $id) {
            DB::table('accounts')->insert(['id' => $id]);
            DB::table('students')->insert(['account_id' => $id]);
            DB::table('user_histories')->insert(['user_id' => $id, 'isbn' => '123-45', 'rating' => $id === 1 ? 2 : 1, 'is_seeded' => $id === 4]);
        }
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Databases']);
        DB::table('book_details')->insert(['book_data_id' => 1, 'isbn' => '12345', 'catalog_kind' => 'book']);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Computing']);
        DB::table('book_data_category')->insert(['book_data_id' => 1, 'category_id' => 1]);

        $data = (new StudentInterestAnalytics)->render()->getData();
        $this->assertSame(3, $data['students']);
        $this->assertSame(1, $data['borrowers']);
        $this->assertSame(1, $data['books']);
        $this->assertSame(2, $data['viewOnly']);
        $this->assertSame(1, $data['borrowed']);
        $this->assertSame('Computing', $data['categories']->first()->name);
        $this->assertSame(3, (int) $data['categories']->first()->students);
        $this->assertSame('Databases', $data['titles']->first()->book_title);
    }
}
