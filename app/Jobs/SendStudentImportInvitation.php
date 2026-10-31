<?php

namespace App\Jobs;

use App\Models\StudentImportRow;
use App\Models\Account;
use App\Mail\StudentImportInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendStudentImportInvitation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $rowId) {}

    public function handle(): void
    {
        $row = StudentImportRow::findOrFail($this->rowId);
        if ($row->email_status === 'sent' || ! $row->account_id) return;
        $account = Account::findOrFail($row->account_id);
        $token = Password::broker()->createToken($account);
        $url = \App\Support\EmailUrl::route('password.reset', ['token' => $token, 'email' => $account->email]);
        Mail::to($account->email)->send(new StudentImportInvitation($account, $url));
        $row->update(['email_status' => 'sent', 'email_error' => null]);
    }

    public function failed(?Throwable $exception): void
    {
        StudentImportRow::whereKey($this->rowId)->update(['email_status' => 'failed', 'email_error' => 'Invitation delivery failed. Retry from the import page.']);
    }
}
