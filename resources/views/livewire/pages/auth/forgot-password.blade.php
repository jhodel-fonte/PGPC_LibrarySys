<div
    x-init="
        document.getElementById('portal-content')?.classList.replace('opacity-0', 'opacity-100');
        if (countdown > 0) startTimer();
    "
    x-data="{
        countdown: {{ $initialCountdown }},
        timer: null,
        startTimer(duration) {
            if (duration !== undefined) this.countdown = duration;
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                if (this.countdown > 0) {
                    this.countdown--;
                } else {
                    clearInterval(this.timer);
                }
            }, 1000);
        }
    }"
    @reset-link-sent.window="startTimer($event.detail.countdown || 30)"
    class="w-full max-w-[480px] mx-auto"
>
    <!-- Elevated White Card -->
    <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-7 sm:p-9 shadow-xl shadow-slate-200/70 select-none">

        @if (! $linkSent)
            <!-- Initial State: Enter Email -->
            <div class="mb-6 text-center sm:text-left">
                <div class="mx-auto sm:mx-0 grid h-12 w-12 place-items-center rounded-xl bg-[#EFF6FF] text-[#102B70] shadow-xs mb-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h2 class="text-[24px] sm:text-[26px] font-bold tracking-tight text-[#102B70] leading-tight">
                    Forgot password?
                </h2>
                <p class="mt-2 text-[14px] text-slate-500 leading-normal">
                    Enter your registered email address and we'll send you a link to reset your password.
                </p>
            </div>

            <form wire:submit="sendPasswordResetLink" novalidate class="space-y-5">
                <!-- Email Input -->
                <div>
                    <label for="email" class="mb-2 inline-block cursor-pointer text-[14px] font-semibold text-slate-700">
                        Email address <span class="text-red-500">*</span>
                    </label>
                    <div class="group relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 transition group-focus-within:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                        <input
                            wire:model="email"
                            id="email"
                            name="email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="e.g. yourname@example.com"
                            class="h-[52px] w-full rounded-xl border border-slate-200/90 bg-[#F8FAFC] pl-11 pr-4 text-[15px] text-slate-900 outline-none transition placeholder:text-[15px] placeholder:text-slate-400 hover:border-slate-300 focus:border-[#102b70] focus:bg-white focus:ring-4 focus:ring-blue-100 select-text @error('email') border-red-500 bg-red-50/20 focus:border-red-500 focus:ring-red-100 @enderror"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button with Spinner Animation -->
                <div class="pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="sendPasswordResetLink"
                        class="group flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#102b70] px-5 text-[15px] font-semibold text-white shadow-md shadow-blue-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-75 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="sendPasswordResetLink" class="inline-flex items-center gap-2">
                            Send password reset link
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                        <span wire:loading.flex wire:target="sendPasswordResetLink" class="items-center justify-center gap-2.5">
                            <span class="inline-block h-4 w-4 animate-spin animate-pgpc-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            <span>Sending link...</span>
                        </span>
                    </button>
                </div>
            </form>

        @else
            <!-- Sent State: Email Sent + Limit Display & 30s Countdown Resend Button -->
            <div class="mb-5 text-center sm:text-left">
                <div class="mx-auto sm:mx-0 grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-emerald-600 shadow-xs mb-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-[24px] sm:text-[26px] font-bold tracking-tight text-[#102B70] leading-tight">
                    Check your email
                </h2>
                <p class="mt-2 text-[14px] text-slate-600 leading-normal">
                    We've emailed a password reset link to:
                </p>
                <div class="mt-2.5 inline-block px-3.5 py-1.5 text-[14px] font-semibold text-[#102B70] break-all">
                    {{ $sentEmail }}
                </div>
            </div>

            <!-- Status Alert -->
            @if (session('status'))
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 p-3.5 text-sm text-emerald-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-semibold text-xs text-emerald-900">Link sent successfully</p>
                        <p class="mt-0.5 text-xs text-emerald-700 leading-relaxed">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @error('email')
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-700">
                    {{ $message }}
                </div>
            @enderror

            <!-- Limit Reached Warning -->
            @if ($maxAttemptsReached)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50/90 p-4 text-xs text-amber-800 flex items-start gap-2.5">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="font-bold text-amber-900">Maximum email limit reached (3/3)</p>
                        <p class="mt-1 leading-relaxed text-amber-800">
                            You have sent the maximum of 3 emails for this reset link. If you cannot find the email, please check your spam or junk folder, or wait for the link to expire (60 minutes) before requesting a new one.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Resend Button Section -->
            <div class="space-y-3 pt-1">
                @if (! $maxAttemptsReached)
                    <button
                        type="button"
                        wire:click="resend"
                        wire:loading.attr="disabled"
                        wire:target="resend"
                        :disabled="countdown > 0"
                        class="group flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#102b70] px-5 text-[15px] font-semibold text-white shadow-md shadow-blue-900/10 transition hover:-translate-y-0.5 hover:bg-[#0b225e] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:bg-[#102b70]"
                    >
                        <span wire:loading.remove wire:target="resend" class="inline-flex items-center gap-2">
                            <span x-show="countdown > 0" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Resend link in <span x-text="countdown" class="font-bold"></span>s
                            </span>
                            <span x-show="countdown === 0" style="display: none;" class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Resend password reset link
                            </span>
                        </span>
                        <span wire:loading.flex wire:target="resend" class="items-center justify-center gap-2.5">
                            <span class="inline-block h-4 w-4 animate-spin animate-pgpc-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            <span>Resending link...</span>
                        </span>
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="flex h-[52px] w-full items-center justify-center rounded-xl bg-slate-200 px-5 text-[15px] font-semibold text-slate-500 cursor-not-allowed"
                    >
                        Resend limit reached (3/3)
                    </button>
                @endif

                <div class="text-center pt-1">
                    <button
                        type="button"
                        wire:click="useDifferentEmail"
                        class="text-xs font-semibold text-slate-500 hover:text-[#102B70] transition underline underline-offset-2"
                    >
                        Use a different email address
                    </button>
                </div>
            </div>
        @endif

        <!-- Back to Login Navigation -->
        <div class="mt-6 pt-5 border-t border-slate-200/80 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] font-semibold text-slate-600 hover:text-[#102B70] transition group" wire:navigate>
                <svg class="h-4 w-4 text-slate-400 transition-transform group-hover:-translate-x-1 group-hover:text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Sign in</span>
            </a>
        </div>
    </div>
</div>
