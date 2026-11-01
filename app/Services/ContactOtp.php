<?php

namespace App\Services;

use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ContactOtp
{
    public function clearRecovery(int $accountId): void
    {
        foreach (['email', 'sms'] as $channel) Cache::forget($this->key('recovery', $accountId, $channel));
        $this->revokeRecoveryAuthorization($accountId);
    }

    public function revokeRecoveryAuthorization(int $accountId, ?string $sessionHash = null): void
    {
        $key = 'recovery-authorization:'.$accountId;
        $authorization = Cache::get($key);
        if ($sessionHash === null || (is_array($authorization) && hash_equals($authorization['session'], $sessionHash))) Cache::forget($key);
    }

    private function key(string $purpose, int $accountId, string $channel): string
    {
        return "contact-otp:$purpose:$accountId:$channel";
    }

    public function send(string $purpose, int $accountId, string $channel, string $destination, ?callable $sendEmail = null): void
    {
        $key = $this->key($purpose, $accountId, $channel);
        $normalized = $channel === 'sms' ? app(SmsService::class)->formatPhoneNumber($destination) : strtolower($destination);
        $destinationKey = 'otp-destination:'.hash('sha256', $channel.'|'.$normalized);

        Cache::lock($destinationKey.':lock', 30)->block(20, function () use ($key, $destinationKey, $channel, $destination, $sendEmail) {
            Cache::lock($key.':lock', 30)->block(20, function () use ($key, $destinationKey, $channel, $destination, $sendEmail) {
                if ($channel === 'sms' && RateLimiter::tooManyAttempts($destinationKey.':cooldown', 1)) {
                    $seconds = RateLimiter::availableIn($destinationKey.':cooldown');
                    throw ValidationException::withMessages(['code' => "Please wait $seconds seconds before requesting another SMS."]);
                }
                if (RateLimiter::tooManyAttempts($key.':send', 3) || RateLimiter::tooManyAttempts($destinationKey, 5) || RateLimiter::tooManyAttempts('otp-ip:'.request()->ip(), 10)) {
                    throw ValidationException::withMessages(['code' => 'Too many requests. Please wait before requesting another code.']);
                }
                RateLimiter::hit($key.':send', 600);
                RateLimiter::hit($destinationKey, 600);
                RateLimiter::hit('otp-ip:'.request()->ip(), 600);

                $stored = Cache::get($key);
                $destinationHash = hash_hmac('sha256', $destination, config('app.key'));
                $reuse = is_array($stored) && $stored['destination'] === $destinationHash && $stored['expires_at'] > now()->timestamp;
                $code = $reuse ? Crypt::decryptString($stored['code']) : sprintf('%06d', random_int(0, 999999));
                $expiresAt = $reuse ? $stored['expires_at'] : now()->addMinutes(10)->timestamp;

                if (! $reuse && ! Cache::put($key, ['code' => Crypt::encryptString($code), 'destination' => $destinationHash, 'expires_at' => $expiresAt], \Carbon\Carbon::createFromTimestamp($expiresAt))) {
                    throw new \RuntimeException('Could not store verification code.');
                }
                try {
                    if ($channel === 'email') {
                        if ($sendEmail) $sendEmail($code, $expiresAt);
                        else Mail::to($destination)->sendNow(new OtpEmail($code, 10));
                    } elseif (! app(SmsService::class)->send($destination, "PGPC Library verification code: $code. Expires 10 minutes after your first request. Do not share it.")['success']) {
                        throw new \RuntimeException('SMS delivery failed');
                    }
                } catch (\Throwable $e) {
                    if (! $reuse) Cache::forget($key);
                    throw $e;
                }
                if ($channel === 'sms') RateLimiter::hit($destinationKey.':cooldown', 120);
            });
        });
    }

    public function verify(string $purpose, int $accountId, string $channel, string $destination, string $code): bool
    {
        $key = $this->key($purpose, $accountId, $channel);
        $attemptKey = "contact-otp:$purpose:$accountId:attempts";
        return Cache::lock($attemptKey.':lock', 10)->block(5, function () use ($key, $attemptKey, $code, $destination) {
            return Cache::lock($key.':lock', 5)->block(3, function () use ($key, $attemptKey, $code, $destination) {
                if (RateLimiter::tooManyAttempts($attemptKey, max(1, (int) config('auth.otp.max_attempts', 5)))) return false;
                $expected = Cache::get($key);
                $valid = is_array($expected)
                    ? $expected['expires_at'] > now()->timestamp && hash_equals($expected['destination'], hash_hmac('sha256', $destination, config('app.key'))) && hash_equals(Crypt::decryptString($expected['code']), $code)
                    : is_string($expected) && hash_equals($expected, hash_hmac('sha256', $code.'|'.$destination, config('app.key')));
                if (! $valid) {
                    RateLimiter::hit($attemptKey, max(1, (int) config('auth.otp.lockout_seconds', 600)));
                    return false;
                }
                Cache::forget($key);
                RateLimiter::clear($attemptKey);
                return true;
            });
        });
    }
}
