@php
    $user = $user ?? null;
    $student = $student ?? $user?->student;
    $statusName = $user?->status?->status_name ?? 'Active';
    $statusLower = strtolower($statusName);
    $libStatusName = $student?->libraryStatus?->status ?? 'Active';
    $libStatusLower = strtolower($libStatusName);
@endphp

<div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-8 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-9 text-base text-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
        <div>
            <p class="font-semibold text-[#102B70]">Reservation access: {{ $user?->reservations_blocked ? 'Blocked' : 'Allowed' }}</p>
            <p class="text-sm text-slate-600">Blocking reservations does not prevent sign-in or returning borrowed books.</p>
        </div>
        @if(\App\Services\EmployeeRoleService::canManageStudents(auth()->user()))
            <button type="button" wire:click="setReservationBlock({{ $user?->reservations_blocked ? 'false' : 'true' }})" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-[#102B70] hover:bg-slate-50">
                {{ $user?->reservations_blocked ? 'Restore reservation access' : 'Block reservations' }}
            </button>
        @endif
    </div>
    
    <!-- SECTION 1: ACCOUNT CREDENTIALS -->
    <div class="space-y-4">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
            <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
            <h3 class="text-lg font-bold text-[#102B70]">Account</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
            
            <!-- System Username -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">System Username</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <span class="font-bold text-sm w-4 text-center">@</span>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $user?->username ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Email Address -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Email</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $user?->email ?? 'Not provided' }}
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Account Status</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center justify-between">
                        <span>{{ $statusName }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusLower === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $statusLower === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $statusName }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Library Privilege Status -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Library Privilege</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center justify-between">
                        <span>{{ $libStatusName }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $libStatusLower === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $libStatusLower === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $libStatusName }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Last System Login -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Last System Login</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $user?->last_login ? $user->last_login->format('M d, Y h:i A') : 'Never logged in' }}
                    </div>
                </div>
            </div>

            <!-- Account Registered -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Account Registered</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $user?->created_at ? $user->created_at->format('M d, Y') : 'Date unavailable' }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SECTION 2: DANGER ZONE -->
    @php
        $canDeleteStudent = \App\Services\EmployeeRoleService::canDeleteStudent();
        $isTrashed = $user?->trashed();
    @endphp
    @if(! ($this->isProfile ?? false) && $canDeleteStudent)
    <div class="space-y-4 pt-4 border-t border-slate-100">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
            <div class="h-6 w-1 bg-[#EF4444] rounded-full"></div>
            <h3 class="text-lg font-bold text-[#B91C1C]">Danger Zone</h3>
        </div>

        <div class="space-y-3.5">
            <!-- Action 1: Disable / Enable Account (Soft Delete) -->
            <div class="rounded-2xl border {{ $isTrashed ? 'border-emerald-200 bg-emerald-50/50' : 'border-amber-200 bg-amber-50/40' }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h4 class="text-base font-bold text-[#0F172A]">
                        {{ $isTrashed ? 'Enable Student Account' : 'Disable Student Account' }}
                    </h4>
                    <p class="text-sm text-[#64748B] max-w-xl leading-relaxed">
                        @if($isTrashed)
                            This account is currently soft-deleted (disabled). Re-enable this account to restore system access and reactivate library borrow privileges.
                        @else
                            Temporarily suspend this student's login access and library borrow privileges by soft-deleting the account. All profile data and borrowing history are preserved.
                        @endif
                    </p>
                </div>

                @if($isTrashed)
                    <button
                        type="button"
                        wire:click="enableAccount"
                        class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Enable Account</span>
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="openDisableModal"
                        class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Disable Account</span>
                    </button>
                @endif
            </div>

            <!-- Action 2: Delete Student Record -->
            <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2]/60 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h4 class="text-base font-bold text-[#0F172A]">Soft Delete Student Record</h4>
                    <p class="text-sm text-[#64748B] max-w-xl leading-relaxed">
                        Delete this student account and revoke library access and privileges. This action cannot be undone.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="openDeleteModal"
                    class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-[#EF4444] hover:bg-[#DC2626] text-white text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-[#EF4444]/40"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Delete Account</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
