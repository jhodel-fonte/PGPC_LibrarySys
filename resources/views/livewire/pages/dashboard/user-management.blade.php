<div class="bg-[#F8FAFC] lg:h-full lg:flex lg:flex-col lg:min-h-0">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-6 lg:h-full lg:min-h-0 lg:flex-1">
        
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#17357A]">User Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage members, librarians, and login accounts.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 h-10 px-4 rounded-lg bg-[#17357A] hover:bg-[#122D68] text-white text-sm font-semibold transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    Add Student
                </button>
                <button class="flex items-center gap-2 h-10 px-4 rounded-lg border border-[#17357A] bg-white text-[#17357A] hover:bg-blue-50 text-sm font-semibold transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    Add Librarian
                </button>
            </div>
        </div>

        <!-- 2. Statistics Cards -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 lg:shrink-0">
            <!-- Total Members -->
            <div class="min-h-[104px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Students</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalStudents) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#EFF6FF] text-[#2563EB]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>

            <!-- Total Librarians -->
            <div class="min-h-[104px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Librarians</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalLibrarians) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#FFFBEB] text-[#D97706]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>

            <!-- Active Accounts -->
            <div class="min-h-[104px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Active Accounts</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($activeAccounts) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#ECFDF5] text-[#059669]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>

            <!-- Locked/Suspended -->
            <div class="min-h-[104px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Locked/Suspended</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($lockedAccounts) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#FEF2F2] text-[#DC2626]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
            </div>
        </div>

        <!-- 3. Table Card -->
        <div class="relative z-10 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col lg:flex-1 lg:min-h-0">
            
            <!-- Toolbar -->
            <div class="border-b border-slate-200 bg-white px-4 py-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between lg:shrink-0">
                
                <!-- Tabs -->
                <div class="flex items-center gap-1">
                    @foreach(['All Users', 'Students', 'Librarians'] as $tab)
                        <button 
                            wire:click="setTab('{{ $tab }}')"
                            class="px-3.5 h-9 rounded-lg text-sm transition-colors {{ $activeTab === $tab ? 'bg-[#17357A] text-white font-semibold' : 'text-slate-600 hover:text-[#17357A] hover:bg-slate-50' }}"
                        >
                            {{ $tab }}
                        </button>
                    @endforeach
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-[340px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Search users..." 
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm focus:border-[#17357A] focus:ring-2 focus:ring-blue-100 transition-shadow outline-none"
                    >
                </div>
            </div>

            <!-- Table Wrapper (Horizontal scroll only) -->
            <!-- Table Wrapper (Horizontal and Vertical scroll) -->
            <div class="overflow-x-auto overflow-y-hidden w-full relative flex-1 min-h-0 lg:overflow-y-auto">
                <!-- Skeletal Loading Overlay for Search and Sorting -->
                <div wire:loading.delay wire:target="search, sortBy, setTab, previousPage, nextPage, gotoPage" class="absolute inset-0 z-20 bg-white/50 backdrop-blur-[1px] flex items-start justify-center pt-10 rounded-b-2xl">
                    <div class="flex items-center gap-2 text-[#17357A] bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium">Loading...</span>
                    </div>
                </div>

                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="sticky top-0 z-10 bg-[#F8FAFC] shadow-[0_1px_0_0_#e2e8f0]">
                        <tr>
                            <th wire:click="sortBy('user')" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[30%] cursor-pointer hover:bg-slate-100 transition-colors group select-none">
                                <div class="flex items-center gap-1">
                                    User
                                    @if($sort['column'] === 'user')
                                        <svg class="h-3.5 w-3.5 text-[#17357A] transition-transform {{ $sort['direction'] === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[12%]">ID Number</th>
                            <th wire:click="sortBy('role')" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[14%] cursor-pointer hover:bg-slate-100 transition-colors group select-none">
                                <div class="flex items-center gap-1">
                                    Account Type
                                    @if($sort['column'] === 'role')
                                        <svg class="h-3.5 w-3.5 text-[#17357A] transition-transform {{ $sort['direction'] === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('status')" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[13%] cursor-pointer hover:bg-slate-100 transition-colors group select-none">
                                <div class="flex items-center gap-1">
                                    Status
                                    @if($sort['column'] === 'status')
                                        <svg class="h-3.5 w-3.5 text-[#17357A] transition-transform {{ $sort['direction'] === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('last_login')" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[18%] cursor-pointer hover:bg-slate-100 transition-colors group select-none">
                                <div class="flex items-center gap-1">
                                    Last Login
                                    @if($sort['column'] === 'last_login')
                                        <svg class="h-3.5 w-3.5 text-[#17357A] transition-transform {{ $sort['direction'] === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 w-[13%] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $user)
                            @php
                                // Resolve personal details safely
                                $person = $user->role?->name === 'Librarian' ? $user->librarian : $user->student;
                                $fullName = $person ? "{$person->first_name} {$person->last_name}" : $user->username;
                                $idNumber = $person ? $person->school_id_number : '—';
                                
                                // Generate initials
                                $initials = collect(explode(' ', $fullName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors h-[64px] group">
                                <!-- USER -->
                                <td class="px-5 py-2 cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-[#E8EEFC] text-[#17357A] flex items-center justify-center shrink-0">
                                            <span class="text-sm font-bold">{{ strtoupper($initials) }}</span>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-semibold text-[#17357A] group-hover:text-blue-700 transition-colors truncate">{{ $fullName }}</span>
                                            <span class="text-xs text-slate-500 truncate mt-0.5">{{ $user->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- ID NUMBER -->
                                <td class="px-5 py-2 cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                                    <span class="text-sm text-slate-700">{{ $idNumber }}</span>
                                </td>

                                <!-- ACCOUNT TYPE -->
                                <td class="px-5 py-2 cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                                    @if($user->role?->name === 'Member')
                                        <span class="inline-flex items-center rounded-md border border-[#BFDBFE] bg-[#DBEAFE] px-2.5 py-1 text-xs font-medium text-[#1D4ED8]">
                                            Student
                                        </span>
                                    @elseif($user->role?->name === 'Librarian')
                                        <span class="inline-flex items-center rounded-md border border-[#FDE68A] bg-[#FEF3C7] px-2.5 py-1 text-xs font-medium text-[#B45309]">
                                            Librarian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            {{ $user->role?->name ?? 'Unknown' }}
                                        </span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td class="px-5 py-2 cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                                    @php
                                        $statusName = $user->status?->status_name ?? 'Unknown';
                                        $statusClass = match(strtolower($statusName)) {
                                            'active' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                            'locked', 'suspended' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                            default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md border {{ $statusClass }} px-2.5 py-1 text-xs font-medium">
                                        {{ $statusName }}
                                    </span>
                                </td>

                                <!-- LAST LOGIN -->
                                <td class="px-5 py-2 cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                                    @if($user->last_login)
                                        <span class="text-sm text-slate-600" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                                            {{ $user->last_login->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">Never</span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="px-5 py-2 text-right">
                                    <div class="flex items-center justify-end gap-4">
                                        <button class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            View
                                        </button>
                                        <button class="text-sm font-medium text-slate-600 hover:text-[#17357A] transition-colors">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 opacity-50"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                                        <p class="text-base font-medium text-slate-600 mb-1">No users found</p>
                                        <p class="text-sm">Try changing the search term or selected filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Toolbar -->
            <footer class="border-t border-slate-200 bg-white lg:shrink-0">
                <div class="flex items-center justify-between px-4 py-3 min-h-[56px]">
                    <span class="text-sm text-slate-600">
                        @if($users->total() > 0)
                            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} results
                        @else
                            Showing 0 results
                        @endif
                    </span>
                    <div>
                        {{ $users->links('components.pagination', data: ['scrollTo' => false]) }}
                    </div>
                </div>
            </footer>
        </div>
    </div>
</div>
