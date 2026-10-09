<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    /**
     * Validate the Cloudflare Turnstile token.
     *
     * @param string|null $token The cf-turnstile-response token.
     * @param string|null $expectedAction The expected action name (e.g., 'login').
     * @param string|null $clientIp Client IP address.
     * @return bool
     */
    public static function verify(?string $token, ?string $expectedAction = null, ?string $clientIp = null): bool
    {
        $secret = config('services.turnstile.secret');

        // Basic token validation
        if (empty($token) || ! is_string($token) || strlen($token) > 2048) {
            return false;
        }

        // If secret key is not set or still set to placeholder in development environment, pass in dev with a warning
        if (empty($secret) || $secret === 'your_cloudflare_secret_key') {
            Log::warning('Cloudflare Turnstile secret key is not configured in services.turnstile.secret or .env (TURNSTILE_SECRET_KEY).');
            if (app()->environment('local')) {
                return true;
            }
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $clientIp ?? request()->ip(),
                ]);

            if (! $response->successful()) {
                Log::error('Cloudflare Turnstile siteverify HTTP error: ' . $response->status());
                return false;
            }

            $data = $response->json();

            if (! ($data['success'] ?? false)) {
                Log::warning('Cloudflare Turnstile verification failed', [
                    'error-codes' => $data['error-codes'] ?? [],
                ]);
                return false;
            }

            // Verify action if supplied
            if ($expectedAction !== null && isset($data['action']) && $data['action'] !== $expectedAction) {
                Log::warning("Cloudflare Turnstile action mismatch: expected '{$expectedAction}', got '{$data['action']}'");
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Cloudflare Turnstile siteverify exception: ' . $e->getMessage());
            return false;
        }
    }
}
