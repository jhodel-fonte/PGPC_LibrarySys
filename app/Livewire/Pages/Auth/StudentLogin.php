<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class StudentLogin extends Component
{
    public LoginForm $form;

    /**
     * If user is already logged in, redirect immediately.
     */
    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));

            if ($roleName === 'admin') {
                $this->redirect(route('admin.dashboard'), navigate: false);
            } elseif (in_array($roleName, ['librarian', 'headlibrarian'])) {
                $this->redirect(route('librarian.dashboard'), navigate: false);
            } else {
                $target = $user->isApprovedStudent()
                    ? route('student.dashboard')
                    : $user->getVerificationRedirectRoute();
                $this->redirect($target, navigate: false);
            }
        }
    }

    /**
     * Handle incoming student authentication request.
     */
    public function login(): void
    {
        try {
            // Enforce strict Student role filter in query
            $this->form->authenticate(['Student']);

            Session::regenerate();
            $intended = session()->get('url.intended');
            session()->forget('url.intended');

            $user = auth()->user();
            if ($user && ! $user->isApprovedStudent()) {
                $this->redirect($user->getVerificationRedirectRoute(), navigate: false);
                return;
            }

            if ($intended && filter_var($intended, FILTER_VALIDATE_URL)) {
                $this->redirect($intended, navigate: false);
                return;
            }

            $this->redirect(route('student.dashboard'), navigate: false);
        } catch (\App\Exceptions\AccountDeactivatedException $e) {
            $this->redirect(route('account.deactivated'), navigate: false);
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('login-failed');
            $this->dispatch('auth-error', [
                'title' => "We couldn't sign you in.",
                'message' => $e->validator->errors()->first() ?: 'Incorrect username or password.',
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
        return view('livewire.pages.auth.student-login');
    }
}

