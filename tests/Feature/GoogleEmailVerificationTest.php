<?php

namespace Tests\Feature;

use App\Livewire\Pages\Auth\Register;
use App\Models\Account;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class GoogleEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function student(bool $phoneVerified = false): Account
    {
        $account = Account::factory()->unverified()->create(['status_id' => 4]);
        Student::create(['account_id' => $account->id, 'school_id_number' => 'GOOGLE-1', 'first_name' => 'Test', 'last_name' => 'Student', 'library_status_id' => 1, 'contact_num' => '09171234567', 'contact_verification_required' => true, 'phone_verified_at' => $phoneVerified ? now() : null]);
        return $account;
    }

    private function googleUser(string $email, bool $verified = true): void
    {
        $google = (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => 'google-id', 'email' => $email]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($google);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    public function test_matching_verified_google_email_completes_only_email_verification(): void
    {
        $account = $this->student();
        $this->googleUser($account->email);
        $this->actingAs($account)->withSession(['google_verify_account' => $account->id])->get(route('auth.google.callback'))
            ->assertRedirect(route('student.contact.verify'));
        $this->assertTrue($account->fresh()->is_email_verified);
        $this->assertTrue((bool) $account->fresh()->student->contact_verification_required);
    }

    public function test_google_verification_continues_to_documents_when_phone_is_verified(): void
    {
        $account = $this->student(true);
        $this->googleUser($account->email);
        $this->actingAs($account)->withSession(['google_verify_account' => $account->id])->get(route('auth.google.callback'))
            ->assertRedirect(route('verification.upload'));
        $this->assertFalse((bool) $account->fresh()->student->contact_verification_required);
    }

    public function test_mismatched_or_unverified_google_email_does_not_verify_account(): void
    {
        $account = $this->student();
        $this->googleUser('different@example.com');
        $this->actingAs($account)->withSession(['google_verify_account' => $account->id])->get(route('auth.google.callback'))
            ->assertRedirect(route('student.contact.verify'))->assertSessionHas('error');
        $this->assertFalse($account->fresh()->is_email_verified);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $account = $this->student();
        $this->googleUser($account->email, false);
        $this->actingAs($account)->withSession(['google_verify_account' => $account->id])->get(route('auth.google.callback'))
            ->assertRedirect(route('student.contact.verify'))->assertSessionHas('error');
        $this->assertFalse($account->fresh()->is_email_verified);
    }

    public function test_unverified_google_email_cannot_start_registration(): void
    {
        $this->googleUser('new@example.com', false);
        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionMissing('google_auth');
    }

    public function test_google_provider_id_cannot_verify_a_different_account_email(): void
    {
        $account = $this->student();
        $account->update(['provider' => 'google', 'provider_id' => 'google-id']);
        $this->googleUser('different@example.com');
        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertFalse($account->fresh()->is_email_verified);
    }

    public function test_google_verification_cannot_switch_the_signed_in_account(): void
    {
        $account = $this->student();
        $this->actingAs($account)->withSession(['google_verify_account' => $account->id + 1])->get(route('auth.google.callback'))
            ->assertRedirect(route('login'));
        $this->assertFalse($account->fresh()->is_email_verified);
    }

    public function test_registration_rejects_a_different_email_from_google(): void
    {
        session(['google_auth' => ['id' => 'google-id', 'email' => 'google@example.com', 'email_verified' => true]]);
        Livewire::test(Register::class)->set('form.email', 'different@example.com')->call('register')->assertHasErrors(['form.email']);
        $this->assertDatabaseMissing('accounts', ['email' => 'different@example.com']);
    }
}
