<div class="mx-auto w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <h1 class="text-2xl font-bold text-[#102B70]">Complete your library account</h1>
    @if(auth()->user()->setup_email_required)
        <p class="mt-2 text-sm text-slate-600">Add and verify your email address to continue to the student portal.</p>
        <form wire:submit="sendVerification" class="mt-5 space-y-4">
            <label for="setup-email" class="block text-sm font-semibold text-slate-700">Email address</label>
            <input id="setup-email" type="email" wire:model="email" required class="w-full rounded-lg border-slate-300" autocomplete="email">
            @error('email')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
            @if($notice)<p role="status" class="text-sm text-green-700">{{ $notice }}</p>@endif
            <button type="submit" wire:loading.attr="disabled" class="min-h-11 rounded-lg bg-[#102B70] px-5 text-sm font-semibold text-white disabled:opacity-50">Send verification email</button>
        </form>
    @elseif(auth()->user()->setup_password_prompt)
        <p class="mt-2 text-sm text-slate-600">Choose a new password to protect your account. You can skip this step.</p>
        <form wire:submit="changePassword" class="mt-5 space-y-4">
            <label for="setup-password" class="block text-sm font-semibold">New password</label>
            <input id="setup-password" type="password" wire:model="password" class="w-full rounded-lg border-slate-300" autocomplete="new-password">
            @error('password')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
            <label for="setup-password-confirm" class="block text-sm font-semibold">Confirm password</label>
            <input id="setup-password-confirm" type="password" wire:model="password_confirmation" class="w-full rounded-lg border-slate-300" autocomplete="new-password">
            <div class="flex flex-wrap gap-3"><button type="submit" class="min-h-11 rounded-lg bg-[#102B70] px-5 text-sm font-semibold text-white">Change password</button><button type="button" wire:click="skipPassword" class="min-h-11 rounded-lg border border-slate-300 px-5 text-sm font-semibold text-slate-700">Skip for now</button></div>
        </form>
    @else
        <a href="{{ route('student.dashboard') }}" class="mt-4 inline-block text-[#102B70] underline">Continue to dashboard</a>
    @endif
</div>
