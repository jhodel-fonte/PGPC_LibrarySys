<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app', ['title' => 'Login'])] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();
        $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));

        if (in_array($roleName, ['admin', 'headlibrarian', 'librarian'])) {
            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        } else {
            $this->redirectIntended(default: route('login'), navigate: true);
        }
    }
}; ?>

<div class="w-full max-w-[460px] bg-white p-8 sm:p-10 rounded-3xl border border-[#E2E8F0] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] relative z-20">
    <!-- Header/Title Area -->
    <div class="mb-8 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-4 group focus:outline-none" wire:navigate>
            <div class="h-16 w-16 rounded-2xl p-2 flex items-center justify-center shadow-sm group-hover:scale-105 group-hover:border-[#FCC719] transition-all">
                <img src="{{ asset('logo.webp') }}" alt="PGPC Logo" class="h-full w-full object-contain" onerror="this.src='{{ asset('images/logo.webp') }}'" />
            </div>
        </a>
        <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight font-sans">Welcome back</h2>
        <p class="text-sm text-[#64748B] mt-1.5 font-medium">Please enter your credentials to sign in.</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-2xl bg-[#EFF6FF] border border-[#102B70]/10 text-sm text-[#102B70] font-medium flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address or Username -->
        <div class="space-y-1.5">
            <label for="email" class="block font-semibold text-sm text-[#334155]">{{ __('Email or Username') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input wire:model="form.email" id="email" type="text" name="email" required autofocus autocomplete="username"
                    class="block w-full h-[56px] pl-11 pr-4 bg-[#F8FAFC] border border-[#E2E8F0] text-[#0F172A] rounded-[16px] focus:bg-white focus:border-[#102B70] focus:ring-[4px] focus:ring-[#EFF6FF] transition-all duration-200 outline-none hover:border-[#CBD5E1]"
                    placeholder="Enter your email or username" />
            </div>
            @error('form.email')
                <p class="text-[13px] font-medium text-[#EF4444] mt-1 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label for="password" class="block font-semibold text-sm text-[#334155]">{{ __('Password') }}</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full h-[56px] pl-11 pr-4 bg-[#F8FAFC] border border-[#E2E8F0] text-[#0F172A] rounded-[16px] focus:bg-white focus:border-[#102B70] focus:ring-[4px] focus:ring-[#EFF6FF] transition-all duration-200 outline-none hover:border-[#CBD5E1]"
                    placeholder="Enter your password" />
            </div>
            @error('form.password')
                <p class="text-[13px] font-medium text-[#EF4444] mt-1 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center justify-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" name="remember" class="peer appearance-none w-5 h-5 rounded-[6px] border-2 border-[#E2E8F0] bg-[#F8FAFC] checked:bg-[#102B70] checked:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] focus:ring-offset-1 transition-all cursor-pointer">
                    <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span class="ms-2.5 text-[13px] font-medium text-[#64748B] group-hover:text-[#334155] transition-colors">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-[13px] font-semibold text-[#102B70] hover:text-[#FCC719] transition-colors focus:outline-none focus:underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-3">
            <button type="submit" wire:loading.attr="disabled" class="w-full h-[56px] flex items-center justify-center gap-2 bg-[#102B70] text-white rounded-[16px] font-bold text-sm tracking-wide shadow-[0_4px_14px_rgba(16,43,112,0.25)] hover:bg-[#0B225E] hover:shadow-[0_6px_20px_rgba(16,43,112,0.35)] hover:-translate-y-0.5 focus:outline-none focus:ring-[4px] focus:ring-[#EFF6FF] active:scale-[0.98] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                    {{ __('Sign In') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </span>
                <span wire:loading.flex wire:target="login" class="items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Signing in...') }}
                </span>
            </button>
        </div>
    </form>

    <!-- Back to Homepage Link -->
    <div class="mt-6 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#64748B] hover:text-[#102B70] transition-colors" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Homepage
        </a>
    </div>
</div>
