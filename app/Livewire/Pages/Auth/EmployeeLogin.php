<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class EmployeeLogin extends Component
{
    public LoginForm $form;

    /**
     * If staff user is already logged in, redirect immediately to dashboard.
     */
    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));

            if (in_array($roleName, ['admin', 'headlibrarian', 'librarian'])) {
                $this->redirect(route('admin.dashboard'), navigate: false);
            } else {
                $this->redirect(url('/'), navigate: false);
            }
        }
    }

    /**
     * Handle incoming staff authentication request.
     */
    public function login(): void
    {
        try {
            // Enforce strict staff roles: Admin, Head Librarian, Librarian
            $this->form->authenticate(['Admin', 'Head Librarian', 'Librarian']);

            Session::regenerate();

            $intended = session()->pull('url.intended');
            if ($intended && ! str_contains($intended, '/portal') && ! str_contains($intended, '/login')) {
                $this->redirect($intended, navigate: false);
                return;
            }

            $this->redirect(route('admin.dashboard'), navigate: false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('login-failed');
            $this->dispatch('auth-error', [
                'title' => "We couldn't sign you in.",
                'message' => $e->validator->errors()->first() ?: 'The email or password you entered is incorrect.',
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('login-failed');
            $this->dispatch('auth-error', [
                'title' => "Sign In Failed",
                'message' => $e->getMessage() ?: 'An unexpected error occurred. Please try again.',
            ]);
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.employee-login');
    }
}

