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
use App\Services\TurnstileService;
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

    #[Validate('required|string', message: 'Please complete the Cloudflare security verification.')]
    public string $turnstileToken = '';

    public function rules(): array
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean',
            'turnstileToken' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your username, email or student ID.',
            'password.required' => 'Please enter your password.',
            'turnstileToken.required' => 'Please complete the Cloudflare security verification.',
        ];
    }

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

        if (! TurnstileService::verify($this->turnstileToken, 'login', request()->ip())) {
            throw ValidationException::withMessages([
                'form.turnstileToken' => 'Security verification failed. Please check the box again.',
            ]);
        }

        $loginInput = trim($this->email);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        // 1. Locate the user account by email, username, or school ID scoped to allowed roles
        $accountQuery = Account::with('role', 'status');
        if (! empty($allowedRoles)) {
            $accountQuery->whereHas('role', function ($q) use ($allowedRoles) {
                $q->whereIn('name', $allowedRoles);
            });
        }

        $account = null;

        if ($isEmail) {
            $account = (clone $accountQuery)->where('email', $loginInput)->first();
        } else {
            // Try username
            $account = (clone $accountQuery)->where('username', $loginInput)->first();

            // Try librarian school ID (only if staff roles are allowed)
            if (! $account && (empty($allowedRoles) || array_intersect(['Admin', 'Head Librarian', 'Librarian'], $allowedRoles))) {
                $librarian = Librarian::where('school_id_number', $loginInput)->first();
                if ($librarian && $librarian->account_id) {
                    $account = (clone $accountQuery)->where('id', $librarian->account_id)->first();
                }
            }

            // Try student school ID (only if student role is allowed)
            if (! $account && (empty($allowedRoles) || in_array('Student', $allowedRoles))) {
                $student = Student::where('school_id_number', $loginInput)->first();
                if ($student && $student->account_id) {
                    $account = (clone $accountQuery)->where('id', $student->account_id)->first();
                }
            }
        }

        // 2. Validate existence and password
        if (! $account || ! Hash::check($this->password, $account->getAuthPassword())) {
            RateLimiter::hit($this->throttleKey());

            if ($account) {
                $account->increment('failed_attempts');
            }

            throw ValidationException::withMessages([
                'form.email' => 'Invalid username or password.',
            ]);
        }

        // 3. Fallback role access safeguard
        if (! empty($allowedRoles)) {
            $userRole = $account->role?->name;
            if (! in_array($userRole, $allowedRoles)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'form.email' => 'Invalid username or password. Please try again.',
                ]);
            }
        }

        // 4. Verify account status
        if ($account->status && strtolower($account->status->status_name) !== 'active') {
            $statusName = strtolower($account->status->status_name);
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => "Your account is currently {$statusName}. Please contact the library administrator.",
            ]);
        }

        // 5. Update login stats on success
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
