<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected bool $enabled;

    public function __construct(?string $apiToken = null, ?string $apiUrl = null, ?bool $enabled = null)
    {
        $this->apiToken = $apiToken 
            ?? config('settings.sms.api_token') 
            ?? config('services.iprogsms.api_token') 
            ?? env('IPROG_SMS_API_TOKEN', env('IPROGSMS_API_TOKEN', env('SMS_API_TOKEN', '')));

        $this->apiUrl = $apiUrl 
            ?? config('settings.sms.api_url') 
            ?? config('services.iprogsms.url') 
            ?? env('IPROG_SMS_URL', 'https://www.iprogsms.com/api/v1/sms_messages');

        $this->enabled = $enabled 
            ?? (bool) (config('settings.sms.enabled') ?? config('services.iprogsms.enabled') ?? env('SMS_ENABLED', true));
    }

    /**
     * Send an SMS immediately using iProgSMS API.
     *
     * @param string $phoneNumber Target mobile number (supports 09..., +639..., 639...)
     * @param string $message Text content of the SMS
     * @return array{success: bool, status_code: int, phone_number: string, response: mixed, error: ?string}
     */
    public function send(string $phoneNumber, string $message): array
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);
        $message = trim($message);

        if (empty($formattedPhone)) {
            Log::warning('[iProgSMS] Cannot send SMS: Empty or invalid phone number.', [
                'raw_phone' => $phoneNumber,
            ]);

            return [
                'success' => false,
                'status_code' => 422,
                'phone_number' => $phoneNumber,
                'response' => null,
                'error' => 'Invalid or empty phone number provided.',
            ];
        }

        if (empty($message)) {
            Log::warning('[iProgSMS] Cannot send SMS: Message body is empty.', [
                'phone_number' => $formattedPhone,
            ]);

            return [
                'success' => false,
                'status_code' => 422,
                'phone_number' => $formattedPhone,
                'response' => null,
                'error' => 'SMS message content cannot be empty.',
            ];
        }

        if (!$this->enabled) {
            Log::info('[iProgSMS] SMS sending is disabled in configuration (SMS_ENABLED=false).', [
                'phone_number' => $formattedPhone,
                'message' => $message,
            ]);

            return [
                'success' => false,
                'status_code' => 200,
                'phone_number' => $formattedPhone,
                'response' => ['simulated' => true, 'note' => 'SMS is disabled in configuration.'],
                'error' => 'SMS sending is disabled via SMS_ENABLED=false.',
            ];
        }

        if (empty($this->apiToken)) {
            Log::error('[iProgSMS] API Token is missing. Please set IPROG_SMS_API_TOKEN in your .env file.', [
                'phone_number' => $formattedPhone,
            ]);

            return [
                'success' => false,
                'status_code' => 401,
                'phone_number' => $formattedPhone,
                'response' => null,
                'error' => 'iProgSMS API token is not configured. Set IPROG_SMS_API_TOKEN in .env',
            ];
        }

        try {
            // iProgSMS requires Content-Type: application/x-www-form-urlencoded
            $response = Http::asForm()
                ->timeout(15)
                ->post($this->apiUrl, [
                    'api_token'    => $this->apiToken,
                    'phone_number' => $formattedPhone,
                    'message'      => $message,
                ]);

            $isSuccessful = $response->successful();
            $responseBody = $response->json() ?? $response->body();

            if ($isSuccessful) {
                Log::info('[iProgSMS] SMS dispatched successfully.', [
                    'phone_number' => $formattedPhone,
                    'status' => $response->status(),
                    'response' => $responseBody,
                ]);
            } else {
                Log::error('[iProgSMS] Gateway returned error.', [
                    'phone_number' => $formattedPhone,
                    'status' => $response->status(),
                    'response' => $responseBody,
                ]);
            }

            return [
                'success' => $isSuccessful,
                'status_code' => $response->status(),
                'phone_number' => $formattedPhone,
                'response' => $responseBody,
                'error' => $isSuccessful ? null : ($response->body() ?: 'SMS request failed with status ' . $response->status()),
            ];
        } catch (Throwable $e) {
            Log::error('[iProgSMS] Exception occurred during SMS delivery: ' . $e->getMessage(), [
                'phone_number' => $formattedPhone,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'status_code' => 500,
                'phone_number' => $formattedPhone,
                'response' => null,
                'error' => 'Network/Connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize Philippine phone numbers to 639XXXXXXXXX format required by iProgSMS.
     *
     * Examples:
     *   "09171234567"   => "639171234567"
     *   "+639171234567" => "639171234567"
     *   "9171234567"    => "639171234567"
     *   "639171234567"  => "639171234567"
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Strip non-digit characters
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber) ?? '';

        if (empty($digits)) {
            return '';
        }

        // Handle leading 00 (international format e.g. 00639...)
        if (str_starts_with($digits, '0063')) {
            $digits = substr($digits, 2);
        }

        // Standard Philippine mobile numbers
        // If 11 digits starting with 09 (e.g. 09171234567) -> replace 0 with 63
        if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
            return '63' . substr($digits, 1);
        }

        // If 10 digits starting with 9 (e.g. 9171234567) -> prepend 63
        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '63' . $digits;
        }

        // If already 12 digits starting with 639 (e.g. 639171234567)
        if (strlen($digits) === 12 && str_starts_with($digits, '639')) {
            return $digits;
        }

        return $digits;
    }

    /**
     * Static helper: Send an SMS immediately.
     */
    public static function sendNow(string $phoneNumber, string $message): array
    {
        return (new self())->send($phoneNumber, $message);
    }

    /**
     * Static helper: Push SMS to the queue worker for background processing.
     */
    public static function queue(string $phoneNumber, string $message): void
    {
        SendSmsJob::dispatch($phoneNumber, $message);
    }

    /**
     * Static helper: Format a phone number.
     */
    public static function format(string $phoneNumber): string
    {
        return (new self())->formatPhoneNumber($phoneNumber);
    }
}
