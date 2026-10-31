<?php

namespace App\Livewire\Pages\Auth;

use App\Mail\StudentSetupEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class StudentSetup extends Component
{
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $notice = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isStudent(), 403);
        $this->email = auth()->user()->email ?? '';
    }

    public function sendVerification(): void
    {
        $user = auth()->user();
        abort_unless($user?->isStudent() && $user->setup_email_required, 403);
        $this->email = mb_strtolower(trim($this->email));
        $this->validate(['email' => 'required|email:rfc|max:255']);
        if (DB::table('accounts')->where('id', '!=', $user->id)->whereRaw('LOWER(email) = ?', [$this->email])->exists()) {
            $this->addError('email', 'This email address is already in use.');
            return;
        }
        $key = 'student-setup-email:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Please wait before requesting another verification email.');
            return;
        }
        RateLimiter::hit($key, 600);
        try {
            $user->forceFill(['email' => $this->email, 'is_email_verified' => false, 'email_verified_at' => null])->save();
            $url = \App\Support\EmailUrl::temporarySignedRoute('student.setup.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
            Mail::to($user->email)->send(new StudentSetupEmail($user, $url));
            $this->notice = 'Verification email sent. Open the link while signed in to continue.';
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('email', 'Could not send the verification email. Check the address and try again.');
        }
    }

    public function changePassword(): void
    {
        $user = auth()->user();
        abort_unless($user?->isStudent() && ! $user->setup_email_required && $user->setup_password_prompt, 403);
        $this->validate(['password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()]]);
        $user->forceFill(['password_hash' => Hash::make($this->password), 'password_changed_at' => now(), 'setup_password_prompt' => false])->save();
        session()->regenerate();
        $this->redirectRoute('student.dashboard', navigate: false);
    }

    public function skipPassword(): void
    {
        $user = auth()->user();
        abort_unless($user?->isStudent() && ! $user->setup_email_required && $user->setup_password_prompt, 403);
        $user->forceFill(['setup_password_prompt' => false])->save();
        $this->redirectRoute('student.dashboard', navigate: false);
    }

    public function render()
    {
        return view('livewire.pages.auth.student-setup');
    }
}
