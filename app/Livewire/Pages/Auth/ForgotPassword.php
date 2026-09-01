<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $linkSent = false;
    public string $sentEmail = '';
    public int $initialCountdown = 0;
    public int $sendCount = 0;
    public bool $maxAttemptsReached = false;

    public const MAX_SEND_LIMIT = 3;
    public const COOLDOWN_SECONDS = 30;

    /**
     * Mount component and restore active state if user refreshes the page.
     */
    public function mount(): void
    {
        $savedEmail = session('password_reset_email');
        $sentAt = session('password_reset_sent_at');

        if ($savedEmail && $sentAt) {
            $emailKey = Str::lower(trim($savedEmail));
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $savedEmail)->first();

            // If token still exists in database and was requested within the 60-minute expiry window
            if ($tokenRecord && (now()->timestamp - (int) $sentAt) < 3600) {
                $this->linkSent = true;
                $this->sentEmail = $savedEmail;
                $this->email = $savedEmail;

                // Calculate remaining cooldown from the 30-second window
                $elapsed = now()->timestamp - (int) $sentAt;
                $this->initialCountdown = max(0, self::COOLDOWN_SECONDS - $elapsed);

                $this->sendCount = (int) Cache::get('password_reset_send_count:' . $emailKey, 1);
                if ($this->sendCount >= self::MAX_SEND_LIMIT) {
                    $this->maxAttemptsReached = true;
                }
            } else {
                // Token has expired or was already consumed
                session()->forget(['password_reset_email', 'password_reset_sent_at']);
            }
        }
    }

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = Str::lower(trim($this->email));

        // 1. Check if user already reached the limit of 3 email sends for this link
        $currentSends = (int) Cache::get('password_reset_send_count:' . $email, 0);
        if ($currentSends >= self::MAX_SEND_LIMIT) {
            $this->linkSent = true;
            $this->sentEmail = $this->email;
            $this->sendCount = $currentSends;
            $this->maxAttemptsReached = true;
            $this->initialCountdown = 0;
            $this->addError('email', "You have reached the limit of 3 email sends for this reset link. Please check your inbox or wait 60 minutes for it to expire.");

            return;
        }

        // 2. Check 30-second cooldown between sends
        if (! $this->ensureIsNotRateLimited($email)) {
            return;
        }

        // 3. Dispatch the password reset link
        $status = Password::sendResetLink(
            ['email' => $this->email]
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        // 4. Increment and persist send count (valid for 60 minutes)
        $this->sendCount = $currentSends + 1;
        Cache::put('password_reset_send_count:' . $email, $this->sendCount, now()->addMinutes(60));

        // 5. Store in session so refreshing the page retains the active link state
        session([
            'password_reset_email' => $this->email,
            'password_reset_sent_at' => now()->timestamp,
        ]);

        $this->sentEmail = $this->email;
        $this->linkSent = true;
        $this->initialCountdown = self::COOLDOWN_SECONDS;

        if ($this->sendCount >= self::MAX_SEND_LIMIT) {
            $this->maxAttemptsReached = true;
        }

        $this->dispatch('reset-link-sent', countdown: self::COOLDOWN_SECONDS);

        session()->flash('status', __($status));
    }

    /**
     * Resend the password reset link.
     */
    public function resend(): void
    {
        $this->email = $this->sentEmail;
        $this->sendPasswordResetLink();
    }

    /**
     * Switch back to entering a different email.
     */
    public function useDifferentEmail(): void
    {
        $this->linkSent = false;
        $this->maxAttemptsReached = false;
        $this->initialCountdown = 0;
        session()->forget(['password_reset_email', 'password_reset_sent_at']);
        $this->reset('email', 'sentEmail');
    }

    /**
     * Rate limiting protection:
     * - Cooldown of 30 seconds between requests
     */
    protected function ensureIsNotRateLimited(string $email): bool
    {
        $ip = request()->ip();
        $cooldownKey = 'forgot-cooldown:' . $email . '|' . $ip;

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);
            $this->initialCountdown = $seconds;
            $this->dispatch('reset-link-sent', countdown: $seconds);
            $this->addError('email', "Please wait {$seconds} seconds before requesting another email.");

            return false;
        }

        RateLimiter::hit($cooldownKey, self::COOLDOWN_SECONDS);

        return true;
    }

    public function render()
    {
        return view('livewire.pages.auth.forgot-password');
    }
}
