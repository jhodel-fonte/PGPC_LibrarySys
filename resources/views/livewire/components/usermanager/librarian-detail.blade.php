<div class="space-y-6 font-sans antialiased text-[#0F172A]" x-data="{ activeTab: @entangle('activeTab') }">

    @php
        $user = $this->user;
        $librarian = $this->librarian;
        $fullName = $this->fullName;
        $initials = $this->initials;
        $statusName = $user?->status?->status_name ?? 'Active';
        $statusLower = strtolower($statusName);
        $roleName = $user?->role?->name ?? 'Librarian';
        $schoolId = $librarian?->school_id_number ?? 'Not assigned';
        $contactNum = $librarian?->contact_num ?? 'Not provided';
        $gender = $librarian?->gender ? ucfirst(strtolower($librarian->gender)) : 'Not specified';
        $issuedCount = $librarian?->issuedTransactions?->count() ?? 0;
        $receivedCount = $librarian?->receivedTransactions?->count() ?? 0;
        $paymentsCount = $librarian?->receivedPayments?->count() ?? 0;
        $permissions = $this->assignedPermissions;
    @endphp

    <!-- ======================================================== -->
    <!-- 1. BREADCRUMBS & TOP HEADER (TITLE & ACTIONS)            -->
    <!-- ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <!-- Page Title & Subtitle -->
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Librarian Information Details</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                View the librarian's profile, credentials, and system privileges.
            </p>
        </div>

        @php
            $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
            $canManageLibrarian = auth()->check() && (
                auth()->user()->hasRole('Admin') ||
                auth()->user()->hasPermission('manage_librarian') ||
                auth()->user()->hasPermission('manage_librarians') ||
                auth()->user()->hasPermission('manage_users')
            );
            $isSelf = auth()->check() && (auth()->id() === $user?->id);
            $isTrashed = $user?->trashed();
        @endphp
        @if($canManageLibrarian)
            <!-- Top Right Actions: Change Status, Disable/Enable, Delete, & Edit -->
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
                    href="{{ route($routePrefix . 'user-management.edit', $user?->encrypted_id ?? \App\Models\Account::encodeId($this->userId)) }}"
                    wire:navigate
                    class="h-11 px-5 sm:px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                    <span>Edit Librarian Information</span>
                </a>
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- 2. TOP PROFILE HERO BANNER                               -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.profile-banner')

    <!-- ======================================================== -->
    <!-- 3. NAVIGATION TABS BAR                                   -->
    <!-- ======================================================== -->
    <div class="border-b border-[#E2E8F0]">
        <nav class="flex items-center gap-1 sm:gap-4 overflow-x-auto" aria-label="Librarian details tabs">
            
            <!-- Tab 1: Personal Information -->
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

            <!-- Tab 2: Account & Permissions -->
            <button
                type="button"
                @click="activeTab = 'account'"
                class="group inline-flex items-center gap-2 py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer whitespace-nowrap"
                :class="activeTab === 'account' ? 'border-[#102B70] text-[#102B70]' : 'border-transparent text-slate-500 hover:text-[#102B70] hover:border-slate-300'"
            >
                <svg class="w-4 h-4 transition-colors" :class="activeTab === 'account' ? 'text-[#102B70]' : 'text-slate-400 group-hover:text-[#102B70]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Account & Permissions</span>
            </button>

        </nav>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: PERSONAL INFORMATION                             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'personal'" x-cloak>
        <div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-8 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-9">
            
            <!-- SECTION 1: PERSONAL IDENTITY INFORMATION -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                    <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
                    <h3 class="text-base font-bold text-[#102B70]">Personal Identity Information</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
                    
                    <!-- First Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">First Name</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $librarian?->first_name ?: '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- Middle Name -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-[#334155]">Middle Name</label>
                            <span class="text-[11px] font-semibold text-slate-400">Optional</span>
                        </div>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $librarian?->middle_name ?: '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- Last Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">Last Name</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $librarian?->last_name ?: '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">Gender</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="10" cy="11" r="5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h4v4m0-4-4.5 4.5M10 16v5m-2.5-2.5h5" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $gender }}
                            </div>
                        </div>
                    </div>

                    <!-- Contact Number -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">Contact Number</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $contactNum }}
                            </div>
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">Email Address</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $user->email ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: ACCOUNT & PERMISSIONS                            -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'account'" x-cloak>
        <div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-8 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-9">
            
            <!-- SECTION 1: ACCOUNT CREDENTIALS & LOGIN OVERVIEW -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                    <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
                    <h3 class="text-base font-bold text-[#102B70]">Account</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
                    
                    <!-- Username -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">System Username</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <span class="font-bold text-sm w-4 text-center">@</span>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                                {{ $user->username ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Email Verification -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#334155]">Email</label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold flex items-center justify-between">
                                <span class="truncate text-slate-800">{{ $user->email ?? 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Current Status -->
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
                                {{ $user->last_login ? $user->last_login->format('M d, Y h:i A') : 'Never logged in' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: ASSIGNED CAPABILITIES & MODULE PERMISSIONS -->
            <div class="space-y-6 pt-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
                        <h3 class="text-base font-bold text-[#102B70]">Permissions - {{ $roleName }}</h3>
                    </div>
                </div>

                @php
                    $groupedPermissions = collect($permissions)->groupBy('group');
                @endphp

                <div class="space-y-6">
                    @foreach($groupedPermissions as $groupName => $groupPerms)
                        <div class="space-y-3">
                            <!-- Group Subheader -->
                            <div class="flex items-center justify-between px-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ $groupName }}</h4>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500">
                                    {{ $groupPerms->where('allowed', true)->count() }} of {{ $groupPerms->count() }} Allowed
                                </span>
                            </div>

                            <!-- Group Permissions Cards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                @foreach($groupPerms as $permKey => $perm)
                                    <div class="p-4 rounded-2xl border transition-all {{ $perm['allowed'] ? 'border-blue-200 bg-blue-50/20 shadow-xs' : 'border-slate-200 bg-slate-50/50 opacity-60' }} flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h5 class="text-sm font-bold text-[#0F172A]">{{ $perm['title'] }}</h5>
                                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $perm['description'] }}</p>
                                        </div>
                                        <div class="shrink-0 mt-0.5">
                                            @if($perm['allowed'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Allowed</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <span>Restricted</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SECTION 3: DANGER ZONE -->
            @if($canManageLibrarian && !$isSelf)
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                        <div class="h-6 w-1 bg-[#EF4444] rounded-full"></div>
                        <h3 class="text-base font-bold text-[#B91C1C]">Danger Zone</h3>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Action 1: Disable / Enable Account (Soft Delete) -->
                        <div class="rounded-2xl border {{ $isTrashed ? 'border-emerald-200 bg-emerald-50/50' : 'border-amber-200 bg-amber-50/40' }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-[#0F172A]">
                                    {{ $isTrashed ? 'Enable Librarian Account' : 'Disable Librarian Account (Soft Delete)' }}
                                </h4>
                                <p class="text-xs text-[#64748B] max-w-xl leading-relaxed">
                                    @if($isTrashed)
                                        This account is currently soft-deleted (disabled). Re-enable this account to restore system access and reactivate the librarian profile.
                                    @else
                                        Temporarily suspend this librarian's login access by soft-deleting the account. All profile data and historical circulation transactions will remain safely intact in the database.
                                    @endif
                                </p>
                            </div>

                            @if($isTrashed)
                                <button
                                    type="button"
                                    wire:click="enableAccount"
                                    class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
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
                                    class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                >
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <span>Disable Account</span>
                                </button>
                            @endif
                        </div>

                        <!-- Action 2: Permanently Delete Account (Hard Delete) -->
                        <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2]/60 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-[#0F172A]">Permanently Delete Librarian (Hard Delete)</h4>
                                <p class="text-xs text-[#64748B] max-w-xl leading-relaxed">
                                    Permanently purge this librarian account and associated records from the database. This action uses a complete hard delete and cannot be undone.
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="openDeleteModal"
                                class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs sm:text-sm font-bold shadow-xs transition-all active:scale-[0.98] cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-[#EF4444]/40"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span>Delete Permanently</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: STATUS CHANGE                                     -->
    <!-- ======================================================== -->
    @if($showStatusModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-md space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-[#102B70]">Change Librarian Status</h3>
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
                                            name="statusOptionLibrarian"
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
    <!-- MODAL: DISABLE LIBRARIAN (SOFT DELETE)                   -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDisableModal,
        'type' => 'disable',
        'title' => 'Disable Librarian Account',
        'subtitle' => 'Soft Delete / Account Deactivation',
        'targetRole' => 'librarian',
        'targetName' => $fullName,
        'targetId' => $schoolId,
        'bulletPoints' => [
            'The account will be soft-deleted and marked as <strong>Inactive</strong>.',
            'The librarian will no longer be able to log in or perform system tasks.',
            'All transaction records and circulation history are preserved.',
            'You can re-enable this account at any time.',
        ],
        'closeAction' => 'closeDisableModal',
        'confirmAction' => 'disableAccount',
        'confirmText' => 'Disable Account',
    ])

    <!-- ======================================================== -->
    <!-- MODAL: PERMANENT DELETE (HARD DELETE)                    -->
    <!-- ======================================================== -->
    @php
        $totalLibrarianTransactions = (int)($issuedCount + $receivedCount + $paymentsCount);
    @endphp
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDeleteModal,
        'type' => 'danger',
        'isHardDelete' => true,
        'title' => 'Permanently Delete Librarian',
        'subtitle' => 'Irreversible Hard Delete',
        'targetRole' => 'librarian',
        'targetName' => $fullName,
        'targetId' => $schoolId,
        'hasHistory' => ($totalLibrarianTransactions > 0),
        'historyCount' => $totalLibrarianTransactions,
        'historyMessage' => 'This librarian has recorded <strong>' . $totalLibrarianTransactions . '</strong> library transaction(s) (' . $issuedCount . ' loans, ' . $receivedCount . ' returns, ' . $paymentsCount . ' fine payments).',
        'closeAction' => 'closeDeleteModal',
        'confirmAction' => 'deleteLibrarian',
        'confirmText' => 'Permanently Delete',
        'confirmLoadingTarget' => 'deleteLibrarian',
    ])

</div>
