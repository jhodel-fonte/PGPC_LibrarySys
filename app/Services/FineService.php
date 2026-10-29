<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\Librarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FineService
{
    public function overdueDays($dueDate): int
    {
        $due = Carbon::parse($dueDate)->startOfDay();
        $today = now()->startOfDay();

        return $due->lessThan($today) ? (int) $due->diffInDays($today) : 0;
    }

    public function overdueAmount($dueDate, ?\App\Models\BookDetail $detail = null): float
    {
        $rate = $detail?->daily_fine ?? config('settings.library_config.penalty_fee', config('settings.penalty_fee', 50));
        return $this->overdueDays($dueDate) * (float) $rate;
    }

    public function confirmPayment(int $fineId, int $studentId, Librarian $librarian): void
    {
        DB::transaction(function () use ($fineId, $studentId, $librarian) {
            $fine = Fine::where('student_id', $studentId)->lockForUpdate()->findOrFail($fineId);

            if ($fine->fine_status !== 'unpaid') {
                throw new \DomainException('This fine is no longer unpaid.');
            }

            $now = now();
            $fine->payments()->create([
                'received_by_id' => $librarian->id,
                'payment_date' => $now,
                'payment_amount' => $fine->amount,
            ]);
            $fine->update(['fine_status' => 'paid', 'paid_at' => $now]);
        });
    }
}
