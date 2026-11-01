<?php

namespace App\Livewire\Pages\Auth;

use App\Models\Account;
use App\Models\Student;
use App\Mail\ResetEmail;
use App\Services\ContactOtp;
use App\Services\SmsService;
use App\Support\EmailUrl;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ForgotPassword extends Component
{
    public string $email = '';
    public string $channel = 'email';
    public string $code = '';
    public string $password = '';
    public string $password_confirmation = '';
    #[Locked] public bool $sent = false;
    #[Locked] public string $sentEmail = '';
    #[Locked] public string $sentChannel = '';
    #[Locked] public bool $verified = false;
    #[Locked] public string $authorization = '';

    private function accountFor(string $identifier): ?Account
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return Account::where('email', Str::lower($identifier))->first();
        }
        $phone = app(SmsService::class)->formatPhoneNumber($identifier);
        if (! $phone) return null;
        $matched = null;
        foreach (Student::whereNotNull('phone_verified_at')->whereNotNull('contact_num')->cursor() as $student) {
            if (app(SmsService::class)->formatPhoneNumber($student->contact_num) !== $phone) continue;
            if ($matched !== null) return null;
            $matched = $student;
        }
        return $matched?->account;
    }

    public function sendPasswordResetLink(): void
    {
        $this->validate(['email' => ['required', 'string', 'max:255'], 'channel' => ['required', 'in:email,sms']]);
        $identifier = trim($this->email);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $phone = $isEmail ? '' : app(SmsService::class)->formatPhoneNumber($identifier);
        if (! $isEmail && ! $phone) {
            $this->addError('email', 'Enter a valid email address or Philippine mobile number.');
            return;
        }
        $identifier = $isEmail ? Str::lower($identifier) : $phone;
        $channel = $isEmail ? $this->channel : 'sms';
        $key = 'recovery-request:'.hash('sha256', $identifier).'|'.request()->ip();
        $ipKey = 'recovery-ip:'.request()->ip();
        $smsCooldownKey = 'recovery-sms-cooldown:'.hash('sha256', $identifier);
        $emailCooldownKey = 'recovery-email-cooldown:'.hash('sha256', $identifier);
        if ($channel === 'sms' && RateLimiter::tooManyAttempts($smsCooldownKey, 1)) {
            $this->addError('email', 'Please wait '.RateLimiter::availableIn($smsCooldownKey).' seconds before requesting another SMS.');
            return;
        }
        if ($channel === 'email' && RateLimiter::tooManyAttempts($emailCooldownKey, 1)) {
            $this->addError('email', 'Please wait '.RateLimiter::availableIn($emailCooldownKey).' seconds before requesting another email.');
            return;
        }
        if (RateLimiter::tooManyAttempts($key, 3) || RateLimiter::tooManyAttempts($ipKey, 10)) {
            $this->addError('email', 'Too many requests. Please wait before trying again.');
            return;
        }
        RateLimiter::hit($key, 600);
        RateLimiter::hit($ipKey, 600);
        if ($channel === 'sms') RateLimiter::hit($smsCooldownKey, 120);
        if ($channel === 'email') RateLimiter::hit($emailCooldownKey, max(1, (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.throttle', 60)));
        $user = $this->accountFor($identifier);
        if ($user) app(ContactOtp::class)->revokeRecoveryAuthorization($user->id, hash('sha256', session()->getId()));
        $this->reset('verified', 'authorization', 'code', 'password', 'password_confirmation');
        try {
            if ($user && $channel === 'email' && $isEmail) {
                Cache::lock('recovery-reset:'.$user->id, 60)->block(5, function () use ($user) {
                    $status = DB::transaction(function () use ($user) {
                        if (! $user->isStudent()) return Password::sendResetLink(['email' => $user->email]);
                        return Password::sendResetLink(['email' => $user->email], function ($account, $token) {
                            $url = EmailUrl::route('password.reset', ['token' => $token, 'email' => $account->email]);
                            app(ContactOtp::class)->send('recovery', $account->id, 'email', $account->email, function ($code, $expiresAt) use ($account, $url) {
                                \Illuminate\Support\Facades\Mail::to($account->email)->sendNow(new ResetEmail($url, $account, $code, $expiresAt));
                            });
                        });
                    });
                    if ($user->isStudent() && $status === Password::RESET_LINK_SENT) event(new PasswordResetLinkSent($user));
                });
            } elseif ($user?->isStudent() && $channel === 'sms' && $user->student?->phone_verified_at && $user->student->contact_num) {
                Cache::lock('recovery-reset:'.$user->id, 60)->block(5, fn () => app(ContactOtp::class)->send('recovery', $user->id, 'sms', $user->student->contact_num));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Password recovery delivery failed.');
        }
        $this->sentEmail = $identifier;
        $this->sentChannel = $channel;
        $this->sent = true;
    }

    public function resend(): void
    {
        abort_unless($this->sent, 403);
        $this->email = $this->sentEmail;
        $this->channel = $this->sentChannel;
        $this->resetErrorBag();
        $this->sendPasswordResetLink();
    }

    public function verifyCode(): void
    {
        abort_unless($this->sent && in_array($this->sentChannel, ['email', 'sms'], true), 403);
        $this->validate(['code' => ['required', 'regex:/^\d{6}$/']]);
        $user = $this->accountFor($this->sentEmail);
        $valid = $user?->isStudent() && Cache::lock('recovery-reset:'.$user->id, 60)->block(5, function () use ($user) {
            $user = Account::find($user->id);
            $destination = $this->sentChannel === 'email' ? $user?->email : $user?->student?->contact_num;
            if (! $user?->isStudent() || ! $destination || ($this->sentChannel === 'sms' && ! $user->student?->phone_verified_at)
                || ! app(ContactOtp::class)->verify('recovery', $user->id, $this->sentChannel, $destination, $this->code)) return false;
            $this->authorization = Str::random(64);
            Cache::put('recovery-authorization:'.$user->id, [
                'token' => hash('sha256', $this->authorization),
                'session' => hash('sha256', session()->getId()),
                'password' => hash_hmac('sha256', (string) $user->password_hash, config('app.key')),
                'destination' => hash_hmac('sha256', $destination, config('app.key')),
                'channel' => $this->sentChannel,
            ], now()->addMinutes(10));
            return true;
        });
        if (! $valid) {
            $this->addError('code', 'Invalid or expired code, or too many attempts. Please wait and try again.');
            return;
        }
        $this->code = '';
        $this->verified = true;
    }

    public function resetWithCode(): void
    {
        abort_unless($this->sent && $this->verified && $this->authorization !== '', 403);
        $this->validate(['password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[0-9]/']]);
        $user = $this->accountFor($this->sentEmail);
        if (! $user?->isStudent()) {
            $this->addError('code', 'Your recovery session has expired. Request a new code.');
            return;
        }
        $reset = Cache::lock('recovery-reset:'.$user->id, 60)->block(5, function () use ($user) {
            $reset = DB::transaction(function () use ($user) {
                $account = Account::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $auth = Cache::get('recovery-authorization:'.$account->id);
                $destination = $this->sentChannel === 'email' ? $account->email : $account->student?->contact_num;
                if (! is_array($auth) || ! $destination || ($this->sentChannel === 'sms' && ! $account->student?->phone_verified_at)
                    || $auth['channel'] !== $this->sentChannel
                    || ! hash_equals($auth['token'], hash('sha256', $this->authorization))
                    || ! hash_equals($auth['session'], hash('sha256', session()->getId()))
                    || ! hash_equals($auth['password'], hash_hmac('sha256', (string) $account->password_hash, config('app.key')))
                    || ! hash_equals($auth['destination'], hash_hmac('sha256', $destination, config('app.key')))) return false;
                $values = ['password_hash' => Hash::make($this->password), 'remember_token' => Str::random(60), 'password_changed_at' => now()];
                if ($this->sentChannel === 'email') $values += ['is_email_verified' => true, 'email_verified_at' => $account->email_verified_at ?? now()];
                $account->forceFill($values)->save();
                Password::broker()->deleteToken($account);
                event(new PasswordReset($account));
                return true;
            });
            if ($reset) app(ContactOtp::class)->clearRecovery($user->id);
            return $reset;
        });
        if (! $reset) {
            $this->reset('verified', 'authorization');
            $this->addError('code', 'Your recovery session has expired. Request a new code.');
            return;
        }
        $this->reset('authorization', 'password', 'password_confirmation');
        session()->flash('status', 'Your password has been reset.');
        $this->redirectRoute('login', navigate: false);
    }

    public function useDifferentEmail(): void
    {
        if ($this->sent && ($user = $this->accountFor($this->sentEmail))) app(ContactOtp::class)->revokeRecoveryAuthorization($user->id, hash('sha256', session()->getId()));
        $this->reset('sent', 'sentEmail', 'sentChannel', 'verified', 'authorization', 'code', 'password', 'password_confirmation');
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.pages.auth.forgot-password');
    }
}
