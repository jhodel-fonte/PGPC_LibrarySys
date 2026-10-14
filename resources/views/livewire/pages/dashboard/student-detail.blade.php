<div class="space-y-6 font-sans antialiased text-[#0F172A]" x-data="{ activeTab: @entangle('activeTab') }">

    @php
        $user = $user ?? null;
        $student = $student ?? $user?->student;
        $fullName = $fullName ?? 'N/A';
        $initials = $initials ?? 'S';
        $statusName = $user?->status?->status_name ?? 'Active';
        $statusLower = strtolower($statusName);
        $schoolId = $student?->school_id_number ?? 'Not assigned';
        $contactNum = $student?->contact_num ?? 'Not provided';
        $activeLoansCount = $activeLoansCount ?? 0;
        $overdueLoansCount = $overdueLoansCount ?? 0;
        $unpaidFinesTotal = $unpaidFinesTotal ?? 0;
        $activeLoans = $activeLoans ?? collect();
        $recentActivity = $recentActivity ?? collect();
        $circulationHistory = $circulationHistory ?? collect();
        $formattedDob = $formattedDob ?? 'Not specified';
        $statuses = $statuses ?? collect();
    @endphp

    <!-- ======================================================== -->
    <!-- 1. BREADCRUMBS & TOP HEADER (TITLE & EDIT BUTTON)       -->
    <!-- ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            

            <!-- Page Title & Subtitle -->
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Student Information Details</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                View the student's profile, enrollment, and library activity.
            </p>
        </div>

        <!-- Top Right Actions: Edit Student Information & Change Status -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <button
                type="button"
                wire:click="openStatusModal"
                class="h-11 px-4 sm:px-5 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-xs sm:text-sm font-bold transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#102B70]"
            >
                <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Update Status</span>
            </button>

            <a
                href="{{ route('admin.user-management.edit', $user?->encrypted_id ?? \App\Models\Account::encodeId($this->userId)) }}"
                wire:navigate
                class="h-11 px-5 sm:px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
            >
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span>Edit Student Information</span>
            </a>
        </div>
    </div>

    
    <!-- TOP PROFILE HERO BANNER                              -->
    
    @include('livewire.components.usermanager.profile-banner')

    <!-- ======================================================== -->
    <!-- 3. NAVIGATION TABS BAR                                  -->
    <!-- ======================================================== -->
    <div class="border-b border-[#E2E8F0]">
        <nav class="flex items-center gap-1 sm:gap-4 overflow-x-auto" aria-label="Student details tabs">
            
            <!-- Tab 1: Overview -->
            <button
                type="button"
                @click="activeTab = 'overview'"
                class="group inline-flex items-center gap-2 py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'overview' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-4 h-4 transition-colors" :class="activeTab === 'overview' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Overview</span>
            </button>

            <!-- Tab 2: Personal Information -->
            <button
                type="button"
                @click="activeTab = 'personal'"
                class="group inline-flex items-center gap-2 py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'personal' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-4 h-4 transition-colors" :class="activeTab === 'personal' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span>Personal Information</span>
            </button>

            <!-- Tab 3: Account -->
            <button
                type="button"
                @click="activeTab = 'account'"
                class="group inline-flex items-center gap-2 py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'account' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-4 h-4 transition-colors" :class="activeTab === 'account' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Account</span>
            </button>

            <!-- Tab 4: Circulation History -->
            <button
                type="button"
                @click="activeTab = 'history'"
                class="group inline-flex items-center gap-2 py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'history' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-4 h-4 transition-colors" :class="activeTab === 'history' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Circulation History</span>
            </button>

        </nav>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: OVERVIEW (CIRCULATION SUMMARY & CURRENT LOANS)    -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'overview'" x-cloak>
        @include('livewire.components.usermanager.student-overview')
    </div>


    <!-- ======================================================== -->
    <!-- TAB 2: PERSONAL INFORMATION                             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'personal'" x-cloak>
        @include('livewire.components.usermanager.student-p-i-card')
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: ACCOUNT                                          -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'account'" x-cloak>
        @include('livewire.components.usermanager.student-account')
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: CIRCULATION HISTORY                              -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'history'" x-cloak>
        @include('livewire.components.usermanager.student-circulation-history')
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: STATUS CHANGE                                   -->
    <!-- ======================================================== -->
    @if($showStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-md space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-[#102B70]">Change Student Status</h3>
                    </div>
                    <button type="button" wire:click="closeStatusModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#334155]">Select New Status <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            @foreach($this->statuses as $statusOption)
                                @php
                                    $stName = strtolower($statusOption->status_name);
                                    $isSelected = (int)$selectedStatusId === (int)$statusOption->id;
                                @endphp
                                <label class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer {{ $isSelected ? 'border-[#102B70] bg-blue-50/60 ring-2 ring-[#102B70]/10' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input
                                            type="radio"
                                            name="statusOptionStudent"
                                            value="{{ $statusOption->id }}"
                                            wire:model.live="selectedStatusId"
                                            class="h-4 w-4 text-[#102B70] focus:ring-[#102B70] border-slate-300 shrink-0"
                                        >
                                        <span class="text-xs font-bold text-slate-800">{{ $statusOption->status_name }}</span>
                                    </div>
                                    <span class="h-2.5 w-2.5 rounded-full shrink-0 {{ $stName === 'active' ? 'bg-emerald-500' : (($stName === 'suspended' || $stName === 'locked') ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeStatusModal"
                            class="h-10 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="updateStatus"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-xs font-bold text-white shadow-sm transition active:scale-[0.98] cursor-pointer"
                        >
                            Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- MODAL 2: DELETE STUDENT CONFIRMATION                     -->
    <!-- ======================================================== -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-md space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#FEF2F2] text-[#EF4444] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-[#102B70]">Delete Student Account</h3>
                    </div>
                    <button type="button" wire:click="closeDeleteModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Are you sure you want to delete the student account for <strong class="text-slate-900">{{ $fullName }}</strong> (ID: <span class="font-mono font-semibold">{{ $schoolId }}</span>)?
                    </p>

                    @if($activeLoansCount > 0)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>This student currently has <strong>{{ $activeLoansCount }} active borrowed book(s)</strong>. Active loans must be returned before deleting the account.</span>
                        </div>
                    @else
                        <div class="rounded-xl border border-red-100 bg-red-50/60 p-3 text-xs text-[#B91C1C] leading-relaxed">
                            This will soft-delete the student record, deactivate their system credentials, and revoke library access.
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeDeleteModal"
                            class="h-10 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="deleteStudent"
                            wire:loading.attr="disabled"
                            @if($activeLoansCount > 0) disabled @endif
                            class="h-10 px-5 rounded-xl bg-[#EF4444] hover:bg-[#DC2626] text-xs font-bold text-white shadow-sm transition active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="deleteStudent" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Delete Student</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
