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

    public function rules(): array
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your username, email or student ID.',
            'password.required' => 'Please enter your password.',
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

        $loginInput = trim($this->email);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        // 1. Locate the user account by email, username, or school ID
        $account = null;

        if ($isEmail) {
            $account = Account::with('role', 'status')->where('email', $loginInput)->first();
        } else {
            // Try username
            $account = Account::with('role', 'status')->where('username', $loginInput)->first();

            // Try librarian school ID
            if (! $account) {
                $librarian = Librarian::where('school_id_number', $loginInput)->first();
                if ($librarian && $librarian->account) {
                    $account = $librarian->account()->with('role', 'status')->first();
                }
            }

            // Try student school ID
            if (! $account) {
                $student = Student::where('school_id_number', $loginInput)->first();
                if ($student && $student->account) {
                    $account = $student->account()->with('role', 'status')->first();
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
                'form.email' => 'These credentials do not match our records. Please verify your username/email and password.',
            ]);
        }

        // 3. Verify role access permissions
        if (! empty($allowedRoles)) {
            $userRole = $account->role?->name;
            if (! in_array($userRole, $allowedRoles)) {
                RateLimiter::hit($this->throttleKey());

                if (in_array('Student', $allowedRoles)) {
                    throw ValidationException::withMessages([
                        'form.email' => 'This account has staff privileges. Please use the Employee Portal to log in.',
                    ]);
                } else {
                    throw ValidationException::withMessages([
                        'form.email' => 'This account does not have staff access. Please use the Student Portal to log in.',
                    ]);
                }
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
