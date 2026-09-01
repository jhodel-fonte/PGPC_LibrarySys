<?php

namespace App\Services;

use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OtpHandler
{
    /**
     * Generate a 6-digit OTP, store it for 10 minutes, and send the email.
     */
    public function sendOtp(string $email): void
    {
        $otp = sprintf("%06d", mt_rand(1, 999999));

        Cache::put('otp_registration_' . $email, $otp, now()->addMinutes(config('auth.otp.expiry')));

        Mail::to($email)->send(new OtpEmail($otp));
    }

    /**
     * Check if the provided OTP is valid and matches the cached value.
     */
    public function verifyOtp(string $email, string $inputOtp): bool
    {
        $cachedOtp = Cache::get('otp_registration_' . $email);

        if ($cachedOtp && $cachedOtp === $inputOtp) {
            // Remove the OTP immediately after a successful match to prevent reuse
            Cache::forget('otp_registration_' . $email);
            return true;
        }

        return false;
    }
}
