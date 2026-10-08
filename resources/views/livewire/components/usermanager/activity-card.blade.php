<section class="space-y-4 sm:space-y-5 rounded-2xl bg-white p-4 sm:p-6 shadow-sm shadow-slate-200/60 ring-1 ring-slate-200 w-full">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="text-[#102B70]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Recent Activity</h3>
        </div>
        @if($activeTab !== 'activity')
            <button
                type="button"
                wire:click="$parent.setTab('activity')"
                class="flex min-h-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-600 transition hover:border-[#102B70]/30 hover:bg-blue-50 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
            >
                View All
            </button>
        @endif
    </div>

    <!-- Timeline List -->
    <div class="space-y-3.5 sm:space-y-4 text-xs">
        @if($statusLower === 'suspended' || $statusLower === 'locked')
            <!-- Event 1: Suspended -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-2.5 sm:gap-3 min-w-0">
                    <span class="h-2 w-2 rounded-full bg-red-500 mt-1.5 shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-bold text-slate-800">Account suspended</p>
                        <p class="text-[11px] sm:text-xs text-slate-500">Account has been suspended.</p>
                    </div>
                </div>
                <div class="text-right text-[11px] sm:text-xs text-slate-400 shrink-0 whitespace-nowrap">
                    <div>{{ $user->updated_at ? $user->updated_at->format('M d, Y') : 'Date unavailable' }}</div>
                    @if($user->updated_at)<div class="text-[10px] sm:text-[11px] text-slate-400">{{ $user->updated_at->format('h:i A') }}</div>@endif
                </div>
            </div>
        @endif

        <!-- Event 2: Created -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2.5 sm:gap-3 min-w-0">
                <span class="h-2 w-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Account created</p>
                    <p class="text-[11px] sm:text-xs text-slate-500">User account was created.</p>
                </div>
            </div>
            <div class="text-right text-[11px] sm:text-xs text-slate-400 shrink-0 whitespace-nowrap">
                <div>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Date unavailable' }}</div>
                @if($user->created_at)<div class="text-[10px] sm:text-[11px] text-slate-400">{{ $user->created_at->format('h:i A') }}</div>@endif
            </div>
        </div>

        <!-- Event 3: Verification status -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2.5 sm:gap-3 min-w-0">
                <span class="h-2 w-2 rounded-full {{ $user->is_email_verified ? 'bg-emerald-500' : 'bg-slate-400' }} mt-1.5 shrink-0"></span>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-slate-800">
                        {{ $user->is_email_verified ? 'Email verified' : 'Email verification pending' }}
                    </p>
                    <p class="text-[11px] sm:text-xs text-slate-500">
                        {{ $user->is_email_verified ? 'Email address confirmed.' : 'Email not yet verified.' }}
                    </p>
                </div>
            </div>
            <div class="text-right text-[11px] sm:text-xs text-slate-400 shrink-0 whitespace-nowrap">
                <div>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Date unavailable' }}</div>
                @if($user->created_at)<div class="text-[10px] sm:text-[11px] text-slate-400">{{ $user->created_at->format('h:i A') }}</div>@endif
            </div>
        </div>
    </div>
</section>
