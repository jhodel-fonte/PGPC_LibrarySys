<?php

namespace Tests\Unit\Circulation;

use App\Livewire\Pages\Dashboard\ConfirmReturn;
use Carbon\Carbon;
use Tests\TestCase;

class ReturnWorkflowTest extends TestCase
{
    public function test_fine_calculation_uses_configured_daily_penalty(): void
    {
        config(['settings.library_config.penalty_fee' => 25]);

        $fine = (new ConfirmReturn())->getFineAmount(
            Carbon::now()->subDays(4)->toDateTimeString()
        );

        $this->assertSame(100.0, round($fine, 2));
    }

    public function test_future_due_date_has_no_overdue_days_or_fine(): void
    {
        $component = new ConfirmReturn();
        $dueDate = Carbon::now()->addDay()->toDateTimeString();

        $this->assertSame(0, $component->getOverdueDays($dueDate));
        $this->assertSame(0.0, $component->getFineAmount($dueDate));
    }
}
