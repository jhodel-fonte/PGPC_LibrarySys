<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dev', function () {
    $command = 'npm exec --no -- concurrently --kill-others'
        .' "php artisan serve" "php artisan queue:work --tries=1"'
        .' "php artisan queue:work --queue=imports --tries=1 --timeout=1800" "npm run dev"';
    $names = 'server,queue,imports,vite';

    if (function_exists('pcntl_fork')) {
        $command .= ' "php artisan pail --timeout=0"';
        $names .= ',logs';
    }

    return Process::fromShellCommandline($command.' --names='.$names, base_path())
        ->setTimeout(null)
        ->run(fn ($type, $output) => $this->output->write($output));
})->purpose('Start the local server, Vite, and application queue workers');

Artisan::command('local:start', function () {
    foreach (['npm run build', 'npm exec --no -- concurrently --kill-others "php artisan serve" "php artisan queue:work"'] as $command) {
        $process = Process::fromShellCommandline($command, base_path())->setTimeout(null);
        $process->run(fn ($type, $output) => $this->output->write($output));
        if (! $process->isSuccessful()) {
            return $process->getExitCode() ?: 1;
        }
    }

    return 0;
})->purpose('Build assets and start the local server and queue worker');

Artisan::command('sms:send {phone? : Target mobile number (e.g. 09171234567)} {message? : SMS message text} {--queue : Queue the SMS for background worker}', function (\App\Services\SmsService $smsService) {
    $phone = (string) ($this->argument('phone') ?? $this->ask('Enter the test mobile number (e.g. 09171234567)'));
    $message = (string) ($this->argument('message') ?? 'This is a Notification from PGPC Library System. If you have any questions, please contact us. Thank you. and have a great day! Also, this is a test message to check if the SMS gateway is working properly.');
    $isQueued = (bool) $this->option('queue');

    $formattedPhone = $smsService->formatPhoneNumber($phone);
    $this->info("Target Phone: {$phone} (formatted: {$formattedPhone})");
    $this->line("Message: \"{$message}\"");

    if ($isQueued) {
        \App\Services\SmsService::queue($phone, $message);
        $this->info("✓ SMS queued successfully into the database jobs table. Run 'php artisan queue:work' to process.");
        return 0;
    }

    $this->info("Sending immediately via iProgSMS...");
    $result = $smsService->send($phone, $message);

    if ($result['success']) {
        $this->info("✓ SMS accepted by iProgSMS for delivery.");
        $this->line("Response: " . json_encode($result['response'], JSON_PRETTY_PRINT));
        return 0;
    }

    $this->error($result['response'] === null || ($result['response']['simulated'] ?? false)
        ? '✗ SMS delivery failed before receiving a gateway response.'
        : "✗ SMS delivery failed (HTTP {$result['status_code']})");
    $this->error("Error: " . ($result['error'] ?? 'Unknown error'));
    if (!empty($result['response'])) {
        $this->line("Response: " . json_encode($result['response'], JSON_PRETTY_PRINT));
    }
    return 1;
})->purpose('Send an SMS notification via iProgSMS gateway');

Artisan::command('alert:send {user : Account ID, username, or email} {title : Alert heading} {message : Alert message body} {--type=info : Alert type (info, success, warning, danger)} {--book= : Related book title} {--due= : Due date} {--sms : Also dispatch SMS}', function () {
    $identifier = (string) $this->argument('user');
    $title = (string) $this->argument('title');
    $message = (string) $this->argument('message');
    $type = (string) $this->option('type');
    $bookTitle = $this->option('book') ? (string) $this->option('book') : null;
    $dueDate = $this->option('due') ? (string) $this->option('due') : null;
    $alsoSms = (bool) $this->option('sms');

    $account = is_numeric($identifier)
        ? \App\Models\Account::find((int) $identifier)
        : \App\Models\Account::where('username', $identifier)->orWhere('email', $identifier)->first();

    if (!$account) {
        $this->error("Account not found with identifier: {$identifier}");
        return 1;
    }

    $this->info("Dispatching alert notification to {$account->username} ({$account->email})...");
    if ($bookTitle) {
        $this->line("Book: \"{$bookTitle}\"");
    }

    $alert = new \App\Notifications\Alerts(
        title: $title,
        message: $message,
        type: $type,
        actionUrl: route('student.dashboard'),
        actionText: 'Open Dashboard',
        bookTitle: $bookTitle,
        dueDate: $dueDate,
        channels: ['database', 'mail'],
        sendSms: $alsoSms
    );

    $account->notify($alert);

    $this->info("✓ Alert notification dispatched to database (in-app) and queued for email" . ($alsoSms ? " and SMS" : "") . ".");
    return 0;
})->purpose('Dispatch an Alerts notification to a user account');

Artisan::command(
    'email:approval-test {recipient? : Target email address, username, or Account ID} {--result=approved : Approval result (approved or rejected)} {--remarks= : Reviewer remarks when result is rejected} {--queue : Queue the email instead of sending immediately}',
    function () {
        $identifier = trim((string) ($this->argument('recipient') ?? ''));
        $rawResult = strtolower(trim((string) $this->option('result')));
        $result = in_array($rawResult, ['rejected', 'not_approved', 'not-approved', 'declined'], true) ? 'rejected' : 'approved';
        $remarks = $this->option('remarks')
            ? trim((string) $this->option('remarks'))
            : ($result === 'rejected'
                ? 'The uploaded School ID image is blurry and the Student ID number is cut off. Please upload a clearer photo.'
                : null);
        $isQueued = (bool) $this->option('queue');

        $account = null;
        if ($identifier !== '') {
            $account = is_numeric($identifier)
                ? \App\Models\Account::with('student')->find((int) $identifier)
                : \App\Models\Account::with('student')
                    ->where('username', $identifier)
                    ->orWhere('email', $identifier)
                    ->first();
        }

        if (! $account) {
            $account = \App\Models\Account::with('student')
                ->whereHas('student')
                ->first() ?? \App\Models\Account::with('student')->first();
        }

        if (! $account) {
            $account = new \App\Models\Account([
                'username' => 'juan.delacruz',
                'email' => filter_var($identifier, FILTER_VALIDATE_EMAIL) ? $identifier : 'jhcyrene@gmail.com',
            ]);
            $account->setRelation('student', new \App\Models\Student([
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'school_id_number' => '2024-00142',
            ]));
        }

        $targetEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $identifier
            : ($account->email ?: 'jhcyrene@gmail.com');

        $this->info("Target Recipient : {$targetEmail}");
        $this->line("Account Username : {$account->username}");
        $this->line("Status Result    : {$result}");
        if ($remarks) {
            $this->line("Reviewer Remarks : \"{$remarks}\"");
        }

        try {
            $mailable = new \App\Mail\AccountApprovalResultEmail($account, $result, $remarks);

            if ($isQueued) {
                \Illuminate\Support\Facades\Mail::to($targetEmail)->queue($mailable);
                $this->info("✓ Approval status email queued successfully for {$targetEmail}. Run 'php artisan queue:work' to process.");
            } else {
                $this->info("Sending approval status email immediately...");
                \Illuminate\Support\Facades\Mail::to($targetEmail)->sendNow($mailable);
                $this->info("✓ Approval status email sent successfully to {$targetEmail}!");
            }

            return 0;
        } catch (\Throwable $e) {
            $this->error("✗ Failed to send approval status email: " . $e->getMessage());
            return 1;
        }
    }
)->purpose('Test sending the student registration approval/rejection result email');




\Illuminate\Support\Facades\Schedule::command('loans:send-due-reminders')->everyMinute()->withoutOverlapping();

Artisan::command('reservations:allocate-waitlist', function (\App\Services\ReservationWaitlistService $waitlist) {
    $waitlist->expireReservations();
    \App\Models\ReservationWaitlist::where('status', 'waiting')->distinct()->pluck('book_detail_id')
        ->each(fn ($detailId) => $waitlist->allocate($detailId));
    app(\App\Services\ReservationApprovalService::class)->approveDue();
})->purpose('Allocate available copies to reservation waitlists');

\Illuminate\Support\Facades\Schedule::command('reservations:allocate-waitlist')->everyMinute()->withoutOverlapping();
