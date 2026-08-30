<?php

namespace Tests\Unit\Circulation;

use Tests\TestCase;
use App\Livewire\Pages\Dashboard\ConfirmReturn;
use Carbon\Carbon;

class processBookReturnTest extends TestCase
{
    public function test_get_overdue_days_returns_zero_for_future_due_date(): void
    {
        $confirmReturn = new ConfirmReturn();
        $dueDate = Carbon::now()->addDays(5)->toDateTimeString();

        $overdueDays = $confirmReturn->getOverdueDays($dueDate);

        $this->assertEquals(0, $overdueDays);
    }

    public function test_get_overdue_days_returns_correct_count_for_past_due_date(): void
    {
        $confirmReturn = new ConfirmReturn();
        $dueDate = Carbon::now()->subDays(4)->toDateTimeString();

        $overdueDays = $confirmReturn->getOverdueDays($dueDate);

        $this->assertEquals(4, round($overdueDays));
    }

    public function test_get_fine_amount_calculates_correct_fine(): void
    {
        $confirmReturn = new ConfirmReturn();
        $dueDate = Carbon::now()->subDays(3)->toDateTimeString(); // 3 days overdue * 20 PHP fine = 60 PHP

        $fine = $confirmReturn->getFineAmount($dueDate);

        $this->assertEquals(60.00, round($fine, 2));
    }
}
