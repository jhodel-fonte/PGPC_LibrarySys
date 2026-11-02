<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\Analytics;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['borrowing_transactions', 'students', 'books', 'book_details', 'book_datas'] as $table) Schema::dropIfExists($table);
        Schema::create('students', function (Blueprint $table) { $table->id(); $table->string('first_name'); $table->string('last_name'); $table->string('program')->nullable(); $table->string('gender')->nullable(); $table->softDeletes(); });
        Schema::create('book_datas', function (Blueprint $table) { $table->id(); $table->string('book_title'); $table->softDeletes(); });
        Schema::create('book_details', function (Blueprint $table) { $table->id(); $table->integer('book_data_id'); $table->softDeletes(); });
        Schema::create('books', function (Blueprint $table) { $table->id(); $table->integer('book_detail_id'); $table->softDeletes(); });
        Schema::create('borrowing_transactions', function (Blueprint $table) { $table->id(); $table->integer('school_id'); $table->integer('book_id'); $table->string('program_at_issue')->nullable(); $table->dateTime('issued_date'); $table->dateTime('due_date'); $table->dateTime('return_date')->nullable(); });
    }

    public function test_real_counts_compare_ranges_and_current_due_status(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-03-15 04:00:00', 'UTC'));
        DB::table('students')->insert(['id' => 1, 'first_name' => 'A', 'last_name' => 'Student', 'program' => 'Current', 'gender' => null]);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'A book']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        DB::table('books')->insert(['id' => 1, 'book_detail_id' => 1]);
        DB::table('borrowing_transactions')->insert([
            ['school_id' => 1, 'book_id' => 1, 'program_at_issue' => 'At issue', 'issued_date' => '2026-03-01 00:00:00', 'due_date' => '2026-03-15 03:00:00', 'return_date' => null],
            ['school_id' => 1, 'book_id' => 1, 'program_at_issue' => null, 'issued_date' => '2026-03-14 18:00:00', 'due_date' => '2026-03-16 00:00:00', 'return_date' => '2026-03-15 01:00:00'],
            ['school_id' => 1, 'book_id' => 1, 'program_at_issue' => null, 'issued_date' => '2026-02-03 00:00:00', 'due_date' => '2026-02-05 00:00:00', 'return_date' => '2026-02-04 00:00:00'],
        ]);
        $analytics = new Analytics;
        $analytics->appliedStart = '2026-03-01';
        $analytics->appliedEnd = '2026-03-15';
        $data = $analytics->render()->getData();
        $this->assertSame(['loans' => 2, 'borrowers' => 1, 'returns' => 1], $data['current']);
        $this->assertSame(1, $data['previous']['loans']);
        $this->assertSame(1, $data['dueCount']);
        $this->assertSame(1, $data['overdueCount']);
        $this->assertSame(1, $data['buckets']['2026-03-15']['loans']);
        $this->assertContains('At issue', $data['programs']->pluck('label')->all());
        $this->assertContains('Not recorded at issue', $data['programs']->pluck('label')->all());
        $this->assertSame('Unspecified', $data['genders']->first()->label);
    }

    public function test_empty_database_returns_zero_without_sample_records(): void
    {
        $analytics = new Analytics;
        $analytics->appliedStart = '2026-03-01';
        $analytics->appliedEnd = '2026-03-15';
        $data = $analytics->render()->getData();
        $this->assertSame(['loans' => 0, 'borrowers' => 0, 'returns' => 0], $data['current']);
        $this->assertSame(0, $data['dueCount']);
        $this->assertCount(0, $data['items']);
        $this->assertCount(0, $data['programs']);
    }
}
