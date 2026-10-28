<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before marking the job as failed.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying a failed attempt.
     */
    public int $backoff = 10;

    public function __construct(
        public string $phoneNumber,
        public string $message
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $result = $smsService->send($this->phoneNumber, $this->message);

        if (!$result['success']) {
            // If it's a configuration issue (token missing or disabled), don't endlessly retry
            if (in_array($result['status_code'], [200, 401, 422], true)) {
                Log::warning('[SendSmsJob] SMS was not dispatched due to configuration or validation.', [
                    'phone_number' => $this->phoneNumber,
                    'error' => $result['error'],
                ]);
                return;
            }

            // Otherwise throw an exception to let the queue retry
            throw new RuntimeException("iProgSMS delivery failed: " . ($result['error'] ?? 'Unknown error'));
        }
    }
}
