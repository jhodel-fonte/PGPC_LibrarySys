<div class="space-y-6 font-sans antialiased text-base text-[#0F172A]" x-data="{ activeTab: @entangle('activeTab') }">

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
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <!-- Page Title & Subtitle -->
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#102B70]">
                {{ $this->isProfile ? 'My Profile' : 'Student Information Details' }}
            </h1>
            <p class="text-base text-slate-500 font-medium mt-1">
                {{ $this->isProfile ? 'View your personal profile, enrollment, and library activity.' : "View the student's profile, enrollment, and library activity." }}
            </p>
        </div>

        @php
            $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
            $canEditStudent = \App\Services\EmployeeRoleService::canEditStudent();
        @endphp
        @if($this->isProfile)
            <!-- Student's Own Profile Top Right Action: Reserve Book -->
            <div class="flex w-full flex-col gap-2.5 sm:flex-row lg:w-auto">
                <button
                    type="button"
                    wire:click="openReserveModal"
                    class="h-11 w-full px-5 sm:px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF] sm:w-auto"
                >
                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Reserve a Book</span>
                </button>

                <a
                    href="{{ route('student.reservations') }}"
                    wire:navigate
                    class="h-11 w-full px-5 sm:px-6 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-base font-bold transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#102B70] sm:w-auto"
                >
                    <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <span>Browse Catalog</span>
                </a>
            </div>
        @elseif($canEditStudent)
            <!-- Staff Top Right Actions: Edit Student Information, Change Status & Reserve -->
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <button
                    type="button"
                    wire:click="openReserveModal"
                    class="h-11 px-4 sm:px-5 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
                >
                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Reserve Book for Student</span>
                </button>

                <button
                    type="button"
                    wire:click="openStatusModal"
                    class="h-11 px-4 sm:px-5 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-sm font-bold transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#102B70]"
                >
                    <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Update Status</span>
                </button>

                <a
                    href="{{ route($routePrefix . 'user-management.edit', $user?->encrypted_id ?? \App\Models\Account::encodeId($this->userId)) }}"
                    wire:navigate
                    class="h-11 px-5 sm:px-6 rounded-2xl border border-[#102B70] text-[#102B70] hover:bg-blue-50/50 text-sm font-bold transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                    <span>Edit Student Information</span>
                </a>
            </div>
        @endif
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
                class="group inline-flex items-center gap-2 py-3.5 px-3 sm:px-4 text-sm sm:text-base font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'overview' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-5 h-5 transition-colors" :class="activeTab === 'overview' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Overview</span>
            </button>

            <!-- Tab 2: Personal Information -->
            <button
                type="button"
                @click="activeTab = 'personal'"
                class="group inline-flex items-center gap-2 py-3.5 px-3 sm:px-4 text-sm sm:text-base font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'personal' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-5 h-5 transition-colors" :class="activeTab === 'personal' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span>Personal Information</span>
            </button>

            <!-- Tab 3: Account -->
            <button
                type="button"
                @click="activeTab = 'account'"
                class="group inline-flex items-center gap-2 py-3.5 px-3 sm:px-4 text-sm sm:text-base font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'account' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-5 h-5 transition-colors" :class="activeTab === 'account' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Account</span>
            </button>

            <!-- Tab 4: Circulation History -->
            <button
                type="button"
                @click="activeTab = 'history'"
                class="group inline-flex items-center gap-2 py-3.5 px-3 sm:px-4 text-sm sm:text-base font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'history' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-5 h-5 transition-colors" :class="activeTab === 'history' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Circulation History</span>
            </button>

            <!-- Tab 5: Book Reservations -->
            <button
                type="button"
                @click="activeTab = 'reservations'"
                class="group inline-flex items-center gap-2 py-3.5 px-3 sm:px-4 text-sm sm:text-base font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'reservations' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-5 h-5 transition-colors" :class="activeTab === 'reservations' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Reservations</span>
                @if($this->activeReservationsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-[#FCC719] text-[#102B70]">
                        {{ $this->activeReservationsCount }}
                    </span>
                @endif
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
    <!-- TAB 5: BOOK RESERVATIONS                                -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'reservations'" x-cloak>
        @include('livewire.components.usermanager.student-reservations')
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
                        <label class="text-sm font-bold text-[#334155]">Select New Status <span class="text-red-500">*</span></label>
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
                                        <span class="text-sm font-bold text-slate-800">{{ $statusOption->status_name }}</span>
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
                            class="h-10 px-4 rounded-xl border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="updateStatus"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] cursor-pointer"
                        >
                            Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- MODAL 2: DISABLE STUDENT ACCOUNT (SOFT DELETE)           -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDisableModal,
        'type' => 'disable',
        'title' => 'Disable Student Account',
        'subtitle' => 'Soft Delete / Account Deactivation',
        'targetRole' => 'student',
        'targetName' => $fullName,
        'targetId' => $schoolId,
        'bulletPoints' => [
            'The account will be soft-deleted and marked as <strong>Inactive</strong>.',
            'The student will no longer be able to log in or borrow books.',
            'All circulation history, records, and fines are preserved.',
            'You can re-enable this account at any time.',
        ],
        'closeAction' => 'closeDisableModal',
        'confirmAction' => 'disableAccount',
        'confirmText' => 'Disable Account',
    ])

    <!-- ======================================================== -->
    <!-- MODAL 3: DELETE STUDENT CONFIRMATION                     -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDeleteModal,
        'type' => 'delete',
        'title' => 'Delete Student Account',
        'targetRole' => 'student',
        'targetName' => $fullName,
        'targetId' => $schoolId,
        'hasHistory' => ($activeLoansCount > 0),
        'historyCount' => $activeLoansCount,
        'historyMessage' => 'This student currently has <strong>' . $activeLoansCount . ' active borrowed book(s)</strong>. Active loans must be returned before deleting the account.',
        'warningNotice' => 'This will soft-delete the student record, deactivate their system credentials, and revoke library access.',
        'closeAction' => 'closeDeleteModal',
        'confirmAction' => 'deleteStudent',
        'confirmText' => 'Delete Student',
        'confirmLoadingTarget' => 'deleteStudent',
    ])

    <!-- ======================================================== -->
    <!-- MODAL 4: RESERVE BOOK MODAL                              -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.student-reserve-modal')
</div>
