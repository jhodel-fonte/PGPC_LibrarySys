<div class="bg-[#F8FAFC] lg:h-full lg:flex lg:flex-col lg:min-h-0">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-6 lg:h-full lg:min-h-0 lg:flex-1">
        
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">User Management</h1>
                <p class="mt-1 text-sm text-slate-500 font-medium">Manage members, librarians, and login accounts.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    <span>Add Student</span>
                </button>
                <button class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg border border-[#CBD5E1] bg-white text-[#102B70] hover:bg-slate-50 hover:border-[#102B70] text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <span>Add Librarian</span>
                </button>
            </div>
        </div>

        <!-- 3. Table Card -->
        <x-data-table
            :headers="$this->headers"
            :sort="$sort"
            :tabs="[
                'ALL' => 'ALL (' . ($totalStudents + $totalLibrarians) . ')',
                'Student' => 'Student (' . $totalStudents . ')',
                'Librarian' => 'Librarian (' . $totalLibrarians . ')'
            ]"
            :activeTab="$activeTab"
            :showFilter="true"
            :activeFilterCount="$this->activeFilterCount"
            searchPlaceholder="Search users..."
            :paginator="$users"
            minWidth="950px"
        >
            <!-- Filter Dropdown-->
            <x-slot:filterDropdown>
                <div class="font-sans select-none">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <h3 class="text-sm font-bold text-[#102B70]">Filter Options</h3>
                        </div>
                        @if($this->activeFilterCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                                {{ $this->activeFilterCount }} Active
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3.5 pt-3.5 pb-4">
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

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#334155]">Account Role</label>
                            <select
                                wire:model="tempFilterRole"
                                class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                            >
                                <option value="">All Roles</option>
                                <option value="Student">Student</option>
                                <option value="Librarian">Librarian</option>
                                <option value="Head Librarian">Head Librarian</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#334155]">Email Verification</label>
                            <select
                                wire:model="tempFilterVerification"
                                class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                            >
                                <option value="">All Verification States</option>
                                <option value="verified">Verified</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                    </div>

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
            @forelse($users as $user)
                @php
                    $person = $user->role?->name === 'Librarian' ? $user->librarian : $user->student;
                    $fullName = $person ? "{$person->first_name} {$person->last_name}" : $user->username;
                    $idNumber = $person ? $person->school_id_number : '—';
                    $initials = collect(explode(' ', $fullName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors h-[72px] group">
                    <!-- USER -->
                    <td x-show="cols['user'] !== false" class="px-6 py-4 align-middle">
                        <div class="flex items-center gap-3.5">
                            <div class="h-10 w-10 rounded-full bg-[#E8EEFC] text-[#102B70] flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold">{{ strtoupper($initials) }}</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[14px] font-bold text-[#102B70] group-hover:text-blue-700 transition-colors truncate">{{ $fullName }}</span>
                                <span class="text-xs text-slate-500 truncate mt-0.5 font-medium">{{ $user->email ?? 'No email' }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- ID NUMBER -->
                    <td x-show="cols['id_number'] !== false" class="px-6 py-4 align-middle">
                        <span class="text-xs font-medium text-slate-700">{{ $idNumber }}</span>
                    </td>

                    <!-- ACCOUNT TYPE -->
                    <td x-show="cols['role'] !== false" class="px-6 py-4 align-middle">
                        @if($user->role?->name === 'Member')
                            <span class="inline-flex items-center rounded-md border border-[#BFDBFE] bg-[#DBEAFE] px-2.5 py-0.5 text-xs font-semibold text-[#1D4ED8]">
                                Student
                            </span>
                        @elseif($user->role?->name === 'Librarian')
                            <span class="inline-flex items-center rounded-md border border-[#FDE68A] bg-[#FEF3C7] px-2.5 py-0.5 text-xs font-semibold text-[#B45309]">
                                Librarian
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                {{ $user->role?->name ?? 'Unknown' }}
                            </span>
                        @endif
                    </td>

                    <!-- STATUS -->
                    <td x-show="cols['status'] !== false" class="px-6 py-4 align-middle">
                        @php
                            $statusName = $user->status?->status_name ?? 'Unknown';
                            $statusClass = match(strtolower($statusName)) {
                                'active' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                'locked', 'suspended' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-md border {{ $statusClass }} px-2.5 py-0.5 text-xs font-semibold">
                            {{ $statusName }}
                        </span>
                    </td>

                    <!-- LAST LOGIN -->
                    <td x-show="cols['last_login'] !== false" class="px-6 py-4 align-middle">
                        @if($user->last_login)
                            <span class="text-xs text-slate-600 font-medium" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                                {{ $user->last_login->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 font-medium">Never</span>
                        @endif
                    </td>

                    <!-- ACTIONS -->
                    <td class="px-6 py-4 align-middle text-right pr-6">
                        <div class="flex items-center justify-end gap-3.5">
                            <button class="text-xs font-bold text-[#102B70] hover:text-[#0B225E] transition-colors">
                                View
                            </button>
                            <button class="text-xs font-bold text-slate-600 hover:text-[#102B70] transition-colors">
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm md:text-base font-semibold">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                            <p class="text-base font-bold text-slate-700 mb-0.5">No users found</p>
                            <p class="text-sm text-slate-500">Try changing the search term or selected filter.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </div>
</div>
