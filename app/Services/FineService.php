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

    public function paymentCalculation(Fine $fine, string $paymentDate): array
    {
        $loan = $fine->transaction;
        if (! $loan || (! $loan->return_date && ! $loan->lost_at) || ! $loan->due_date || $fine->fineType?->name !== 'Overdue') {
            throw new \DomainException('Only overdue fines for returned or lost items can be processed here.');
        }
        $due = $loan->due_date->copy()->startOfDay();
        $returned = ($loan->lost_at ?? $loan->return_date)->copy()->startOfDay();
        $paid = Carbon::parse($paymentDate)->startOfDay();
        if ($paid->greaterThan(now()->startOfDay()) || $paid->lessThan($due)) {
            throw new \DomainException('The payment date must be on or after the due date and not in the future.');
        }
        $returnDays = max(0, (int) $due->diffInDays($returned, false));
        if ($returnDays === 0) {
            throw new \DomainException('The recorded fine has no overdue days to calculate.');
        }
        $days = max(0, (int) $due->diffInDays($paid->min($returned), false));
        $rate = (float) $fine->amount / $returnDays;
        return ['days' => $days, 'rate' => $rate, 'amount' => round($days * $rate, 2)];
    }

    public function confirmOverduePayment(int $fineId, int $studentId, Librarian $librarian, string $paymentDate, string $receiptNumber): void
    {
        if (trim($receiptNumber) === '' || mb_strlen($receiptNumber) > 255 || ! \Illuminate\Support\Facades\Validator::make(['date' => $paymentDate], ['date' => 'required|date_format:Y-m-d'])->passes()) {
            throw new \DomainException('A valid payment date and receipt number are required.');
        }
        \App\Services\AuditTrail::transaction('Fine payment recorded', function () use ($fineId, $studentId, $librarian, $paymentDate, $receiptNumber) {
            $fine = Fine::where('student_id', $studentId)->lockForUpdate()->findOrFail($fineId);
            if ($fine->fine_status !== 'unpaid') {
                throw new \DomainException('This fine is no longer unpaid.');
            }
            $calculation = $this->paymentCalculation($fine, $paymentDate);
            $fine->payments()->create([
                'received_by_id' => $librarian->id,
                'payment_date' => $paymentDate,
                'payment_amount' => $calculation['amount'],
                'receipt_number' => trim($receiptNumber),
            ]);
            $fine->update(['amount' => $calculation['amount'], 'fine_status' => 'paid', 'paid_at' => now()]);
        });
    }

    public function confirmPayment(int $fineId, int $studentId, Librarian $librarian): void
    {
        \App\Services\AuditTrail::transaction('Fine marked paid', function () use ($fineId, $studentId, $librarian) {
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
