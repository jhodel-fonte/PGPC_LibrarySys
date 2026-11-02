<?php

namespace Tests\Unit;

use App\Livewire\Pages\Dashboard\Analytics;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class AnalyticsComparisonTest extends TestCase
{
    public function test_month_to_date_clamps_the_previous_month(): void
    {
        [$start, $end] = Analytics::previousRange('month', CarbonImmutable::parse('2026-03-01'), CarbonImmutable::parse('2026-03-31'));
        $this->assertSame('2026-02-01', $start->toDateString());
        $this->assertSame('2026-02-28', $end->toDateString());
    }

    public function test_year_to_date_handles_leap_day(): void
    {
        [$start, $end] = Analytics::previousRange('year', CarbonImmutable::parse('2024-01-01'), CarbonImmutable::parse('2024-02-29'));
        $this->assertSame('2023-01-01', $start->toDateString());
        $this->assertSame('2023-02-28', $end->toDateString());
    }

    public function test_last_month_compares_the_previous_calendar_month(): void
    {
        [$start, $end] = Analytics::previousRange('last_month', CarbonImmutable::parse('2026-03-01'), CarbonImmutable::parse('2026-03-31'));
        $this->assertSame('2026-02-01', $start->toDateString());
        $this->assertSame('2026-02-28', $end->toDateString());
    }

    public function test_custom_range_compares_the_preceding_equal_length(): void
    {
        [$start, $end] = Analytics::previousRange('custom', CarbonImmutable::parse('2026-03-01'), CarbonImmutable::parse('2026-03-03'));
        $this->assertSame('2026-02-26', $start->toDateString());
        $this->assertSame('2026-02-28', $end->toDateString());
    }

    public function test_comparison_handles_zero_and_decline(): void
    {
        $this->assertSame('No previous activity', Analytics::comparison(5, 0));
        $this->assertSame('No activity in either period', Analytics::comparison(0, 0));
        $this->assertSame('-50% vs previous period', Analytics::comparison(5, 10));
    }
}
