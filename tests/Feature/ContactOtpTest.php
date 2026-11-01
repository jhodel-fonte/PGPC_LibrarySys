<?php

namespace Tests\Feature;

use App\Mail\OtpEmail;
use App\Services\ContactOtp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContactOtpTest extends TestCase
{
    public function test_codes_are_bound_to_the_destination_and_can_only_be_used_once(): void
    {
        Mail::fake();
        Cache::flush();
        $service = app(ContactOtp::class);
        $service->send('recovery', 42, 'email', 'student@example.com');
        $code = Mail::sent(OtpEmail::class)->first()->otp;

        $this->assertFalse($service->verify('recovery', 42, 'email', 'other@example.com', $code));
        $this->assertTrue($service->verify('recovery', 42, 'email', 'student@example.com', $code));
        $this->assertFalse($service->verify('recovery', 42, 'email', 'student@example.com', $code));
    }

    public function test_code_expires_and_wrong_attempts_are_limited(): void
    {
        Mail::fake();
        Cache::flush();
        $service = app(ContactOtp::class);
        $service->send('registration', 43, 'email', 'student@example.com');
        $code = Mail::sent(OtpEmail::class)->first()->otp;
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($i = 0; $i < 5; $i++) $this->assertFalse($service->verify('registration', 43, 'email', 'student@example.com', $wrong));
        $this->assertFalse($service->verify('registration', 43, 'email', 'student@example.com', $code));
        $this->travel(11)->minutes();
        $this->assertFalse($service->verify('registration', 43, 'email', 'student@example.com', $code));
    }

    public function test_configured_attempt_limit_is_shared_across_channels_and_resends(): void
    {
        Mail::fake();
        Cache::flush();
        config(['auth.otp.max_attempts' => 2, 'auth.otp.lockout_seconds' => 60]);
        $service = app(ContactOtp::class);
        $service->send('recovery', 47, 'email', 'student@example.com');
        $code = Mail::sent(OtpEmail::class)->first()->otp;
        $wrong = $code === '000000' ? '000001' : '000000';
        $this->assertFalse($service->verify('recovery', 47, 'email', 'student@example.com', $wrong));
        $this->assertFalse($service->verify('recovery', 47, 'sms', '09171234567', $wrong));
        $service->send('recovery', 47, 'email', 'student@example.com');
        $this->assertFalse($service->verify('recovery', 47, 'email', 'student@example.com', $code));
        $this->travel(61)->seconds();
        $this->assertTrue($service->verify('recovery', 47, 'email', 'student@example.com', $code));
    }

    public function test_email_resend_reuses_the_code_without_extending_its_expiry(): void
    {
        Mail::fake();
        Cache::flush();
        $service = app(ContactOtp::class);
        $service->send('registration', 44, 'email', 'student@example.com');
        $code = Mail::sent(OtpEmail::class)->first()->otp;
        $this->travel(9)->minutes();
        $service->send('registration', 44, 'email', 'student@example.com');
        $this->assertSame($code, Mail::sent(OtpEmail::class)->last()->otp);
        $this->travel(2)->minutes();
        $this->assertFalse($service->verify('registration', 44, 'email', 'student@example.com', $code));
    }

    public function test_sms_resend_waits_two_minutes_and_keeps_the_original_code(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $service = app(ContactOtp::class);
        $service->send('recovery', 45, 'sms', '09171234567');
        $first = Http::recorded()->first()[0]['message'];
        try {
            $service->send('registration', 45, 'sms', '+639171234567');
            $this->fail('A second SMS should be blocked during the cooldown.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('120 seconds', $e->getMessage());
        }
        Http::assertSentCount(1);
        $this->travel(2)->minutes();
        $service->send('recovery', 45, 'sms', '09171234567');
        $this->assertSame($first, Http::recorded()->last()[0]['message']);
    }

    public function test_failed_resend_keeps_the_current_code_valid(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::sequence()->push(['status' => 200])->push([], 500)]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $service = app(ContactOtp::class);
        $service->send('recovery', 46, 'sms', '09171234567');
        preg_match('/\b\d{6}\b/', Http::recorded()->first()[0]['message'], $match);
        $this->travel(2)->minutes();
        try {
            $service->send('recovery', 46, 'sms', '09171234567');
            $this->fail('Gateway failure should be reported.');
        } catch (\RuntimeException $e) {
            $this->assertTrue($service->verify('recovery', 46, 'sms', '09171234567', $match[0]));
        }
    }
}
