@php
    $user = $user ?? null;
    $student = $student ?? $user?->student;
    $fullName = $fullName ?? ($student ? implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name])) : ($user?->username ?? 'N/A'));
    $initials = $initials ?? ($fullName ? collect(explode(' ', $fullName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') : strtoupper(substr($user?->username ?? 'U', 0, 1)));
    $schoolId = $schoolId ?? ($student?->school_id_number ?? 'Not assigned');
    $contactNum = $contactNum ?? ($student?->contact_num ?? 'Not provided');
    $dobFormatted = $formattedDob ?? ($student?->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'Not specified');
    $gender = $gender ?? ($student?->gender ? ucfirst(strtolower($student->gender)) : 'Not specified');
@endphp

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
                        {{ $student?->first_name ?: '—' }}
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
                        {{ $student?->middle_name ?: '—' }}
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
                        {{ $student?->last_name ?: '—' }}
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

            <!-- Date Of Birth -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Date of Birth</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center">
                        {{ $dobFormatted }}
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
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-[#334155]">Email Address</label>
                </div>
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

    <!-- SECTION 2: ACADEMIC & ENROLLMENT DETAILS -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
            <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
            <h3 class="text-base font-bold text-[#102B70]">Academic & Enrollment Details</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
            
            <!-- School ID Number -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">School ID Number</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.374a4.125 4.125 0 0 0-6.338 0 .375.375 0 0 0 .285.626h5.768a.375.375 0 0 0 .285-.626Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-bold text-[#102B70] font-mono flex items-center">
                        {{ $schoolId }}
                    </div>
                </div>
            </div>

            <!-- Program -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Degree Program</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $student?->program ?: 'Not assigned' }}
                    </div>
                </div>
            </div>

            <!-- Year Level -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Year Level</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center">
                        {{ $student?->year_level ?: 'Not assigned' }}
                    </div>
                </div>
            </div>

            <!-- Enrollment Year -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Enrollment Year</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center">
                        {{ $student?->enrollment_year ?: 'Current Year' }}
                    </div>
                </div>
            </div>

            <!-- Enrollment Status -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Enrollment Status</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center">
                        <span class="inline-flex items-center gap-1.5 text-base font-bold text-emerald-700 px-2.5 py-1 rounded-xl">
                            {{ $student?->enrollment_status ?: 'Active Enrolled' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SECTION 4: ACCOUNT CREDENTIALS & SECURITY -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
            <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
            <h3 class="text-base font-bold text-[#102B70]">Account Credentials & Security Details</h3>
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

            <!-- Last Login -->
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

            <!-- Account Created -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#334155]">Account Registered</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-base font-semibold text-[#0F172A] flex items-center truncate">
                        {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Date unavailable' }}
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
