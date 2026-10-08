<div class="min-h-full bg-[#F5F7FB] pb-14 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1480px] space-y-4 px-3 py-4 sm:space-y-5 sm:px-6 lg:px-8 lg:py-7">

        @php
            $user = $this->user;
            $person = $this->person;
            $fullName = $this->fullName;
            $initials = $this->initials;
            $statusName = $user->status?->status_name ?? 'Active';
            $statusLower = strtolower($statusName);
            $roleName = $user->role?->name ?? 'User';
            $isStudent = $roleName === 'Member' || $user->student;
            $isLibrarian = $roleName === 'Librarian' || $user->librarian;
            $schoolId = $person?->school_id_number ?? 'Not assigned';
            $contactNum = $person?->contact_num ?? 'Not provided';
        @endphp

        <!-- Header navigation and quick actions -->
        <header class="flex flex-col gap-3.5 xl:flex-row xl:items-end xl:justify-between">
            <div class="flex items-center gap-2.5 sm:items-start sm:gap-3.5">
                <a
                    href="{{ route('admin.user-management') }}"
                    wire:navigate
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm shadow-slate-200/50 transition hover:border-[#102B70]/30 hover:bg-blue-50 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 sm:mt-0.5"
                    title="Back to User Management"
                    aria-label="Back to User Management"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="mb-0.5 flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <span>User Management</span>
                    </div>
                    <h1 class="truncate text-xl font-extrabold tracking-tight text-[#102B70] sm:text-2xl md:text-[1.75rem]">User Profile</h1>
                </div>
            </div>

            <div class="flex items-center xl:justify-end">
                <button
                    type="button"
                    wire:click="openEditModal"
                    class="inline-flex h-10 w-full sm:w-auto items-center justify-center gap-1.5 whitespace-nowrap rounded-xl bg-[#102B70] px-3.5 text-xs font-bold text-white shadow-md shadow-blue-950/15 transition hover:bg-[#0B225E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 active:translate-y-px sm:h-11 sm:gap-2 sm:px-5"
                >
                    <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    <span class="truncate">Edit Profile</span>
                </button>
            </div>
        </header>

        <!-- Profile identity banner -->
        <livewire:components.usermanager.profile-banner
            :user="$user"
            :fullName="$fullName"
            :initials="$initials"
            :roleName="$roleName"
            :statusName="$statusName"
            :statusLower="$statusLower"
            :isStudent="$isStudent"
            :isLibrarian="$isLibrarian"
            :schoolId="$schoolId"
            wire:key="profile-banner-{{ $user->id }}"
        />

        <!-- Section navigation and profile details -->
        <div class="grid grid-cols-1 items-start gap-4 sm:gap-5 lg:grid-cols-12">
            
            <!-- Left Sidebar Navigation -->
            <nav class="overflow-x-auto rounded-2xl bg-white p-1.5 shadow-sm shadow-slate-200/60 ring-1 ring-slate-200 lg:sticky lg:top-5 lg:col-span-3 lg:p-3 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden" aria-label="Profile sections">
                <div class="flex min-w-max gap-1.5 lg:min-w-0 lg:flex-col">
                    <button
                        type="button"
                        wire:click="setTab('personal')"
                        aria-pressed="{{ $activeTab === 'personal' ? 'true' : 'false' }}"
                        class="flex min-h-10 shrink-0 items-center justify-between gap-2.5 rounded-xl px-3.5 py-2 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 sm:min-h-11 sm:gap-3 sm:px-4 sm:py-2.5 sm:text-sm lg:w-full {{ $activeTab === 'personal' ? 'bg-[#102B70] text-white shadow-md shadow-blue-950/15' : 'text-slate-600 hover:bg-blue-50 hover:text-[#102B70]' }}"
                    >
                        <div class="flex items-center gap-2 whitespace-nowrap sm:gap-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <span>Personal Information</span>
                        </div>
                        @if($activeTab === 'personal')
                            <svg class="hidden lg:block w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="setTab('security')"
                        aria-pressed="{{ $activeTab === 'security' ? 'true' : 'false' }}"
                        class="flex min-h-10 shrink-0 items-center justify-between gap-2.5 rounded-xl px-3.5 py-2 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 sm:min-h-11 sm:gap-3 sm:px-4 sm:py-2.5 sm:text-sm lg:w-full {{ $activeTab === 'security' ? 'bg-[#102B70] text-white shadow-md shadow-blue-950/15' : 'text-slate-600 hover:bg-blue-50 hover:text-[#102B70]' }}"
                    >
                        <div class="flex items-center gap-2 whitespace-nowrap sm:gap-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span>Account & Security</span>
                        </div>
                        @if($activeTab === 'security')
                            <svg class="hidden lg:block w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="setTab('activity')"
                        aria-pressed="{{ $activeTab === 'activity' ? 'true' : 'false' }}"
                        class="flex min-h-10 shrink-0 items-center justify-between gap-2.5 rounded-xl px-3.5 py-2 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 sm:min-h-11 sm:gap-3 sm:px-4 sm:py-2.5 sm:text-sm lg:w-full {{ $activeTab === 'activity' ? 'bg-[#102B70] text-white shadow-md shadow-blue-950/15' : 'text-slate-600 hover:bg-blue-50 hover:text-[#102B70]' }}"
                    >
                        <div class="flex items-center gap-2 whitespace-nowrap sm:gap-2.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>Activity Log</span>
                        </div>
                        @if($activeTab === 'activity')
                            <svg class="hidden lg:block w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        @endif
                    </button>
                </div>
            </nav>

            <!-- Right Content Area -->
            <main class="min-w-0 space-y-4 sm:space-y-5 lg:col-span-9">

                <!-- Personal Information Tab -->
                @if($activeTab === 'personal')
                    <livewire:components.usermanager.info-card
                        :user="$user"
                        :fullName="$fullName"
                        :contactNum="$contactNum"
                        :schoolId="$schoolId"
                        :isStudent="$isStudent"
                        :isLibrarian="$isLibrarian"
                        wire:key="info-card-{{ $user->id }}"
                    />

                <!-- Account & Security Tab -->
                @elseif($activeTab === 'security')
                    <div class="grid grid-cols-1 gap-4 sm:gap-5 xl:grid-cols-12">
                        <livewire:components.usermanager.status-card
                            :user="$user"
                            :statusName="$statusName"
                            :statusLower="$statusLower"
                            :roleName="$roleName"
                            wire:key="status-card-{{ $user->id }}"
                        />

                        <livewire:components.usermanager.security-card
                            :user="$user"
                            wire:key="security-card-{{ $user->id }}"
                        />
                    </div>

                <!-- Activity Log Tab -->
                @elseif($activeTab === 'activity')
                    <livewire:components.usermanager.activity-card
                        :user="$user"
                        :statusLower="$statusLower"
                        :activeTab="$activeTab"
                        wire:key="activity-card-{{ $user->id }}"
                    />
                @endif

            </main>

        </div>

    </div>

    <!-- MODAL 1: STATUS CHANGE -->
    @if($showStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="status-dialog-title">
            <div class="my-auto w-full max-w-md space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <h3 id="status-dialog-title" class="text-base font-bold text-[#102B70]">Change Account Status</h3>
                    </div>
                    <button type="button" wire:click="closeStatusModal" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]" aria-label="Close status dialog">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateStatus" class="space-y-4">
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
                                            name="statusOption"
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
                        @error('selectedStatusId') <span class="text-[11px] text-red-600 font-bold">{{ $message }}</span> @enderror
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
                            type="submit"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-xs font-bold text-white shadow-sm transition active:scale-[0.98] cursor-pointer"
                        >
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: EDIT PROFILE -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="edit-profile-dialog-title">
            <div class="my-auto w-full max-w-lg space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl max-h-[92vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <h3 id="edit-profile-dialog-title" class="text-base font-bold text-[#102B70]">Edit User Profile</h3>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]" aria-label="Close edit profile dialog">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveProfile" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">First Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="editFirstName"
                                class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            >
                            @error('editFirstName') <span class="text-[11px] text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Middle Name</label>
                            <input
                                type="text"
                                wire:model="editMiddleName"
                                class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Last Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="editLastName"
                                class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            >
                            @error('editLastName') <span class="text-[11px] text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Contact Number</label>
                            <input
                                type="text"
                                wire:model="editContact"
                                class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            >
                            @error('editContact') <span class="text-[11px] text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-[#334155]">Email Address <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            wire:model="editEmail"
                            class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                        @error('editEmail') <span class="text-[11px] text-red-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    @if($isStudent)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-[#334155]">Program / Course</label>
                                <input
                                    type="text"
                                    wire:model="editProgram"
                                    placeholder="e.g. BS Information Technology"
                                    class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                                >
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-[#334155]">Year Level</label>
                                <input
                                    type="text"
                                    wire:model="editYearLevel"
                                    placeholder="e.g. 3rd Year"
                                    class="w-full h-10 sm:h-11 px-3.5 rounded-xl border border-[#E2E8F0] text-xs font-semibold text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                                >
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Notes</label>
                            <textarea
                                wire:model="editNote"
                                rows="3"
                                placeholder="Add notes about this member..."
                                class="w-full p-3 rounded-xl border border-[#E2E8F0] text-xs font-medium text-slate-800 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            ></textarea>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="h-10 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-xs font-bold text-white shadow-sm transition-all active:scale-[0.98] cursor-pointer"
                        >
                            Save Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
