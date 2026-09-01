<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
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
        // Enforce strict staff roles: Admin, Head Librarian, Librarian
        $this->form->authenticate(['Admin', 'Head Librarian', 'Librarian']);

        Session::regenerate();

        $intended = session()->pull('url.intended');
        if ($intended && ! str_contains($intended, '/login')) {
            $this->redirect($intended, navigate: false);
            return;
        }

        $this->redirect(route('admin.dashboard'), navigate: false);
    }
}; ?>

<div>
    <!-- Title Area -->
    <div class="mb-8">
        <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-[#102b70]">Authorized personnel access</p>
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Staff Portal</h2>
        <p class="mt-3 leading-7 text-slate-500">Sign in to access the library management workspace.</p>
    </div>

    <!-- Auth Response / Error Card (Alpine.js controlled) -->
    <x-auth.responseCard id="ajax-general-error" />

    <form wire:submit="login" class="space-y-5" novalidate>
        <!-- Username, Employee ID or Email -->
        <div>
            <label for="login" class="mb-2 block text-sm font-bold text-slate-700">Username or Employee ID <span class="text-red-500">*</span></label>
            <div class="group relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0" />
                </svg>
                <input
                    wire:model="form.email"
                    id="login"
                    name="login"
                    type="text"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter username or employee ID"
                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-base sm:text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:ring-4 focus:ring-blue-100"
                >
            </div>
            @error('form.email')
                <p data-error-for="login" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password with Alpine Show/Hide -->
        <div x-data="{ showPassword: false }">
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-sm font-bold text-slate-700">Password <span class="text-red-500">*</span></label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-bold text-[#102b70] transition hover:text-blue-800" wire:navigate>Forgot password?</a>
                @endif
            </div>
            <div class="group relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v3h8z" />
                </svg>
                <input
                    wire:model="form.password"
                    id="password"
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-14 text-base sm:text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:ring-4 focus:ring-blue-100"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    class="absolute right-2 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none focus:ring-2 focus:ring-blue-200"
                >
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z" />
                    </svg>
                    <svg x-show="showPassword" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 4.2A10.5 10.5 0 0112 4c4.48 0 8.27 2.94 9.54 8a11.8 11.8 0 01-2.1 3.8M6.2 6.2A11.7 11.7 0 002.46 12C3.73 17.06 7.52 20 12 20a10.5 10.5 0 004.1-.8" />
                    </svg>
                </button>
            </div>
            @error('form.password')
                <p data-error-for="password" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keep me signed in -->
        <label class="inline-flex cursor-pointer items-center gap-3 text-sm text-slate-600">
            <input wire:model="form.remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#102b70] focus:ring-[#102b70]">
            Keep me signed in on this device
        </label>

        <!-- Submit Button -->
        <button type="submit" wire:loading.attr="disabled" class="group flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#102b70] px-5 font-bold text-white shadow-lg shadow-blue-900/15 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                Sign in to workspace
                <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </span>
            <span wire:loading.flex wire:target="login" class="items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            </span>
        </button>
    </form>

    <div class="mt-8 border-t border-slate-200 pt-6 text-center">
        <p class="text-xs text-slate-400">
            Are you a student?
            <a href="{{ route('login') }}" class="ml-1 font-semibold text-slate-500 hover:text-[#102b70]" wire:navigate>Student login</a>
        </p>
    </div>
</div>
