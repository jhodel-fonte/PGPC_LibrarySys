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
        Schema::create('borrowing_transactions', function (Blueprint $table) { $table->id(); $table->integer('school_id'); $table->integer('book_id'); $table->integer('borrow_type_id')->nullable(); $table->string('program_at_issue')->nullable(); $table->string('year_level_at_issue')->nullable(); $table->dateTime('issued_date'); $table->dateTime('due_date'); $table->dateTime('return_date')->nullable(); });
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
        $this->assertSame(1, $data['borrowerTrend']->last()->total);
        $this->assertSame(1, $data['programTrend']->last()->total);
        $this->assertSame(0, $data['borrowerTrend'][1]->total);
        $analytics->trendProgram = 'BSCS - Bachelor of Science in Computer Science';
        $this->assertSame(0, $analytics->render()->getData()['programTrend']->sum('total'));
        $analytics->trendProgram = '';
        $this->assertContains('At issue', $data['programs']->pluck('label')->all());
        $this->assertContains('Not recorded at issue', $data['programs']->pluck('label')->all());
        $this->assertCount(0, $data['genders']);
        $this->assertSame('A book', $data['topTitles']->first()->label);
        $this->assertSame(2, $data['topTitles']->first()->total);
        $this->assertContains('Not recorded at issue', $data['years']->pluck('label')->all());
        $html = $analytics->render()->render();
        $this->assertStringContainsString('Circulation trend', $html);
        $this->assertStringContainsString('#102B70', $html);
    }

    public function test_hourly_chart_keeps_all_bars_but_samples_axis_labels(): void
    {
        $labels = array_map(fn ($hour) => sprintf('%02d:00', $hour), range(0, 23));
        $html = $this->blade('<x-widget.graph.main :series="$series" :labels="$labels" bar grid />', [
            'series' => [['name' => 'Loans', 'data' => array_fill(0, 24, 0)]],
            'labels' => $labels,
        ])->render();

        $this->assertSame(24, substr_count($html, '<rect'));
        $this->assertSame(6, preg_match_all('/>\s*\d{2}:00\s*<\/text>/', $html));
        $this->assertStringContainsString('height="0"', $html);
    }

    public function test_column_chart_keeps_full_category_labels_and_zero_counts(): void
    {
        $rows = collect([(object) ['label' => 'BSCS - Computer Science', 'total' => 1], (object) ['label' => 'BPA - Public Administration', 'total' => 0]]);
        $html = $this->blade('<x-analytics.bars title="Loans by program" description="Selected period" :rows="$rows" chart="column" />', ['rows' => $rows])->render();

        $this->assertStringContainsString('BSCS - Computer Science', $html);
        $this->assertStringContainsString('BPA - Public Administration', $html);
        $this->assertSame(2, substr_count($html, '<rect'));
        $this->assertStringContainsString('height="0"', $html);
    }

    public function test_academic_categories_include_zero_and_preserve_unknown_values(): void
    {
        $rows = collect([(object) ['label' => 'BSCS', 'total' => 2], (object) ['label' => 'BSCS - Computer Science', 'total' => 1], (object) ['label' => 'Former program', 'total' => 3], (object) ['label' => 'Not recorded at issue', 'total' => 1]]);
        $data = Analytics::academicBreakdown($rows, ['BSCS - Computer Science' => ['BSCS', 'Computer Science'], 'BPA - Public Administration' => ['BPA']]);
        $this->assertSame(['BSCS - Computer Science', 'BPA - Public Administration', 'Former program', 'Not recorded at issue'], $data->pluck('label')->all());
        $this->assertSame([3, 0, 3, 1], $data->pluck('total')->all());
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
        $this->assertCount(count(config('settings.college.programs')), $data['programs']);
        $this->assertSame(0, $data['programs']->sum('total'));
        $this->assertCount(4, $data['years']);
        $this->assertSame(0, $data['years']->sum('total'));
    }
}
