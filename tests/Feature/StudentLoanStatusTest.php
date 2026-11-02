<?php

namespace Tests\Feature;

use App\Models\BorrowingTransaction;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentLoanStatusTest extends TestCase
{
    public function test_deadline_status_handles_missing_dates_and_boundaries(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 10:00:00', 'Asia/Manila'));

        foreach ([
            [null, 'Due date unavailable'],
            ['2026-11-02 09:59:00', 'Overdue'],
            ['2026-11-02 17:00:00', 'Due today'],
            ['2026-11-03 09:00:00', 'Due in 1 day'],
            ['2026-11-05 09:00:00', 'Due in 3 days'],
        ] as [$due, $expected]) {
            $loan = new BorrowingTransaction(['due_date' => $due ? Carbon::parse($due, 'Asia/Manila') : null]);
            $this->blade('<x-student.loan-status :loan="$loan" />', ['loan' => $loan])->assertSee($expected);
        }

        Carbon::setTestNow();
    }
}
