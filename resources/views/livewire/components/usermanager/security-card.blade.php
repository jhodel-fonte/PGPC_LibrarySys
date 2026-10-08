<section class="space-y-4 sm:space-y-5 rounded-2xl bg-white p-4 sm:p-6 shadow-sm shadow-slate-200/60 ring-1 ring-slate-200 xl:col-span-7">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="text-[#102B70]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Security & Authentication</h3>
        </div>
        <button
            type="button"
            wire:click="$parent.sendPasswordReset()"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#102B70] hover:text-[#0B225E] transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
            <span>Reset Password</span>
        </button>
    </div>

    <div class="divide-y divide-slate-100 text-sm font-medium">
        <!-- Password Row -->
        <div class="grid gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                Password
            </span>
            <div class="flex items-center justify-between gap-3 sm:justify-end">
                <span class="font-mono text-slate-500 tracking-widest text-sm">••••••••</span>
                <button
                    type="button"
                    wire:click="$parent.sendPasswordReset()"
                    class="min-h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-[#102B70] transition hover:border-[#102B70] hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Email Address Row -->
        <div class="grid gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                Email Address
            </span>
            <div class="flex flex-wrap items-center justify-between gap-3 sm:justify-end">
                <span class="break-all text-xs sm:text-sm font-semibold text-slate-800">
                    {{ $user?->email ?: 'No email address' }}
                </span>
                <button
                    type="button"
                    wire:click="$parent.openEditModal()"
                    class="min-h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-[#102B70] transition hover:border-[#102B70] hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
                >
                    {{ !empty($user?->email) ? 'Edit' : 'Add Email' }}
                </button>
            </div>
        </div>

    </div>
</section>
