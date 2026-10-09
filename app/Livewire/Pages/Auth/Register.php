<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\Forms\RegisterForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.register-auth')]
class Register extends Component
{
    public RegisterForm $form;

    /**
     * If student is already logged in, redirect away.
     */
    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect(url('/'), navigate: false);
            return;
        }

        if (session()->has('google_auth')) {
            $google = session('google_auth');
            if (! empty($google['email'])) {
                $this->form->email = $google['email'];
            }
            if (! empty($google['first_name'])) {
                $this->form->first_name = $google['first_name'];
            }
            if (! empty($google['last_name'])) {
                $this->form->last_name = $google['last_name'];
            }
            if (empty($this->form->username) && ! empty($google['email'])) {
                $base = Str::slug(explode('@', $google['email'])[0], '');
                $this->form->username = substr($base, 0, 30);
            }
        }
    }

    /**
     * Clear Google OAuth session data.
     */
    public function clearGoogleAuth(): void
    {
        session()->forget('google_auth');
        $this->redirect(route('register'), navigate: false);
    }

    /**
     * Handle incoming student registration request.
     */
    public function register(): void
    {
        try {
            $account = $this->form->store();

            Auth::login($account);

            session()->regenerate();

            $this->redirect(url('/'), navigate: false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('registration-failed');
            $this->dispatch('scroll-to-error');
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('registration-failed');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.register');
    }
}
