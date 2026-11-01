<div x-data="{
        step: 'account', sending: false, checking: false, saving: false,
        digits: ['', '', '', '', '', ''],
        focusDigit(index) { this.$nextTick(() => this.$refs.otpGroup?.querySelectorAll('input')[index]?.focus()); },
        enterDigits(index, value) {
            const numbers = value.replace(/\D/g, '').slice(0, 6 - index);
            if (!numbers) { this.digits[index] = ''; return; }
            [...numbers].forEach((number, offset) => this.digits[index + offset] = number);
            this.focusDigit(Math.min(index + numbers.length, 5));
        },
        async send() {
            if (!this.$refs.accountForm.reportValidity()) return;
            this.step = 'code'; this.sending = true; this.digits = ['', '', '', '', '', ''];
            try {
                await $wire.sendPasswordResetLink();
                if (!$wire.sent) this.step = 'account';
                else this.focusDigit(0);
            } catch (_) { this.step = 'account'; this.$refs.networkError.hidden = false; }
            finally { this.sending = false; }
        },
        async resend() {
            this.sending = true;
            try { await $wire.resend(); } catch (_) { this.$refs.codeNetworkError.hidden = false; }
            finally { this.sending = false; }
        },
        async verify() {
            if (this.digits.join('').length !== 6) { this.$refs.digitError.hidden = false; return; }
            this.$refs.digitError.hidden = true; this.checking = true;
            try {
                await $wire.set('code', this.digits.join(''), false);
                await $wire.verifyCode();
                if ($wire.verified) { this.step = 'password'; this.$nextTick(() => this.$refs.password?.focus()); }
            } catch (_) { this.$refs.codeNetworkError.hidden = false; }
            finally { this.checking = false; }
        },
        async resetPassword() {
            this.saving = true;
            try { await $wire.resetWithCode(); if (!$wire.verified) this.step = 'code'; }
            catch (_) { this.$refs.passwordNetworkError.hidden = false; }
            finally { this.saving = false; }
        },
        async changeAccount() {
            this.step = 'account'; this.digits = ['', '', '', '', '', ''];
            await $wire.useDifferentEmail();
            this.$nextTick(() => this.$refs.identifier?.focus());
        }
    }" x-init="document.getElementById('portal-content')?.classList.replace('opacity-0', 'opacity-100')" class="mx-auto w-full max-w-[480px]">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl sm:p-9">
        <div x-show="step === 'account'">
            <h1 class="text-2xl font-bold text-[#102B70]">Forgot password?</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Enter your account email or verified mobile number to receive recovery instructions.</p>
            <form x-ref="accountForm" @submit.prevent="send()" class="mt-6 space-y-5">
                <div>
                    <label for="recovery-identifier" class="block text-sm font-semibold text-slate-700">Email address or mobile number</label>
                    <input id="recovery-identifier" x-ref="identifier" type="text" wire:model="email" autocomplete="username" required maxlength="255" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300">
                    @error('email')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Send instructions by</legend>
                    <div class="mt-2 flex flex-wrap gap-5 text-sm text-slate-700">
                        <label class="flex min-h-11 items-center gap-2"><input type="radio" wire:model="channel" value="email"> Email</label>
                        <label class="flex min-h-11 items-center gap-2"><input type="radio" wire:model="channel" value="sms"> SMS</label>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-600">A mobile number receives SMS and must already be verified. Staff can use the email reset link.</p>
                </fieldset>
                <p x-ref="networkError" hidden role="alert" class="text-sm text-red-700">Could not connect. Please try again.</p>
                <button type="submit" :disabled="sending" class="min-h-11 w-full rounded-lg bg-[#102B70] px-4 font-semibold text-white hover:bg-[#0B225E] disabled:opacity-50">Send recovery instructions</button>
            </form>
        </div>

        <div x-show="step === 'code'" x-cloak>
            <h1 class="text-2xl font-bold text-[#102B70]">Enter your code</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">If the account is eligible, check for recovery instructions. If you receive an email link, you can use it instead. If nothing arrives, check your details and try again after a short wait.</p>
            <p class="mt-2 text-xs leading-relaxed text-slate-600">Codes expire 10 minutes after the first request. After {{ max(1, (int) config('auth.otp.max_attempts', 5)) }} failed attempts, you may need to wait up to {{ max(1, (int) config('auth.otp.lockout_seconds', 600)) }} seconds before trying again. SMS requests are limited to one every 2 minutes.</p>
            <p x-show="sending" role="status" class="mt-4 text-sm font-semibold text-[#102B70]">Sending instructions…</p>
            <form @submit.prevent="verify()" class="mt-6 space-y-4">
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">6-digit verification code</legend>
                    <div x-ref="otpGroup" class="mt-2" style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.5rem;">
                        <template x-for="(digit, index) in digits" :key="index">
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" :autocomplete="index === 0 ? 'one-time-code' : 'off'" :aria-label="'Code digit ' + (index + 1) + ' of 6'" :value="digit"
                                @input="enterDigits(index, $event.target.value); $event.target.value = digits[index]" @paste.prevent="enterDigits(index, $event.clipboardData.getData('text'))"
                                @keydown.backspace="if (!digits[index] && index > 0) { digits[index - 1] = ''; focusDigit(index - 1); }"
                                class="h-12 min-w-0 w-full rounded-lg border-slate-300 p-0 text-center text-lg font-bold text-slate-900 focus:border-[#102B70] focus:ring-[#102B70]">
                        </template>
                    </div>
                </fieldset>
                <p x-ref="digitError" hidden role="alert" class="text-sm text-red-700">Enter all six digits.</p>
                @error('code')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
                @error('email')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
                <p x-ref="codeNetworkError" hidden role="alert" class="text-sm text-red-700">Could not connect. Please try again.</p>
                <button type="submit" :disabled="sending || checking" class="min-h-11 w-full rounded-lg bg-[#102B70] px-4 font-semibold text-white hover:bg-[#0B225E] disabled:opacity-50"><span x-text="checking ? 'Checking…' : 'Confirm code'"></span></button>
            </form>
            <div class="mt-4 flex flex-wrap gap-x-5">
                <button type="button" @click="resend()" :disabled="sending || checking" class="min-h-11 text-sm font-semibold text-[#102B70] underline underline-offset-2 disabled:opacity-50">Resend code</button>
                <button type="button" @click="changeAccount()" :disabled="sending || checking" class="min-h-11 text-sm font-semibold text-[#102B70] underline underline-offset-2 disabled:opacity-50">Use a different account</button>
            </div>
        </div>

        <div x-show="step === 'password'" x-cloak>
            <h1 class="text-2xl font-bold text-[#102B70]">Set a new password</h1>
            <p class="mt-2 text-sm text-slate-600">Code confirmed. Choose a new password for your account.</p>
            <form @submit.prevent="resetPassword()" class="mt-6 space-y-4">
                <div><label for="recovery-password" class="block text-sm font-semibold text-slate-700">New password</label><input id="recovery-password" x-ref="password" type="password" wire:model="password" autocomplete="new-password" required class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300"><p class="mt-1 text-xs text-slate-600">At least 8 characters, including an uppercase letter and a number.</p>@error('password')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="recovery-password-confirmation" class="block text-sm font-semibold text-slate-700">Confirm new password</label><input id="recovery-password-confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300"></div>
                <p x-ref="passwordNetworkError" hidden role="alert" class="text-sm text-red-700">Could not connect. Please try again.</p>
                <button type="submit" :disabled="saving" class="min-h-11 w-full rounded-lg bg-[#102B70] px-4 font-semibold text-white hover:bg-[#0B225E] disabled:opacity-50"><span x-text="saving ? 'Resetting…' : 'Reset password'"></span></button>
            </form>
        </div>
        <div class="mt-6 border-t border-slate-200 pt-5"><a href="{{ route('login') }}" wire:navigate class="text-sm font-semibold text-[#102B70] underline underline-offset-2">Back to sign in</a></div>
    </div>
</div>
