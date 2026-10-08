<section class="space-y-4 sm:space-y-5 rounded-2xl bg-white p-4 sm:p-6 shadow-sm shadow-slate-200/60 ring-1 ring-slate-200 xl:col-span-5">
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2.5">
            <div class="text-[#102B70]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Account Status</h3>
        </div>
        <button
            type="button"
            wire:click="$parent.openStatusModal()"
            class="rounded-lg px-2.5 py-1 text-xs font-bold text-[#102B70] transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
        >
            Change
        </button>
    </div>

    <!-- Prominent Status Banner -->
    @if($statusLower === 'suspended' || $statusLower === 'locked')
        <div class="p-3.5 sm:p-4 rounded-xl bg-red-50/70 border border-red-200 flex items-center gap-3 sm:gap-3.5">
            <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-red-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <div class="min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-red-700">Suspended</h4>
                <p class="text-[11px] sm:text-xs text-red-600/90 font-medium">This account is currently suspended.</p>
            </div>
        </div>
    @elseif($statusLower === 'active')
        <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-center gap-3 sm:gap-3.5">
            <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div class="min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-emerald-800">Active</h4>
                <p class="text-[11px] sm:text-xs text-emerald-700/90 font-medium">This account is active and in good standing.</p>
            </div>
        </div>
    @else
        <div class="p-3.5 sm:p-4 rounded-xl bg-amber-50/70 border border-amber-200 flex items-center gap-3 sm:gap-3.5">
            <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div class="min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-amber-800">{{ $statusName }}</h4>
                <p class="text-[11px] sm:text-xs text-amber-700/90 font-medium">Account status is currently {{ strtolower($statusName) }}.</p>
            </div>
        </div>
    @endif

    <!-- Meta details list -->
    <div class="divide-y divide-slate-100 pt-1 text-sm font-medium">
        <div class="grid gap-1 py-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:py-2.5">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Assigned Role
            </span>
            <span class="text-sm font-bold text-slate-800">{{ $roleName === 'Member' ? 'Student' : $roleName }}</span>
        </div>

        <div class="grid gap-1 py-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:py-2.5">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Account Created
            </span>
            <span class="text-sm font-semibold text-slate-800">{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</span>
        </div>

        <div class="grid gap-1 py-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:py-2.5">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Last Login
            </span>
            <span class="text-sm font-semibold text-slate-800">
                {{ $user->last_login ? $user->last_login->format('M d, Y h:i A') : 'Never logged in' }}
            </span>
        </div>

        <div class="grid gap-1 py-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:gap-4 sm:py-2.5">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Failed Attempts
            </span>
            <span class="text-sm font-bold text-slate-800">{{ $user->failed_attempts ?? 0 }}</span>
        </div>
    </div>
</section>
