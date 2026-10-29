<?php

namespace App\Console\Commands;

use App\Models\BorrowingTransaction;
use App\Notifications\Alerts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDueReminders extends Command
{
    protected $signature = 'loans:send-due-reminders';
    protected $description = 'Queue advance reminders for outstanding loans';

    public function handle(): int
    {
        $days = max(1, (int) config('settings.notifications.due_reminder_days', 3));
        $now = now();

        BorrowingTransaction::query()
            ->whereNull('return_date')
            ->where('due_date', '>', $now)
            ->where('due_date', '<=', $now->copy()->addDays($days)->endOfDay())
            ->orderBy('id')
            ->chunkById(100, function ($loans) use ($now, $days) {
                foreach ($loans as $loan) {
                    DB::transaction(function () use ($loan, $now, $days) {
                        $loan = BorrowingTransaction::with(['student.account', 'book.bookDetail.bookData'])
                            ->lockForUpdate()->find($loan->id);
                        if (! $loan || $loan->return_date || ! $loan->due_date ||
                            $loan->due_date->lte($now) || $loan->due_date->gt($now->copy()->addDays($days)->endOfDay()) ||
                            ($loan->due_reminder_for && $loan->due_reminder_for->equalTo($loan->due_date))) {
                            return;
                        }

                        $account = $loan->student?->account;
                        if (! $account) {
                            return;
                        }

                        $title = $loan->book?->bookDetail?->bookData?->book_title ?? 'Borrowed book';
                        $dueDate = $loan->due_date->format('M d, Y');
                        $account->notify(new Alerts(
                            title: 'Book return reminder',
                            message: "Please return your borrowed book by {$dueDate}.",
                            type: 'warning',
                            bookTitle: $title,
                            dueDate: $dueDate,
                            sendSms: true,
                        ));
                        $loan->due_reminder_for = $loan->due_date;
                        $loan->save();
                    });
                }
            });

        return self::SUCCESS;
    }
}
