<div
    class="relative flex flex-col gap-4 sm:gap-6 min-h-0 flex-1"
    x-data="{
        selected: @entangle('selectedLibrarians').live,
        get currentPageIds() {
            return @js($librarians->filter(fn($u) => $u->id !== auth()->id())->pluck('id')->map(fn($id) => (int) $id)->values()->toArray());
        },
        isSelected(id) {
            return this.selected.map(Number).includes(Number(id));
        },
        toggle(id) {
            id = Number(id);
            const idx = this.selected.map(Number).indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
        },
        toggleCurrentPage() {
            const pageIds = this.currentPageIds.map(Number);
            const allSelected = pageIds.length > 0 && pageIds.every(id => this.selected.map(Number).includes(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !pageIds.includes(Number(id)));
            } else {
                const current = this.selected.map(Number);
                pageIds.forEach(id => {
                    if (!current.includes(id)) {
                        this.selected.push(id);
                    }
                });
            }
        },
        isAllCurrentPageSelected() {
            const pageIds = this.currentPageIds.map(Number);
            return pageIds.length > 0 && pageIds.every(id => this.selected.map(Number).includes(id));
        },
        isSomeCurrentPageSelected() {
            const pageIds = this.currentPageIds.map(Number);
            const some = pageIds.some(id => this.selected.map(Number).includes(id));
            return some && !this.isAllCurrentPageSelected();
        },
        clearAll() {
            this.selected = [];
        }
    }"
>
    
    <!-- Background Watermark -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
        <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
    </div>

    @php
        $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        $canManageLibrarians = \App\Services\EmployeeRoleService::canManageLibrarians();
        $isSuperAdmin = \App\Services\EmployeeRoleService::isAdmin();
        $librarianTabs = [
            'ALL' => 'All Staff (' . $totalLibrarians . ')',
            'Head Librarian' => 'Head Librarian (' . $headLibrarians . ')',
            'Librarian' => 'Librarian (' . $regularLibrarians . ')',
        ];
        if ($isSuperAdmin) {
            $librarianTabs['Deleted'] = 'Deleted (' . ($deletedLibrarians ?? 0) . ')';
        }
    @endphp

    <!-- 1. Page Header -->
    <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Librarian Management</h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">Manage library staff, roles, and administrative access.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            @if($canManageLibrarians)
                <a href="{{ route($routePrefix . 'user-management.add', ['type' => 'librarian']) }}" wire:navigate class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <span>Add Librarian</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. Table Card -->
    <x-widget.data-table
        :headers="$this->headers"
        :sort="$sort"
        :tabs="$librarianTabs"
        :activeTab="$activeTab"
        :showFilter="true"
        :activeFilterCount="$this->activeFilterCount"
        searchPlaceholder="Search librarians by name, ID number, or email..."
        :paginator="$librarians"
        :selectable="true"
        selectAllModel="selectAll"
        :selectedCount="count($selectedLibrarians)"
        perPageModel="perPage"
        minWidth="950px"
    >
        <!-- Custom Select-All Header Checkbox -->
        <x-slot:headerCheckbox>
            <input
                type="checkbox"
                @change="toggleCurrentPage()"
                :checked="isAllCurrentPageSelected()"
                :indeterminate.prop="isSomeCurrentPageSelected()"
                class="w-4 h-4 rounded border-[#CBD5E1] text-[#102B70] focus:ring-[#102B70]/20 cursor-pointer transition-colors"
                title="Select all librarians on this page"
            >
        </x-slot:headerCheckbox>

        <!-- Bulk Action Buttons -->
        <x-slot:bulkActions>
            @if($canManageLibrarians)
                @if($activeTab === 'Deleted' && $isSuperAdmin)
                    <button
                        type="button"
                        wire:click="bulkRestore"
                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition-colors cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Restore Selected</span>
                    </button>
                @elseif($activeTab !== 'Deleted')
                    <button
                        type="button"
                        wire:click="bulkDelete"
                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-colors cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Delete Selected</span>
                    </button>
                @endif
            @endif
            <button
                type="button"
                @click="clearAll()"
                class="inline-flex items-center h-8 px-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 transition-colors cursor-pointer"
            >
                Clear
            </button>
        </x-slot:bulkActions>
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
                $isTrashed = $user->trashed() || ($librarian && $librarian->trashed());
                $isSelf = auth()->id() === $user->id;
            @endphp
            <tr
                class="hover:bg-slate-50/70 transition-colors h-auto md:h-[72px] group"
                :class="isSelected({{ $user->id }}) ? 'bg-[#EFF6FF]/60 hover:bg-[#EFF6FF]/80' : ''"
            >
                <!-- Mobile Card Cell (Visible on mobile screens <= 768px) -->
                <td colspan="8" class="mobile-cell">
                    <div
                        class="bg-white rounded-2xl border border-[#E2E8F0] p-4 shadow-xs relative transition-all hover:shadow-sm"
                        :class="isSelected({{ $user->id }}) ? 'border-[#102B70]/40 bg-[#EFF6FF]/30' : ''"
                    >
                        <!-- Top Row: Checkbox, Avatar, Name, Email, ID & 3-dots Menu -->
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                <input
                                    type="checkbox"
                                    value="{{ $user->id }}"
                                    :checked="isSelected({{ $user->id }})"
                                    @change="toggle({{ $user->id }})"
                                    @if($isSelf) disabled @endif
                                    class="mt-1 w-4 h-4 rounded border-[#CBD5E1] text-[#102B70] focus:ring-[#102B70]/20 cursor-pointer shrink-0 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                <a href="{{ route($routePrefix . 'user-details', $user->encrypted_id) }}" wire:navigate class="flex items-start gap-3 min-w-0 flex-1 focus:outline-none">
                                    @if($librarian?->image_url)
                                        <img src="{{ $librarian->image_url }}" alt="{{ $fullName }}" class="w-11 h-11 rounded-full object-cover border border-[#CBD5E1] shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div class="flex flex-col min-w-0 flex-1">
                                        <span class="text-sm font-bold text-[#102B70] hover:text-blue-700 hover:underline transition-colors truncate">{{ $fullName }}</span>
                                        <span class="text-xs text-slate-500 truncate mt-0.5 font-medium">{{ $user->email ?? 'No email' }}</span>
                                        <span class="text-[11px] font-mono text-slate-600 font-semibold mt-0.5">{{ $idNumber }}</span>
                                    </div>
                                </a>
                            </div>

                            <!-- 3-Dots Actions Menu -->
                            <div class="shrink-0 -mr-1">
                                <x-widget.table-action-dot>
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
                                        @if(!$isTrashed)
                                            <a
                                                href="{{ route($routePrefix . 'user-management.edit', $user->encrypted_id) }}"
                                                wire:navigate
                                                @click="open = false"
                                                class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-[#102B70] rounded-lg transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                Edit Profile
                                            </a>
                                        @endif

                                        @if(!$isSelf)
                                            @if($isTrashed)
                                                <button
                                                    type="button"
                                                    wire:click="enableLibrarian({{ $user->id }})"
                                                    @click="open = false"
                                                    class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                    Restore Account
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="confirmDelete({{ $user->id }})"
                                                    @click="open = false"
                                                    class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-[#B91C1C] hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    <svg class="h-3.5 w-3.5 text-[#EF4444]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    Delete Librarian
                                                </button>
                                            @endif
                                        @endif
                                    @endif
                                </x-widget.table-action-dot>
                            </div>
                        </div>

                        <!-- Card Body: Role, Status, Contact & Last Login Grid -->
                        <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 gap-y-2.5 gap-x-3 text-left">
                            <div x-show="cols['role'] !== false">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Role</span>
                                <div class="mt-0.5">
                                    @if($user->role?->name === 'Head Librarian')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                                            Head Librarian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                                            Librarian
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div x-show="cols['status'] !== false">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
                                @if($isTrashed)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Deleted
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusClass }}">
                                        {{ $statusName }}
                                    </span>
                                @endif
                            </div>

                            <div x-show="cols['contact'] !== false">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Contact</span>
                                <span class="text-xs font-semibold text-slate-700 block mt-0.5">{{ $librarian?->contact_num ?: '—' }}</span>
                            </div>

                            <div x-show="cols['last_login'] !== false">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Last Login</span>
                                <span class="text-xs font-semibold text-slate-600 block mt-0.5">
                                    {{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- ROW SELECTION CHECKBOX (Desktop) -->
                <td class="desktop-cell w-12 pl-6 pr-2 py-4 align-middle">
                    <input
                        type="checkbox"
                        value="{{ $user->id }}"
                        :checked="isSelected({{ $user->id }})"
                        @change="toggle({{ $user->id }})"
                        @if($isSelf) disabled @endif
                        class="w-4 h-4 rounded border-[#CBD5E1] text-[#102B70] focus:ring-[#102B70]/20 cursor-pointer transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                </td>

                <!-- STAFF INFO & AVATAR (Desktop) -->
                <td x-show="cols['staff'] !== false" class="desktop-cell px-6 py-4 align-middle">
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

                <!-- EMPLOYEE ID (Desktop) -->
                <td x-show="cols['id_number'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    <span class="text-sm font-semibold text-slate-700 font-mono">{{ $idNumber }}</span>
                </td>

                <!-- ROLE / DESIGNATION (Desktop) -->
                <td x-show="cols['role'] !== false" class="desktop-cell px-6 py-4 align-middle">
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

                <!-- CONTACT NUMBER (Desktop) -->
                <td x-show="cols['contact'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    <span class="text-sm text-slate-700 font-medium">{{ $librarian?->contact_num ?: '—' }}</span>
                </td>

                <!-- ACCOUNT STATUS (Desktop) -->
                <td x-show="cols['status'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    @if($isTrashed)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            Deleted
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

                <!-- LAST LOGIN (Desktop) -->
                <td x-show="cols['last_login'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    @if($user->last_login)
                        <span class="text-sm text-slate-600 font-medium" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                            {{ $user->last_login->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-sm text-slate-400 font-medium">Never</span>
                    @endif
                </td>

                <!-- ACTIONS (Desktop) -->
                <td class="desktop-cell px-6 py-4 align-middle text-right pr-6">
                    <div class="flex items-center justify-end">
                        <x-widget.table-action-dot>
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
                                @if(!$isTrashed)
                                    <a
                                        href="{{ route($routePrefix . 'user-management.edit', $user->encrypted_id) }}"
                                        wire:navigate
                                        @click="open = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-[#102B70] rounded-lg transition-colors"
                                    >
                                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        Edit Profile
                                    </a>
                                @endif

                                @if(!$isSelf)
                                    @if($isTrashed)
                                        <button
                                            type="button"
                                            wire:click="enableLibrarian({{ $user->id }})"
                                            @click="open = false"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            Restore Account
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $user->id }})"
                                            @click="open = false"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-[#B91C1C] hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        >
                                            <svg class="h-3.5 w-3.5 text-[#EF4444]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Delete Librarian
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </x-widget.table-action-dot>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-slate-500 text-sm md:text-base font-semibold">
                    <div class="flex flex-col items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        <p class="text-base font-bold text-slate-700 mb-0.5">No librarians found</p>
                        <p class="text-sm text-slate-500">Try changing the search term or selected filters.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-widget.data-table>

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
    <!-- MODAL: SOFT DELETE LIBRARIAN                             -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDeleteModal,
        'type' => 'delete',
        'isHardDelete' => false,
        'title' => 'Soft Delete Librarian Record',
        'subtitle' => 'Soft Delete / Archive Librarian',
        'targetRole' => 'librarian',
        'targetName' => $selectedLibrarianName,
        'bulletPoints' => [
            'The librarian account and profile will be <strong>soft-deleted</strong> and marked as <strong>Inactive</strong>.',
            'They will no longer appear in active staff lists or be able to log in.',
            'All historical library transactions and audit records remain intact.',
            'Only a Superadmin can view and restore deleted accounts from the Deleted filter.',
        ],
        'closeAction' => 'closeDeleteModal',
        'confirmAction' => 'deleteLibrarian',
        'confirmText' => 'Delete Librarian',
        'confirmLoadingTarget' => 'deleteLibrarian',
    ])
</div>
