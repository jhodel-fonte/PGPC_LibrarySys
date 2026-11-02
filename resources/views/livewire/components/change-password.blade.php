<div>
    @if($open)
        <dialog open x-init="$el.showModal && ($el.removeAttribute('open'), $el.showModal())" @cancel.prevent="$wire.close()" aria-labelledby="change-password-title" class="m-auto w-[min(90vw,28rem)] rounded-2xl border-0 bg-white p-6 shadow-xl backdrop:bg-slate-900/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="change-password-title" class="text-xl font-bold text-[#102B70]">Change your password</h2>
                    <p class="mt-2 text-sm text-slate-600">Choose a secure password for your library account.</p>
                </div>
                <button type="button" wire:click="close" aria-label="Close" class="rounded-lg px-2 py-1 text-2xl text-slate-600 focus-visible:ring-2">&times;</button>
            </div>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div><label for="change-current-password" class="block text-sm font-semibold">Current password</label><input id="change-current-password" type="password" wire:model="current_password" autocomplete="current-password" required class="mt-1 w-full rounded-lg border-slate-300">@error('current_password')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="change-new-password" class="block text-sm font-semibold">New password</label><input id="change-new-password" type="password" wire:model="password" autocomplete="new-password" required class="mt-1 w-full rounded-lg border-slate-300"><p class="mt-1 text-xs text-slate-600">At least 8 characters, including uppercase and lowercase letters and a number.</p>@error('password')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="change-confirm-password" class="block text-sm font-semibold">Confirm new password</label><input id="change-confirm-password" type="password" wire:model="password_confirmation" autocomplete="new-password" required class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div class="flex flex-wrap justify-end gap-3"><button type="button" wire:click="close" class="min-h-11 rounded-lg border border-slate-300 px-4 font-semibold">Later</button><button type="submit" wire:loading.attr="disabled" class="min-h-11 rounded-lg bg-[#102B70] px-4 font-semibold text-white disabled:opacity-50">Change password</button></div>
            </form>
            <form method="POST" action="{{ route('password.change.recovery') }}" class="mt-4" onsubmit="return confirm('You will be signed out to reset your password. Continue?')">
                @csrf
                <button type="submit" class="text-sm font-semibold text-[#102B70] underline">Forgot password?</button>
            </form>
        </dialog>
    @endif
</div>
