<div class="relative flex flex-col gap-4 sm:gap-6 min-h-0 flex-1">
    
    <!-- Background Watermark -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
        <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
    </div>

    @php
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
        $canManageLibrarians = auth()->check() && (
            auth()->user()->hasRole('Admin') ||
            auth()->user()->hasPermission('manage_librarian') ||
            auth()->user()->hasPermission('manage_librarians') ||
            auth()->user()->hasPermission('manage_users')
        );
    @endphp

    <!-- 1. Page Header -->
    <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Librarian Management</h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">Manage library staff, roles, and administrative access.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            @if($canManageLibrarians)
                <a href="{{ route($routePrefix . 'user-management.add', ['type' => 'librarian']) }}" wire:navigate class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <span>Add Librarian</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. Table Card -->
    <x-data-table
        :headers="$this->headers"
        :sort="$sort"
        :tabs="[
            'ALL' => 'All Staff (' . $totalLibrarians . ')',
            'Head Librarian' => 'Head Librarian (' . $headLibrarians . ')',
            'Librarian' => 'Librarian (' . $regularLibrarians . ')'
        ]"
        :activeTab="$activeTab"
        :showFilter="true"
        :activeFilterCount="$this->activeFilterCount"
        searchPlaceholder="Search librarians by name, ID number, or email..."
        :paginator="$librarians"
        minWidth="950px"
    >
        <!-- Filter Dropdown -->
        <x-slot:filterDropdown>
            <div class="font-sans select-none">
                <!-- Filter Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-[#102B70]">Filter Staff</h3>
                    </div>
                    @if($this->activeFilterCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                            {{ $this->activeFilterCount }} Active
                        </span>
                    @endif
                </div>

                <div class="space-y-3.5 pt-3.5 pb-4">
                    <!-- Status Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Account Status</label>
                        <select
                            wire:model="tempFilterStatus"
                            class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                        >
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Locked">Locked</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <!-- Role Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Account Role</label>
                        <select
                            wire:model="tempFilterRole"
                            class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                        >
                            <option value="">All Roles</option>
                            <option value="Librarian">Librarian</option>
                            <option value="Head Librarian">Head Librarian</option>
                        </select>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2.5">
                    <button
                        type="button"
                        wire:click="clearFilters"
                        @click="filterOpen = false"
                        class="h-9 px-3.5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#334155] text-xs font-bold transition-colors cursor-pointer"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        wire:click="applyFilters"
                        @click="filterOpen = false"
                        class="flex-1 h-9 px-4 rounded-xl bg-[#102B70] hover:bg-[#0B225E] active:scale-[0.98] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </x-slot:filterDropdown>

        @forelse($librarians as $user)
            @php
                $librarian = $user->librarian;
                $fullName = $librarian ? "{$librarian->first_name} {$librarian->last_name}" : $user->username;
                $idNumber = $librarian?->school_id_number ?? '—';
                $initials = $librarian ? strtoupper(substr($librarian->first_name, 0, 1) . substr($librarian->last_name, 0, 1)) : strtoupper(substr($user->username, 0, 2));
            @endphp
            <tr class="hover:bg-slate-50/70 transition-colors h-[72px] group">
                <!-- STAFF INFO & AVATAR -->
                <td x-show="cols['staff'] !== false" class="px-6 py-4 align-middle">
                    <a href="{{ route($routePrefix . 'user-details', $user->encrypted_id) }}" wire:navigate class="flex items-center gap-3.5 group/user focus:outline-none">
                        @if($librarian?->image_url)
                            <img src="{{ $librarian->image_url }}" alt="{{ $fullName }}" class="w-10 h-10 rounded-full object-cover border border-[#CBD5E1] shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $initials }}
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="text-[15px] font-bold text-[#102B70] group-hover/user:text-blue-700 group-hover/user:underline transition-colors truncate">{{ $fullName }}</span>
                            <span class="text-xs text-slate-500 truncate mt-0.5 font-medium">{{ $user->email ?? 'No email' }}</span>
                        </div>
                    </a>
                </td>

                <!-- EMPLOYEE ID -->
                <td x-show="cols['id_number'] !== false" class="px-6 py-4 align-middle">
                    <span class="text-sm font-semibold text-slate-700 font-mono">{{ $idNumber }}</span>
                </td>

                <!-- ROLE / DESIGNATION -->
                <td x-show="cols['role'] !== false" class="px-6 py-4 align-middle">
                    @if($user->role?->name === 'Head Librarian')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                            Head Librarian
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                            Librarian
                        </span>
                    @endif
                </td>

                <!-- CONTACT NUMBER -->
                <td x-show="cols['contact'] !== false" class="px-6 py-4 align-middle">
                    <span class="text-sm text-slate-700 font-medium">{{ $librarian?->contact_num ?: '—' }}</span>
                </td>

                <!-- ACCOUNT STATUS -->
                <td x-show="cols['status'] !== false" class="px-6 py-4 align-middle">
                    @if($user->trashed())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            Disabled
                        </span>
                    @else
                        @php
                            $statusName = $user->status?->status_name ?? 'Unknown';
                            $statusClass = match(strtolower($statusName)) {
                                'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'locked', 'suspended' => 'bg-red-50 text-red-700 border-red-200',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusClass }}">
                            {{ $statusName }}
                        </span>
                    @endif
                </td>

                <!-- LAST LOGIN -->
                <td x-show="cols['last_login'] !== false" class="px-6 py-4 align-middle">
                    @if($user->last_login)
                        <span class="text-sm text-slate-600 font-medium" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                            {{ $user->last_login->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-sm text-slate-400 font-medium">Never</span>
                    @endif
                </td>

                <!-- ACTIONS -->
                <td class="px-6 py-4 align-middle text-right pr-6">
                    <div class="flex items-center justify-end">
                        <x-table-action-dot>
                            <a
                                href="{{ route($routePrefix . 'user-details', $user->encrypted_id) }}"
                                wire:navigate
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-[#102B70] rounded-lg transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Profile
                            </a>

                            @if($canManageLibrarians)
                                <a
                                    href="{{ route($routePrefix . 'user-management.edit', $user->encrypted_id) }}"
                                    wire:navigate
                                    @click="open = false"
                                    class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-[#102B70] rounded-lg transition-colors"
                                >
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    Edit Profile
                                </a>

                                @if(auth()->id() !== $user->id)
                                    @if($user->trashed())
                                        <button
                                            type="button"
                                            wire:click="enableLibrarian({{ $user->id }})"
                                            @click="open = false"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            Enable Account
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="confirmDisable({{ $user->id }})"
                                            @click="open = false"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            Disable Account
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $user->id }})"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-[#B91C1C] hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5 text-[#EF4444]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        Delete Permanently
                                    </button>
                                @endif
                            @endif
                        </x-table-action-dot>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm md:text-base font-semibold">
                    <div class="flex flex-col items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        <p class="text-base font-bold text-slate-700 mb-0.5">No librarians found</p>
                        <p class="text-sm text-slate-500">Try changing the search term or selected filters.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-data-table>

    <!-- ======================================================== -->
    <!-- MODAL: DISABLE LIBRARIAN (SOFT DELETE)                   -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDisableModal,
        'type' => 'disable',
        'title' => 'Disable Librarian Account',
        'subtitle' => 'Soft Delete / Account Deactivation',
        'targetRole' => 'librarian',
        'targetName' => $selectedLibrarianName,
        'bulletPoints' => [
            'The account will be soft-deleted and marked as <strong>Inactive</strong>.',
            'The librarian will no longer be able to log in or perform system tasks.',
            'All transaction records and circulation history are preserved.',
            'You can re-enable this account at any time.',
        ],
        'closeAction' => 'closeDisableModal',
        'confirmAction' => 'disableLibrarian',
        'confirmText' => 'Disable Account',
    ])

    <!-- ======================================================== -->
    <!-- MODAL: HARD DELETE LIBRARIAN                             -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDeleteModal,
        'type' => 'danger',
        'isHardDelete' => true,
        'title' => 'Permanently Delete Librarian',
        'subtitle' => 'Irreversible Hard Delete',
        'targetRole' => 'librarian',
        'targetName' => $selectedLibrarianName,
        'hasHistory' => $selectedLibrarianHasTransactions,
        'historyMessage' => '<strong class="text-slate-900">' . e($selectedLibrarianName) . '</strong> has recorded library transactions (loans, returns, or payments).',
        'closeAction' => 'closeDeleteModal',
        'confirmAction' => 'hardDeleteLibrarian',
        'confirmText' => 'Permanently Delete',
        'confirmLoadingTarget' => 'hardDeleteLibrarian',
    ])
</div>
