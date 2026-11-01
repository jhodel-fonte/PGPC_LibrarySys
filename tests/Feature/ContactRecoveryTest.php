<?php

namespace Tests\Feature;

use App\Livewire\Pages\Auth\ForgotPassword;
use App\Livewire\Pages\Auth\VerifyContact;
use App\Mail\OtpEmail;
use App\Mail\ResetEmail;
use App\Models\Account;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ContactRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function student(bool $verified = false): Account
    {
        $user = Account::factory()->unverified()->create();
        Student::create(['account_id' => $user->id, 'school_id_number' => 'TEST-123', 'first_name' => 'Test', 'last_name' => 'Student', 'library_status_id' => 1, 'contact_num' => '09171234567', 'contact_verification_required' => ! $verified, 'phone_verified_at' => $verified ? now() : null]);
        return $user;
    }

    public function test_registration_requires_both_codes(): void
    {
        Cache::flush();
        Mail::fake();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $user = $this->student();
        $component = Livewire::actingAs($user)->test(VerifyContact::class)->call('sendCode', 'email');
        $code = Mail::sent(OtpEmail::class)->first()->otp;
        $component->set('emailCode', $code)->call('verifyCode', 'email')->assertHasNoErrors();
        $this->assertTrue((bool) $user->fresh()->student->contact_verification_required);
        $component->call('sendCode', 'sms');
        $message = Http::recorded()->last()[0]['message'];
        preg_match('/\b\d{6}\b/', $message, $match);
        $component->set('smsCode', $match[0])->call('verifyCode', 'sms')->assertRedirect(route('verification.upload'));
        $this->assertFalse((bool) $user->fresh()->student->contact_verification_required);
    }

    public function test_sms_recovery_resets_password_without_verifying_email(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $user = $this->student(true);
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->set('channel', 'sms')->call('sendPasswordResetLink');
        $message = Http::recorded()->last()[0]['message'];
        preg_match('/\b\d{6}\b/', $message, $match);
        $component->set('code', $match[0])->call('verifyCode')->assertSet('verified', true)
            ->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')->call('resetWithCode')->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password_hash));
        $this->assertFalse($user->fresh()->is_email_verified);
    }

    public function test_email_recovery_sends_a_link_and_code_and_invalidates_the_link_after_reset(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        Mail::assertSent(ResetEmail::class, 1);
        Mail::assertNotSent(OtpEmail::class);
        $mail = Mail::sent(ResetEmail::class)->first();
        $code = $mail->otp;
        $token = basename(parse_url($mail->url, PHP_URL_PATH));
        $this->assertTrue(\Illuminate\Support\Facades\Password::tokenExists($user, $token));
        $component->set('code', $code)->call('verifyCode')->assertSet('verified', true)
            ->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')->call('resetWithCode')->assertRedirect(route('login'));
        $this->assertTrue($user->fresh()->is_email_verified);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertFalse(\Illuminate\Support\Facades\Password::tokenExists($user, $token));
    }

    public function test_email_resend_waits_for_broker_and_keeps_original_code_expiry(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        $first = Mail::sent(ResetEmail::class)->first();
        $firstToken = basename(parse_url($first->url, PHP_URL_PATH));
        $component->call('resend')->assertHasErrors(['email']);
        Mail::assertSent(ResetEmail::class, 1);
        $this->travel(61)->seconds();
        $component->call('resend')->assertHasNoErrors();
        Mail::assertSent(ResetEmail::class, 2);
        $second = Mail::sent(ResetEmail::class)->last();
        $this->assertSame($first->otp, $second->otp);
        $this->assertSame($first->otpExpiresAt, $second->otpExpiresAt);
        $this->assertFalse(\Illuminate\Support\Facades\Password::tokenExists($user, $firstToken));
        $this->assertTrue(\Illuminate\Support\Facades\Password::tokenExists($user, basename(parse_url($second->url, PHP_URL_PATH))));
    }

    public function test_failed_email_resend_keeps_the_previous_link_and_code(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        $first = Mail::sent(ResetEmail::class)->first();
        $token = basename(parse_url($first->url, PHP_URL_PATH));
        $this->travel(61)->seconds();
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Delivery failed'));
        $component->call('resend');
        $this->assertTrue(\Illuminate\Support\Facades\Password::tokenExists($user, $token));
        $component->set('code', $first->otp)->call('verifyCode')->assertSet('verified', true);
    }

    public function test_reset_by_email_link_invalidates_verified_otp_authorization(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        $mail = Mail::sent(ResetEmail::class)->first();
        $component->set('code', $mail->otp)->call('verifyCode')->assertSet('verified', true);
        \Livewire\Volt\Volt::test('pages.auth.reset-password', ['token' => basename(parse_url($mail->url, PHP_URL_PATH))])
            ->set('email', $user->email)->set('password', 'LinkPassword1')->set('password_confirmation', 'LinkPassword1')
            ->call('resetPassword')->assertRedirect(route('login'));
        $component->set('password', 'OtherPassword1')->set('password_confirmation', 'OtherPassword1')
            ->call('resetWithCode')->assertHasErrors(['code']);
        $this->assertTrue(Hash::check('LinkPassword1', $user->fresh()->password_hash));
    }

    public function test_sms_recovery_does_not_send_to_an_unverified_phone(): void
    {
        Cache::flush();
        Http::fake();
        $user = $this->student();
        Livewire::test(ForgotPassword::class)->set('email', $user->email)->set('channel', 'sms')->call('sendPasswordResetLink');
        Http::assertNothingSent();
    }

    public function test_verified_phone_can_request_and_confirm_a_recovery_code(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $user = $this->student(true);
        $component = Livewire::test(ForgotPassword::class)->set('email', '+639171234567')->call('sendPasswordResetLink');
        preg_match('/\b\d{6}\b/', Http::recorded()->last()[0]['message'], $match);
        $component->set('code', $match[0])->call('verifyCode')->assertSet('verified', true)
            ->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')->call('resetWithCode')->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password_hash));
    }

    public function test_shared_verified_phone_does_not_select_an_account(): void
    {
        Cache::flush();
        Http::fake();
        $this->student(true);
        $other = Account::factory()->create();
        Student::create(['account_id' => $other->id, 'school_id_number' => 'TEST-456', 'first_name' => 'Other', 'last_name' => 'Student', 'library_status_id' => 1, 'contact_num' => '+639171234567', 'phone_verified_at' => now()]);
        Livewire::test(ForgotPassword::class)->set('email', '09171234567')->call('sendPasswordResetLink')->assertSet('sent', true);
        Http::assertNothingSent();
    }

    public function test_reset_requires_verified_code_and_expiring_authorization(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        $component->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1');
        $code = Mail::sent(ResetEmail::class)->first()->otp;
        $component->set('code', $code)->call('verifyCode')->assertSet('verified', true);
        $this->travel(11)->minutes();
        $component->call('resetWithCode')->assertHasErrors(['code'])->assertSet('verified', false);
        $this->assertFalse(Hash::check('NewPassword1', $user->fresh()->password_hash));
    }

    public function test_password_cannot_be_reset_before_code_verification(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink')
            ->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')->call('resetWithCode')->assertForbidden();
        $this->assertFalse(Hash::check('NewPassword1', $user->fresh()->password_hash));
    }

    public function test_password_change_invalidates_verified_recovery(): void
    {
        Cache::flush();
        Mail::fake();
        $user = $this->student();
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendPasswordResetLink');
        $code = Mail::sent(ResetEmail::class)->first()->otp;
        $component->set('code', $code)->call('verifyCode')->assertSet('verified', true);
        $user->forceFill(['password_hash' => Hash::make('OtherPassword1')])->save();
        $component->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')
            ->call('resetWithCode')->assertHasErrors(['code'])->assertSet('verified', false);
        $this->assertTrue(Hash::check('OtherPassword1', $user->fresh()->password_hash));
    }

    public function test_existing_student_can_verify_their_registered_phone(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $user = $this->student();
        $user->student->update(['contact_verification_required' => false]);
        $component = Livewire::actingAs($user)->test(VerifyContact::class)->call('sendCode', 'sms');
        preg_match('/\b\d{6}\b/', Http::recorded()->last()[0]['message'], $match);
        $component->set('smsCode', $match[0])->call('verifyCode', 'sms')->assertRedirect(route('student.profile'));
        $this->assertNotNull($user->fresh()->student->phone_verified_at);
    }

    public function test_recovery_resend_shows_sms_cooldown_and_resends_the_same_code(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['status' => 200])]);
        config(['settings.sms.api_token' => 'test', 'settings.sms.enabled' => true]);
        $user = $this->student(true);
        $component = Livewire::test(ForgotPassword::class)->set('email', $user->email)->set('channel', 'sms')->call('sendPasswordResetLink');
        $first = Http::recorded()->first()[0]['message'];
        $component->call('resend')->assertHasErrors(['email']);
        Http::assertSentCount(1);
        $this->travel(2)->minutes();
        $component->call('resend')->assertHasNoErrors();
        $this->assertSame($first, Http::recorded()->last()[0]['message']);
    }
}
