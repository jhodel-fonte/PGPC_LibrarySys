<div
    x-data="{
        selected: @entangle('selectedStudents').live,

        isSelected(id) {
            const sId = String(id);
            return Array.isArray(this.selected) && this.selected.some(x => String(x) === sId);
        },

        toggle(id) {
            const sId = String(id);
            if (!Array.isArray(this.selected)) this.selected = [];
            const idx = this.selected.findIndex(x => String(x) === sId);
            if (idx > -1) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push(sId);
            }
        },

        toggleCurrentPage(ids) {
            if (!Array.isArray(this.selected)) this.selected = [];
            const strIds = ids.map(x => String(x));
            const allSelected = strIds.length > 0 && strIds.every(id => this.isSelected(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !strIds.includes(String(id)));
            } else {
                const toAdd = strIds.filter(id => !this.isSelected(id));
                this.selected = [...this.selected, ...toAdd];
            }
        },

        isAllCurrentPageSelected(ids) {
            if (!Array.isArray(this.selected)) return false;
            const strIds = ids.map(x => String(x));
            return strIds.length > 0 && strIds.every(id => this.isSelected(id));
        },

        isSomeCurrentPageSelected(ids) {
            if (!Array.isArray(this.selected)) return false;
            const strIds = ids.map(x => String(x));
            return strIds.some(id => this.isSelected(id)) && !this.isAllCurrentPageSelected(ids);
        },

        clearAll() {
            this.selected = [];
        }
    }"
    class="relative flex flex-col gap-4 sm:gap-6 min-h-0 flex-1"
>
    <!-- Background Watermark -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
        <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
    </div>

    @php
        $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        $isSuperAdmin = \App\Services\EmployeeRoleService::isAdmin();
        $canCreateStudent = \App\Services\EmployeeRoleService::canCreateStudent();
        $canEditStudent = \App\Services\EmployeeRoleService::canEditStudent();
        $canDeleteStudent = \App\Services\EmployeeRoleService::canDeleteStudent();
        $currentPageIds = $students->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();

        $studentTabs = [
            'ALL' => 'All Students (' . $totalStudents . ')',
            'Active' => 'Active (' . $activeStudents . ')',
            'Inactive' => 'Inactive (' . $inactiveStudents . ')',
            'Suspended' => 'Suspended/Locked (' . $lockedStudents . ')',
        ];
        if ($isSuperAdmin) {
            $studentTabs['Deleted'] = 'Deleted (' . ($deletedStudents ?? 0) . ')';
        }
    @endphp

    <!-- 1. Page Header -->
    <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Student Management</h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">Manage student members, course enrollments, and accounts.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            @if($canCreateStudent)
                <a href="{{ route($routePrefix . 'user-management.import') }}" wire:navigate class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-slate-300 text-[#102B70] text-sm font-semibold">Import Students</a>
                <a href="{{ route($routePrefix . 'user-management.add', ['type' => 'student']) }}" wire:navigate class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2 w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    <span>Add Student</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. Table Card -->
    <x-widget.data-table
        :headers="$this->headers"
        :sort="$sort"
        :tabs="$studentTabs"
        :activeTab="$activeTab"
        :showFilter="true"
        :activeFilterCount="$this->activeFilterCount"
        searchPlaceholder="Search students by name, ID number, program, or email..."
        :paginator="$students"
        minWidth="950px"
        :selectable="true"
        selectAllModel="selectAll"
        :selectedCount="count($selectedStudents)"
        perPageModel="perPage"
    >
        <x-slot:headerCheckbox>
            <input
                type="checkbox"
                :checked="isAllCurrentPageSelected(@js($currentPageIds))"
                :indeterminate="isSomeCurrentPageSelected(@js($currentPageIds))"
                @change="toggleCurrentPage(@js($currentPageIds))"
                class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer"
                aria-label="Select all students on this page"
            >
        </x-slot:headerCheckbox>

        <!-- Bulk Actions Slot -->
        <x-slot:bulkActions>
            @if($activeTab === 'Deleted' && $isSuperAdmin && ($canEditStudent || $canDeleteStudent) && count($selectedStudents) > 0)
                <button
                    type="button"
                    wire:click="bulkRestore"
                    class="rounded-lg border border-emerald-300 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition-colors hover:bg-emerald-100 active:translate-y-px cursor-pointer"
                >
                    Restore Selected
                </button>
            @endif

            @if($canDeleteStudent && $activeTab !== 'Deleted' && count($selectedStudents) > 0)
                <button
                    type="button"
                    wire:click="bulkDelete"
                    class="rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-red-700 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 cursor-pointer"
                >
                    Delete Selected
                </button>
            @endif

            <button
                type="button"
                @click="clearAll()"
                wire:click="clearSelection"
                class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1 cursor-pointer"
            >
                <span>&times;</span> Clear
            </button>
        </x-slot:bulkActions>

        <!-- Filter Dropdown -->
        <x-slot:filterDropdown>
            <div class="font-sans select-none">
                <!-- Filter Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-[#102B70]">Filter Students</h3>
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

                    <!-- Program Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Degree Program</label>
                        <select
                            wire:model="tempFilterProgram"
                            class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                        >
                            <option value="">All Programs</option>
                            @foreach($programs as $abbr => $programName)
                                <option value="{{ $abbr }}">{{ $abbr }} - {{ $programName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Level Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Year Level</label>
                        <select
                            wire:model="tempFilterYearLevel"
                            class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                        >
                            <option value="">All Year Levels</option>
                            @foreach($yearLevels as $yl)
                                <option value="{{ $yl }}">{{ $yl }}</option>
                            @endforeach
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

        @forelse($students as $user)
            @php
                $student = $user->student;
                $isTrashed = $user->trashed() || ($student && $student->trashed());
                $fullName = $student ? "{$student->first_name} {$student->last_name}" : $user->username;
                $idNumber = $student?->school_id_number ?? '—';
                $initials = $student ? strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) : strtoupper(substr($user->username, 0, 2));
            @endphp
            <tr
                :class="isSelected('{{ $user->id }}') ? 'bg-blue-50/60' : '{{ $isTrashed ? 'bg-rose-50/25' : '' }}'"
                class="hover:bg-slate-50/70 transition-colors h-auto md:h-[72px] group {{ $isTrashed ? 'opacity-85' : '' }}"
            >
                <!-- Mobile Card Cell (Visible on mobile screens <= 768px) -->
                <td colspan="7" class="mobile-cell">
                    <div
                        :class="isSelected('{{ $user->id }}') ? 'border-[#102B70] bg-blue-50/30 ring-1 ring-[#102B70]' : ''"
                        class="bg-white rounded-2xl border border-[#E2E8F0] p-4 shadow-xs relative transition-all hover:shadow-sm"
                    >
                        <!-- Top Row: Checkbox, Avatar, Name, Email, ID & 3-dots Menu -->
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <!-- Selection Checkbox -->
                                <div class="shrink-0 pt-1">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected('{{ $user->id }}')"
                                        @change="toggle('{{ $user->id }}')"
                                        value="{{ (string)$user->id }}"
                                        class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer"
                                        aria-label="Select student {{ $fullName }}"
                                    >
                                </div>

                                <a href="{{ route($routePrefix . 'user-details', $user->encrypted_id) }}" wire:navigate class="flex items-start gap-3 min-w-0 flex-1 focus:outline-none">
                                    @if($student?->image_url)
                                        <img src="{{ $student->image_url }}" alt="{{ $fullName }}" class="w-11 h-11 rounded-full object-cover border border-[#CBD5E1] shrink-0">
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

                                    @if($canEditStudent && !$isTrashed)
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

                                    @if($isTrashed)
                                        @if($canEditStudent || $canDeleteStudent)
                                            <button
                                                type="button"
                                                wire:click="restoreStudent({{ $user->id }})"
                                                @click="open = false"
                                                class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                            >
                                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                                Restore Student
                                            </button>
                                        @endif
                                    @else
                                        @if($canDeleteStudent && auth()->id() !== $user->id)
                                            <div class="border-t border-slate-100 my-1"></div>
                                            <button
                                                type="button"
                                                wire:click="confirmDelete({{ $user->id }})"
                                                @click="open = false"
                                                class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                            >
                                                <svg class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete Student
                                            </button>
                                        @endif
                                    @endif
                                </x-widget.table-action-dot>
                            </div>
                        </div>

                        <!-- Card Body: Program, Status & Last Login Grid -->
                        <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 gap-y-2.5 gap-x-3 text-left">
                            <div x-show="cols['program'] !== false">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program</span>
                                <span class="text-xs font-bold text-slate-800 truncate block">{{ $student?->program ?? '—' }}</span>
                                @if($student?->year_level)
                                    <span class="text-[11px] text-slate-500 font-medium block">{{ $student->year_level }}</span>
                                @endif
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

                            <div x-show="cols['last_login'] !== false" class="col-span-2 pt-1 border-t border-slate-50 flex items-center justify-between text-xs text-slate-500">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Last Login</span>
                                <span class="font-medium text-slate-600">
                                    {{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Desktop Cells (Visible on desktop screens > 768px) -->
                <!-- Selection Checkbox -->
                <td class="desktop-cell w-12 px-4 py-4 align-middle text-center">
                    <input
                        type="checkbox"
                        :checked="isSelected('{{ $user->id }}')"
                        @change="toggle('{{ $user->id }}')"
                        value="{{ (string)$user->id }}"
                        class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer"
                        aria-label="Select student {{ $fullName }}"
                    >
                </td>

                <!-- STUDENT INFO & AVATAR -->
                <td x-show="cols['student'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    <a href="{{ route($routePrefix . 'user-details', $user->encrypted_id) }}" wire:navigate class="flex items-center gap-3.5 group/user focus:outline-none">
                        @if($student?->image_url)
                            <img src="{{ $student->image_url }}" alt="{{ $fullName }}" class="w-10 h-10 rounded-full object-cover border border-[#CBD5E1] shrink-0">
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

                <!-- STUDENT ID NUMBER -->
                <td x-show="cols['id_number'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    <span class="text-sm font-semibold text-slate-700 font-mono">{{ $idNumber }}</span>
                </td>

                <!-- PROGRAM & YEAR LEVEL -->
                <td x-show="cols['program'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-800">{{ $student?->program ?? '—' }}</span>
                        @if($student?->year_level)
                            <span class="text-xs text-slate-500 font-medium">{{ $student->year_level }}</span>
                        @endif
                    </div>
                </td>

                <!-- ACCOUNT STATUS -->
                <td x-show="cols['status'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    @if($isTrashed)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
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

                <!-- LAST LOGIN -->
                <td x-show="cols['last_login'] !== false" class="desktop-cell px-6 py-4 align-middle">
                    @if($user->last_login)
                        <span class="text-sm text-slate-600 font-medium" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                            {{ $user->last_login->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-sm text-slate-400 font-medium">Never</span>
                    @endif
                </td>

                <!-- ACTIONS -->
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

                            @if($canEditStudent && !$isTrashed)
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

                            @if($isTrashed)
                                @if($canEditStudent || $canDeleteStudent)
                                    <button
                                        type="button"
                                        wire:click="restoreStudent({{ $user->id }})"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        Restore Student
                                    </button>
                                @endif
                            @else
                                @if($canDeleteStudent && auth()->id() !== $user->id)
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $user->id }})"
                                        @click="open = false"
                                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                    >
                                        <svg class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete Student
                                    </button>
                                @endif
                            @endif
                        </x-widget.table-action-dot>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm md:text-base font-semibold">
                    <div class="flex flex-col items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        <p class="text-base font-bold text-slate-700 mb-0.5">No students found</p>
                        <p class="text-sm text-slate-500">Try changing the search term or selected filters.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-widget.data-table>

    <!-- ======================================================== -->
    <!-- MODAL: DELETE STUDENT CONFIRMATION (SOFT DELETE)         -->
    <!-- ======================================================== -->
    @include('livewire.components.usermanager.alert-modal', [
        'show' => $showDeleteModal,
        'type' => 'delete',
        'title' => 'Delete Student Account',
        'targetRole' => 'student',
        'targetName' => $selectedStudentName,
        'targetId' => $selectedStudentSchoolId,
        'hasHistory' => ($selectedStudentActiveLoans > 0),
        'historyCount' => $selectedStudentActiveLoans,
        'historyMessage' => 'This student currently has <strong>' . $selectedStudentActiveLoans . ' active borrowed book(s)</strong>. Active loans must be returned before deleting the account.',
        'warningNotice' => 'This will soft-delete the student record, deactivate their system credentials, and revoke library access.',
        'closeAction' => 'closeDeleteModal',
        'confirmAction' => 'deleteStudent',
        'confirmText' => 'Delete Student',
        'confirmLoadingTarget' => 'deleteStudent',
    ])
</div>
