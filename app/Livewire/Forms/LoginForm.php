<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\Librarian;
use App\Models\Student;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string', message: 'Please enter your username, email or student ID.')]
    public string $email = '';

    #[Validate('required|string', message: 'Please enter your password.')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials with role filtering.
     *
     * @param array<string> $allowedRoles
     * @throws ValidationException
     */
    public function authenticate(array $allowedRoles = []): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->email);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $roleFilter = function ($q) use ($allowedRoles) {
            if (! empty($allowedRoles)) {
                $q->whereIn('name', $allowedRoles);
            }
        };

        $account = null;

        if ($isEmail) {
            $account = Account::where('email', $loginInput)
                ->when(! empty($allowedRoles), function ($q) use ($roleFilter) {
                    $q->whereHas('role', $roleFilter);
                })
                ->first();
        } else {
            // 1. Try finding account by username with role filter
            $account = Account::where('username', $loginInput)
                ->when(! empty($allowedRoles), function ($q) use ($roleFilter) {
                    $q->whereHas('role', $roleFilter);
                })
                ->first();

            // 2. If staff/librarian roles are allowed, search librarian school_id_number
            if (! $account && (empty($allowedRoles) || array_intersect($allowedRoles, ['Admin', 'Head Librarian', 'Librarian']))) {
                $librarian = Librarian::where('school_id_number', $loginInput)->first();
                if ($librarian && $librarian->account) {
                    if (empty($allowedRoles) || in_array($librarian->account->role?->name, $allowedRoles)) {
                        $account = $librarian->account;
                    }
                }
            }

            // 3. If student role is allowed, search student school_id_number
            if (! $account && (empty($allowedRoles) || in_array('Student', $allowedRoles))) {
                $student = Student::where('school_id_number', $loginInput)->first();
                if ($student && $student->account) {
                    if (empty($allowedRoles) || in_array($student->account->role?->name, $allowedRoles)) {
                        $account = $student->account;
                    }
                }
            }
        }

        if (! $account || ! Hash::check($this->password, $account->getAuthPassword())) {
            RateLimiter::hit($this->throttleKey());

            if ($account) {
                $account->increment('failed_attempts');
            }

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        // Verify account status
        if ($account->status && strtolower($account->status->status_name) !== 'active') {
            $statusName = strtolower($account->status->status_name);
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => "Your account is currently {$statusName}. Please contact the library administrator.",
            ]);
        }

        // Update login stats on success
        $account->update([
            'last_login' => now(),
            'failed_attempts' => 0,
        ]);

        Auth::login($account, $this->remember);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim($this->email)).'|'.request()->ip());
    }
}
