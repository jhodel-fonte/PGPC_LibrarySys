<?php

use App\Livewire\Forms\RegisterForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.register-auth')] class extends Component
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
                $base = \Illuminate\Support\Str::slug(explode('@', $google['email'])[0], '');
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
}; ?>

<!-- Elevated White Card Container (rounded-2xl matching Employee & Student portals) -->
<div
    x-data="{
        isRegistering: false,
        scrollToError() {
            setTimeout(() => {
                const firstError = document.querySelector('[data-error-field], [data-error-for]:not(.hidden), .text-red-600:not(:empty)');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const input = firstError.closest('div').querySelector('input, select') || firstError;
                    if (input && typeof input.focus === 'function') {
                        input.focus({ preventScroll: true });
                    }
                }
            }, 80);
        }
    }"
    @registration-failed.window="isRegistering = false"
    x-on:livewire:error.window="isRegistering = false"
    @scroll-to-error.window="scrollToError()"
    class="w-full rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-9 md:p-10 shadow-xl shadow-slate-200/70 select-none"
>
    @if ($errors->any())
        <div x-init="scrollToError()" class="hidden"></div>
    @endif

    <!-- Title Area (Heading 32px/Bold, Subtitle 15px/Regular, 8px gap, 28-32px bottom spacing) -->
    <div class="mb-[30px]">
        {{-- <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-[#102b70]">Student registration</p> --}}
        <h2 class="text-[32px] text-[#102B70] font-bold tracking-tight text-slate-900 leading-tight">Create your account</h2>
        <p class="mt-2 text-[15px] font-normal text-slate-500 leading-normal">Use your current student information to join the PGPC Library.</p>
    </div>

    <!-- Auth Response / Error Card -->
    <x-auth.responseCard id="ajax-general-error" />

    @if (session()->has('google_auth'))
        <div class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50/80 p-4 text-sm text-[#102b70]">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>Google account connected: <strong class="font-bold">{{ session('google_auth.email') }}</strong>. Please enter your academic details below to finish.</span>
            </div>
            <button type="button" wire:click="clearGoogleAuth" class="shrink-0 text-xs font-semibold text-slate-500 hover:text-red-600 transition underline">
                Disconnect
            </button>
        </div>
    @endif

    <form wire:submit="register" @submit="isRegistering = true" novalidate class="space-y-6">
        <!-- Section 1: Student Information -->
        <fieldset class="space-y-4">
            <legend class="mb-3 flex w-full items-center gap-2.5 pb-2 text-[14px] font-bold text-slate-900 border-b border-slate-100">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-[#EFF6FF] text-xs font-bold text-[#102b70]">1</span>
                Student Information
            </legend>

            <!-- Student ID Number -->
            <div>
                <label for="student_id_number" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Student ID number <span class="text-red-500">*</span>
                </label>
                <div class="group relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <input
                        wire:model="form.student_id_number"
                        id="student_id_number"
                        name="student_id_number"
                        type="text"
                        required
                        placeholder="e.g. 04-12345"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-11 pr-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                </div>
                @error('form.student_id_number')
                    <p data-error-for="student_id_number" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- First Name & Last Name (2 cols) -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        First name <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="form.first_name"
                        id="first_name"
                        name="first_name"
                        type="text"
                        required
                        autocomplete="given-name"
                        placeholder="Juan"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    @error('form.first_name')
                        <p data-error-for="first_name" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        Last name <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="form.last_name"
                        id="last_name"
                        name="last_name"
                        type="text"
                        required
                        autocomplete="family-name"
                        placeholder="Dela Cruz"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    @error('form.last_name')
                        <p data-error-for="last_name" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Middle Name -->
            <div>
                <label for="middle_name" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Middle name <span class="text-xs font-normal text-slate-400">(optional)</span>
                </label>
                <input
                    wire:model="form.middle_name"
                    id="middle_name"
                    name="middle_name"
                    type="text"
                    autocomplete="additional-name"
                    placeholder="Santos"
                    class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                >
                @error('form.middle_name')
                    <p data-error-for="middle_name" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Program (Full Width) -->
            <div>
                <label for="program" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Program <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select
                        wire:model="form.program"
                        id="program"
                        name="program"
                        required
                        class="h-[52px] w-full appearance-none rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-10 text-[15px] text-slate-900 outline-none transition hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text cursor-pointer"
                    >
                        <option value="">Select program</option>
                        @foreach (config('pgpc.college.programs', []) as $code => $name)
                            <option value="{{ $code }}">{{ $code }} ({{ $name }})</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('form.program')
                    <p data-error-for="program" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Year Level (Full Width) -->
            <div>
                <label for="year_level" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Year level <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select
                        wire:model="form.year_level"
                        id="year_level"
                        name="year_level"
                        required
                        class="h-[52px] w-full appearance-none rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-10 text-[15px] text-slate-900 outline-none transition hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text cursor-pointer"
                    >
                        <option value="">Select year level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('form.year_level')
                    <p data-error-for="year_level" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>

        <!-- Section 2: Contact Details -->
        <fieldset class="space-y-4">
            <legend class="mb-3 flex w-full items-center gap-2.5 pb-2 text-[14px] font-bold text-slate-900 border-b border-slate-100">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-[#EFF6FF] text-xs font-bold text-[#102b70]">2</span>
                Contact Details
            </legend>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Email Address -->
                <div>
                    <label for="email" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        Email address <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="form.email"
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="student@pgpc.edu.ph"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    @error('form.email')
                        <p data-error-for="email" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Number -->
                <div>
                    <label for="contact_num" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        Contact number <span class="text-xs font-normal text-slate-400">(optional)</span>
                    </label>
                    <input
                        wire:model="form.contact_num"
                        id="contact_num"
                        name="contact_num"
                        type="tel"
                        autocomplete="tel"
                        placeholder="0912 345 6789"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    @error('form.contact_num')
                        <p data-error-for="contact_num" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </fieldset>

        <!-- Section 3: Account Security -->
        <fieldset class="space-y-4">
            <legend class="mb-3 flex w-full items-center gap-2.5 pb-2 text-[14px] font-bold text-slate-900 border-b border-slate-100">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-[#EFF6FF] text-xs font-bold text-[#102b70]">3</span>
                Account Security
            </legend>

            <!-- Username -->
            <div>
                <label for="username" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Username <span class="text-red-500">*</span>
                </label>
                <div class="group relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0" />
                    </svg>
                    <input
                        wire:model="form.username"
                        id="username"
                        name="username"
                        type="text"
                        required
                        autocomplete="username"
                        placeholder="Choose a username"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-11 pr-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                </div>
                @error('form.username')
                    <p data-error-for="username" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password (Full Width) -->
            <div x-data="{ showPassword: false }">
                <label for="password" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Password <span class="text-red-500">*</span>
                    <span class="text-xs font-normal text-slate-400 ml-1">(min. 8 characters, 1 capital, 1 number)</span>
                </label>
                <div class="group relative">
                    <input
                        wire:model="form.password"
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="At least 8 characters, 1 capital, 1 number"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-12 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none focus:ring-2 focus:ring-blue-200"
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
                @error('form.password')
                    <p data-error-for="password" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation (Full Width) -->
            <div x-data="{ showConfirmPassword: false }">
                <label for="password_confirmation" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                    Confirm password <span class="text-red-500">*</span>
                </label>
                <div class="group relative">
                    <input
                        wire:model="form.password_confirmation"
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Repeat your password"
                        class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-4 pr-12 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text"
                    >
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        :aria-label="showConfirmPassword ? 'Hide password confirmation' : 'Show password confirmation'"
                        class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-[#102b70] focus:outline-none focus:ring-2 focus:ring-blue-200"
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
                @error('form.password_confirmation')
                    <p data-error-for="password_confirmation" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>

        <!-- Terms and Conditions Checkbox -->
        <div>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl p-1 text-[14px] leading-6 text-slate-600 transition hover:border-slate-300">
                <input
                    wire:model="form.terms"
                    type="checkbox"
                    name="terms"
                    required
                    class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-[#102b70] focus:ring-[#102b70]"
                >
                <span>
                    I agree to the
                    <button type="button" onclick="document.getElementById('terms_modal').showModal()" class="font-bold text-[#102b70] underline decoration-[#fcc719] decoration-2 underline-offset-4 hover:text-blue-800 transition">Terms of Service</button>
                    and
                    <button type="button" onclick="document.getElementById('privacy_modal').showModal()" class="font-bold text-[#102b70] underline decoration-[#fcc719] decoration-2 underline-offset-4 hover:text-blue-800 transition">Privacy Policy</button>.
                </span>
            </label>
            @error('form.terms')
                <p data-error-for="terms" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Primary Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                :disabled="isRegistering"
                class="group flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#102b70] px-5 text-[15px] font-semibold text-white shadow-md shadow-blue-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-75 disabled:cursor-not-allowed"
            >
                <span x-show="!isRegistering" class="inline-flex items-center gap-2">
                    Create student account
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>
                <span x-show="isRegistering" style="display: none;" class="inline-flex items-center justify-center gap-2.5">
                    <span class="inline-block h-4 w-4 animate-spin animate-pgpc-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span>Creating account...</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Social Sign Up (Divider "or", Button height 52px, Radius 12px, Text 15px/Semibold) -->
    <div class="relative my-7">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center text-[13px] font-normal">
            <span class="bg-white px-3 text-slate-400">or</span>
        </div>
    </div>

    <div>
        <a href="{{ Route::has('auth.google') ? route('auth.google') : url('/auth/google') }}" class="flex h-[52px] w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white text-[15px] font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
            <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            <span>Sign up with Google</span>
        </a>
    </div>

    <!-- Bottom Link -->
    <div class="mt-3 text-center">
        <p class="text-[14px] text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="ml-1 font-semibold text-[#102b70] underline underline-offset-4 transition hover:text-blue-800" wire:navigate>
                Sign in
            </a>
        </p>
    </div>

    <!-- Terms Modal -->
    <dialog id="terms_modal" class="modal modal-bottom sm:modal-middle rounded-2xl p-0 backdrop:bg-slate-900/60">
        <div class="relative w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('terms_modal').close()" class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition">✕</button>
            </div>
            <x-auth.termsCard />
        </div>
    </dialog>

    <!-- Privacy Modal -->
    <dialog id="privacy_modal" class="modal modal-bottom sm:modal-middle rounded-2xl p-0 backdrop:bg-slate-900/60">
        <div class="relative w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('privacy_modal').close()" class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition">✕</button>
            </div>
            <x-auth.privacypolicyCard />
        </div>
    </dialog>
</div>
