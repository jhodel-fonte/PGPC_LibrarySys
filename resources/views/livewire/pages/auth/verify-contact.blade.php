<div class="w-full max-w-lg mx-auto rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-lg">
    <h1 class="text-2xl font-bold text-[#102B70]">Verify your contact details</h1>
    <p class="mt-2 text-sm text-slate-600">{{ $user->student->contact_verification_required ? 'Verify your email and mobile number before submitting your student documents.' : 'Verify your registered mobile number to use it for password recovery.' }} Codes expire 10 minutes after the first request. Wait 2 minutes between SMS requests.</p>
    @if(session('contact_delivery_failed'))<p role="alert" class="mt-4 text-sm text-red-700">A code could not be sent. Please use the send button to try again.</p>@endif
    @if(session('error'))<p role="alert" class="mt-4 text-sm text-red-700">{{ session('error') }}</p>@endif
    @if(session('status'))<p role="status" class="mt-4 text-sm text-emerald-700">{{ session('status') }}</p>@endif
    @if($notice)<p role="status" class="mt-4 text-sm font-medium text-emerald-700">{{ $notice }}</p>@endif
    @error('code')<p role="alert" class="mt-4 text-sm text-red-700">{{ $message }}</p>@enderror
    @foreach(['email' => ['Email address', $user->email, $user->is_email_verified, 'emailCode'], 'sms' => ['Mobile number', $user->student->contact_num, $user->student->phone_verified_at, 'smsCode']] as $channel => [$label, $destination, $verified, $field])
        @if($channel === 'email' && ! $user->student->contact_verification_required) @continue @endif
        <section class="mt-6 border-t border-slate-200 pt-5">
            <h2 class="font-bold text-slate-900">{{ $label }}</h2>
            <p class="mt-1 text-sm text-slate-600 break-all">{{ $destination }}</p>
            @if($verified)
                <p class="mt-2 text-sm font-semibold text-emerald-700">Verified</p>
            @else
                <button type="button" wire:click="sendCode('{{ $channel }}')" wire:loading.attr="disabled" class="mt-3 min-h-11 rounded-lg border border-[#102B70] px-4 text-sm font-semibold text-[#102B70] hover:bg-blue-50 disabled:opacity-50">Send code</button>
                @if($channel === 'email')
                    <a href="{{ route('student.contact.google') }}" class="mt-3 ml-2 inline-flex min-h-11 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Verify with Google</a>
                @endif
                <form wire:submit="verifyCode('{{ $channel }}')" class="mt-3 flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-36"><label for="{{ $field }}" class="block text-sm font-medium text-slate-700">6-digit code</label><input id="{{ $field }}" wire:model="{{ $field }}" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="mt-1 w-full rounded-lg border-slate-300" required>@error($field)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <button type="submit" class="min-h-11 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white hover:bg-[#0B225E]">Verify</button>
                </form>
            @endif
        </section>
    @endforeach
</div>
