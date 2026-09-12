<div class="bg-[#F8FAFC] lg:h-full lg:flex lg:flex-col lg:min-h-0">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-6 lg:h-full lg:min-h-0 lg:flex-1">
        
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">User Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage members, librarians, and login accounts.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 h-11 px-5 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    Add Student
                </button>
                <button class="flex items-center gap-2 h-11 px-5 rounded-2xl border border-[#102B70] bg-white text-[#102B70] hover:bg-slate-50 text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    Add Librarian
                </button>
            </div>
        </div>

        <!-- 2. Statistics Cards -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 lg:shrink-0">
            <!-- Total Members -->
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Total Students</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#0F172A]">{{ number_format($totalStudents) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#EFF6FF] text-[#2563EB] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>

            <!-- Total Librarians -->
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Total Librarians</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#0F172A]">{{ number_format($totalLibrarians) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#FFFBEB] text-[#D97706] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>

            <!-- Active Accounts -->
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Active Accounts</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-emerald-700">{{ number_format($activeAccounts) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#ECFDF5] text-[#059669] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>

            <!-- Locked/Suspended -->
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Locked / Suspended</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-red-700">{{ number_format($lockedAccounts) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#FEF2F2] text-[#DC2626] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
            </div>
        </div>

        <!-- 3. Table Card -->
        <x-data-table
            :headers="$this->headers"
            :sort="$sort"
            :tabs="['All Users', 'Students', 'Librarians']"
            :activeTab="$activeTab"
            searchPlaceholder="Search users..."
            :paginator="$users"
            minWidth="950px"
        >
            @forelse($users as $user)
                @php
                    // Resolve personal details safely
                    $person = $user->role?->name === 'Librarian' ? $user->librarian : $user->student;
                    $fullName = $person ? "{$person->first_name} {$person->last_name}" : $user->username;
                    $idNumber = $person ? $person->school_id_number : '—';
                    
                    // Generate initials
                    $initials = collect(explode(' ', $fullName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors h-[72px] group">
                    <!-- USER -->
                    <td class="px-6 py-4 align-middle cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                        <div class="flex items-center gap-3.5">
                            <div class="h-10 w-10 rounded-full bg-[#E8EEFC] text-[#102B70] flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold">{{ strtoupper($initials) }}</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-[#102B70] group-hover:text-blue-700 transition-colors truncate">{{ $fullName }}</span>
                                <span class="text-xs text-slate-500 truncate mt-0.5 font-medium">{{ $user->email ?? 'No email' }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- ID NUMBER -->
                    <td class="px-6 py-4 align-middle cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                        <span class="text-sm text-slate-700 font-semibold">{{ $idNumber }}</span>
                    </td>

                    <!-- ACCOUNT TYPE -->
                    <td class="px-6 py-4 align-middle cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                        @if($user->role?->name === 'Member')
                            <span class="inline-flex items-center rounded-lg border border-[#BFDBFE] bg-[#DBEAFE] px-2.5 py-1 text-xs font-bold text-[#1D4ED8]">
                                Student
                            </span>
                        @elseif($user->role?->name === 'Librarian')
                            <span class="inline-flex items-center rounded-lg border border-[#FDE68A] bg-[#FEF3C7] px-2.5 py-1 text-xs font-bold text-[#B45309]">
                                Librarian
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                {{ $user->role?->name ?? 'Unknown' }}
                            </span>
                        @endif
                    </td>

                    <!-- STATUS -->
                    <td class="px-6 py-4 align-middle cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                        @php
                            $statusName = $user->status?->status_name ?? 'Unknown';
                            $statusClass = match(strtolower($statusName)) {
                                'active' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                'locked', 'suspended' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-lg border {{ $statusClass }} px-2.5 py-1 text-xs font-bold">
                            {{ $statusName }}
                        </span>
                    </td>

                    <!-- LAST LOGIN -->
                    <td class="px-6 py-4 align-middle cursor-pointer" onclick="alert('Navigate to user: {{ $user->id }}')">
                        @if($user->last_login)
                            <span class="text-xs md:text-sm text-slate-600 font-medium" title="{{ $user->last_login->format('M d, Y h:i A') }}">
                                {{ $user->last_login->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 font-medium">Never</span>
                        @endif
                    </td>

                    <!-- ACTIONS -->
                    <td class="px-6 py-4 align-middle text-right">
                        <div class="flex items-center justify-end gap-3.5">
                            <button class="text-xs font-bold text-[#102B70] hover:text-blue-700 transition-colors uppercase tracking-wider">
                                View
                            </button>
                            <button class="text-xs font-bold text-slate-600 hover:text-[#102B70] transition-colors uppercase tracking-wider">
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
