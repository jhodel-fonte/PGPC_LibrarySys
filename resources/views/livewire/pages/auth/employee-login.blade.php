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
        } catch (\Throwable $e) {
            $this->dispatch('login-failed');
            throw $e;
        }
    }
}; ?>

<!-- Elevated White Card Container -->
<div
    x-data="{ isLoggingIn: false }"
    @login-failed.window="isLoggingIn = false"
    x-on:livewire:error.window="isLoggingIn = false"
    class="w-full rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-9 md:p-10 shadow-xl shadow-slate-200/70 select-none"
>
    <!-- Title Area (Heading 32px/Bold, Subtitle 15px, 8px gap, 28-32px bottom spacing) -->
    <div class="mb-[30px]">
        <h2 class="text-[20px] font-bold tracking-tight text-slate-900 leading-tight">Employee Portal</h2>
        <p class="mt-2 text-[15px] font-normal text-slate-500 leading-normal">Sign in to access the library management workspace.</p>
    </div>

    <!-- Auth Response / Error Card (Alpine.js controlled) -->
    <x-auth.responseCard id="ajax-general-error" />

    <form wire:submit="login" @submit="isLoggingIn = true" novalidate>
        <div class="space-y-5">
            <!-- Username or Employee ID (Label 14px/Semibold, 8px gap, Input 52px height, 12px radius, Text 15px, Icon 20px) -->
            <div>
                <label for="login" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Username or Employee ID <span class="text-red-500">*</span>
                </label>
                <div class="group relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0" />
                    </svg>
                    <input
                        wire:model="form.email"
                        id="login"
                        name="login"
                        type="text"
                        required
                        autocomplete="username"
                        placeholder="Enter username or employee ID"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-11 pr-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                </div>
                @error('form.email')
                    <p data-error-for="login" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password with Alpine Show/Hide (Label 14px/Semibold, 8px gap, Input 52px height, 12px radius, Text 15px) -->
            <div x-data="{ showPassword: false }">
                <div class="mb-2 flex items-center justify-between">
                    <label for="password" class="inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        Password <span class="text-red-500">*</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[12px] font-semibold text-[#102b70] transition hover:text-blue-800" wire:navigate>Forgot password?</a>
                    @endif
                </div>
                <div class="group relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-11 pr-12 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none focus:ring-2 focus:ring-blue-200"
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
        </div>

        <!-- Keep me signed in (16px top spacing, Checkbox 16px, Text 14px) -->
        <div class="mt-4">
            <label class="inline-flex cursor-pointer items-center gap-2.5 text-[14px] text-slate-600 select-none">
                <input wire:model="form.remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#102b70] focus:ring-[#102b70]">
                Keep me signed in on this device
            </label>
        </div>

        <!-- Primary Submit Button (Stays loading even during redirect) -->
        <div class="mt-6">
            <button
                type="submit"
                :disabled="isLoggingIn"
                class="group relative flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#102b70] px-5 text-[15px] font-semibold text-white shadow-md shadow-blue-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-75 disabled:cursor-not-allowed"
            >
                <!-- Normal Button Content -->
                <span x-show="!isLoggingIn" class="inline-flex items-center gap-2">
                    Sign in to your account
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>

                <!-- Animated CSS Spinner Content -->
                <span x-show="isLoggingIn" style="display: none;" class="inline-flex items-center justify-center gap-2.5">
                    <span class="inline-block h-4 w-4 animate-spin animate-pgpc-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span>Signing in...</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Social Sign In (28-32px top spacing, Divider "or", Button height 52px, Radius 12px, Text 15px/Semibold) -->
    @if (Route::has('auth.google'))
        <div class="relative my-7">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-[13px] font-normal">
                <span class="bg-white px-3 text-slate-400">or</span>
            </div>
        </div>

        <div>
            <a href="{{ route('auth.google') }}" class="flex h-[50px] w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white text-[15px] font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span>Continue with Google</span>
            </a>
        </div>
    @endif

    <!-- Bottom Links & Policy Notice -->
    <div class="mt-2 pt-2 text-center space-y-3">

        <p class="text-[12px] text-slate-400 pt-1">
            By signing in, you agree to our
            <a href="#" class="font-semibold text-[#102b70] underline decoration-[#fcc719] decoration-2 underline-offset-2 hover:text-blue-800 transition">
                Terms of Service
            </a>
            and
            <a href="#" class="font-semibold text-[#102b70] underline decoration-[#fcc719] decoration-2 underline-offset-2 hover:text-blue-800 transition">
                Privacy Policy
            </a>.
        </p>
    </div>
</div>
