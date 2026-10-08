<section class="space-y-4 sm:space-y-5 rounded-2xl bg-white p-4 sm:p-6 shadow-sm shadow-slate-200/60 ring-1 ring-slate-200 w-full">
    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
        <div class="text-[#102B70]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Personal Information</h3>
    </div>

    <div class="divide-y divide-slate-100 text-sm font-medium">
        <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Full Name
            </span>
            <span class="break-words text-sm font-bold text-slate-800 sm:text-right">{{ $fullName }}</span>
        </div>

        <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                Email Address
            </span>
            <div class="flex min-w-0 flex-wrap items-center gap-2 sm:justify-end">
                <span class="break-all text-sm font-semibold text-slate-800">{{ $user->email ?? 'Not provided' }}</span>
                @if($user?->is_email_verified)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        Verified
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                        Unverified
                    </span>
                @endif
            </div>
        </div>

        <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                Contact Number
            </span>
            <span class="text-sm font-semibold text-slate-800 sm:text-right">{{ $contactNum }}</span>
        </div>

        <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                {{ $isLibrarian ? 'Employee ID' : 'Student ID' }}
            </span>
            <span class="text-sm font-semibold text-slate-800 sm:text-right">{{ $schoolId }}</span>
        </div>

        <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
            <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                <span class="font-bold text-slate-400 w-4 text-center">@</span>
                Username
            </span>
            <span class="break-all text-sm font-semibold text-slate-800 sm:text-right">{{ $user->username ?? 'N/A' }}</span>
        </div>

        @if($isStudent)
            <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
                <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 13.5C18 16.538 15.314 19 12 19s-6-2.462-6-5.5c0-1.032.31-1.998.84-2.922L12 14z" /></svg>
                    Program / Course
                </span>
                <span class="break-words text-sm font-semibold text-slate-800 sm:text-right">{{ $user?->student?->program ?: 'Not provided' }}</span>
            </div>

            <div class="grid gap-1 py-2.5 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] sm:items-center sm:gap-4 sm:py-3">
                <span class="inline-flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Year Level
                </span>
                <span class="text-sm font-semibold text-slate-800 sm:text-right">{{ $user?->student?->year_level ?: 'Not provided' }}</span>
            </div>
        @endif
    </div>
</section>
