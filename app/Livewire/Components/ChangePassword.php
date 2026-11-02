<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\On;
use Livewire\Component;

class ChangePassword extends Component
{
    public bool $open = false;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && ! $user->setup_email_required && $user->password_change_reminder
            && (string) session('password_reminder_account') === (string) $user->id) {
            session()->forget('password_reminder_account');
            $this->open = true;
        }
    }

    #[On('open-change-password')]
    public function show(): void
    {
        abort_unless(auth()->check(), 403);
        $this->resetValidation();
        $this->open = true;
    }

    public function close(): void
    {
        $this->reset('open', 'current_password', 'password', 'password_confirmation');
        $this->resetValidation();
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $key = 'change-password:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['current_password' => 'Too many attempts. Please try again later.']);
        }
        RateLimiter::hit($key, 60);
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);
        $user->forceFill(['password_hash' => Hash::make($this->password), 'password_changed_at' => now(),
            'password_change_reminder' => false, 'setup_password_prompt' => false])->save();
        RateLimiter::clear($key);
        session()->forget('password_reminder_account');
        session()->regenerate();
        $this->close();
        $this->dispatch('toast', message: 'Password changed successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.components.change-password');
    }
}
