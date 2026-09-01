<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('The password must contain at least one capital letter.');
                    }
                    if (! preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least one number.');
                    }
                },
                'same:password_confirmation',
            ],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password_hash' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: false);
    }
}; ?>

<div x-init="document.getElementById('portal-content')?.classList.replace('opacity-0', 'opacity-100')" class="w-full max-w-[480px] mx-auto">

    <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-9 shadow-xl shadow-slate-200/70 select-none">
    <!-- Header -->
    <div class="mb-6 text-center sm:text-left">
        <div class="mx-auto sm:mx-0 grid h-12 w-12 place-items-center rounded-xl bg-[#EFF6FF] text-[#102B70] shadow-xs mb-4">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-[24px] sm:text-[26px] font-bold tracking-tight text-[#102B70] leading-tight">
            Set new password
        </h2>
        <p class="mt-2 text-[14px] text-slate-500 leading-normal">
            Choose a strong password to protect your account.
        </p>
    </div>

    <form wire:submit="resetPassword" novalidate class="space-y-5">
        <!-- Email (Read-only or prefilled) -->
        <div>
            <label for="email" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                Email address
            </label>
            <input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                required
                readonly
                class="h-[52px] w-full rounded-xl border border-slate-200 bg-slate-100/70 px-4 text-[15px] text-slate-600 outline-none cursor-not-allowed select-text"
            >
            @error('email')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div x-data="{ showPassword: false }">
            <label for="password" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                New password <span class="text-red-500">*</span>
                <span class="text-xs font-normal text-slate-400 ml-1">(min. 8 chars, 1 capital, 1 number)</span>
            </label>
            <div class="group relative">
                <input
                    wire:model="password"
                    id="password"
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="At least 8 characters"
                    class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-12 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text @error('password') border-red-500 bg-red-50/20 focus:border-red-500 focus:ring-red-100 @enderror"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none"
                >
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z" />
                    </svg>
                    <svg x-show="showPassword" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 4.2A10.5 10.5 0 0112 4c4.48 0 8.27 2.94 9.54 8a11.8 11.8 0 01-2.1 3.8M6.2 6.2A11.7 11.7 0 002.46 12C3.73 17.06 7.52 20 12 20a10.5 10.5 0 004.1-.8" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div x-data="{ showConfirmPassword: false }">
            <label for="password_confirmation" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                Confirm new password <span class="text-red-500">*</span>
            </label>
            <div class="group relative">
                <input
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    name="password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repeat new password"
                    class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-12 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text @error('password_confirmation') border-red-500 bg-red-50/20 focus:border-red-500 focus:ring-red-100 @enderror"
                >
                <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    :aria-label="showConfirmPassword ? 'Hide password confirmation' : 'Show password confirmation'"
                    class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none"
                >
                    <svg x-show="!showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z" />
                    </svg>
                    <svg x-show="showConfirmPassword" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 4.2A10.5 10.5 0 0112 4c4.48 0 8.27 2.94 9.54 8a11.8 11.8 0 01-2.1 3.8M6.2 6.2A11.7 11.7 0 002.46 12C3.73 17.06 7.52 20 12 20a10.5 10.5 0 004.1-.8" />
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="resetPassword"
                class="group flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#102b70] px-5 text-[15px] font-semibold text-white shadow-md shadow-blue-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-75 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="resetPassword" class="inline-flex items-center gap-2">
                    Reset password
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span wire:loading.flex wire:target="resetPassword" class="items-center justify-center gap-2.5">
                    <span class="inline-block h-4 w-4 animate-spin animate-pgpc-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span>Updating password...</span>
                </span>
            </button>
        </div>
    </form>
    </div>
</div>
