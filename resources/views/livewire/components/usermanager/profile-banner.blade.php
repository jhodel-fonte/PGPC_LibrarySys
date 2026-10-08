<section class="relative isolate overflow-hidden rounded-2xl bg-[#102B70] text-white shadow-lg shadow-blue-950/10">
    <div class="absolute inset-y-0 right-0 hidden w-[42%] overflow-hidden lg:block" aria-hidden="true">
        <img src="{{ asset('images/school-img.webp') }}" alt="" class="h-full w-full object-cover object-center opacity-25">
        <div class="absolute inset-0 bg-gradient-to-r from-[#102B70] via-[#102B70]/70 to-[#102B70]/20"></div>
    </div>
    <div class="absolute -bottom-24 -left-16 h-56 w-56 rounded-full bg-blue-400/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col gap-5 p-4 sm:gap-6 sm:p-6 lg:flex-row lg:items-center lg:justify-between lg:p-7">
        <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
            <div class="relative shrink-0 self-start sm:self-center">
                <div class="flex h-18 w-18 select-none items-center justify-center rounded-full border-4 border-white/90 bg-white/10 text-xl font-black text-[#FCC719] shadow-xl shadow-blue-950/25 sm:h-22 sm:w-22 sm:text-2xl lg:h-24 lg:w-24 lg:text-3xl">
                    {{ $initials ?: strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="break-words text-lg font-extrabold tracking-tight text-white sm:text-2xl">{{ $fullName }}</h2>

                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 sm:mt-2 sm:gap-2">
                    @if($roleName === 'Member' || $isStudent)
                        <span class="inline-flex items-center rounded-md bg-white/12 px-2.5 py-0.5 text-[11px] font-bold text-blue-50 ring-1 ring-inset ring-white/20 sm:py-1">
                            Student
                        </span>
                    @elseif($roleName === 'Librarian' || $isLibrarian)
                        <span class="inline-flex items-center rounded-md bg-white/12 px-2.5 py-0.5 text-[11px] font-bold text-blue-50 ring-1 ring-inset ring-white/20 sm:py-1">
                            Librarian
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-white/12 px-2.5 py-0.5 text-[11px] font-bold text-blue-50 ring-1 ring-inset ring-white/20 sm:py-1">
                            {{ $roleName }}
                        </span>
                    @endif

                    @if($statusLower === 'active')
                        <span class="inline-flex items-center rounded-md bg-emerald-400/15 px-2.5 py-0.5 text-[11px] font-bold text-emerald-100 ring-1 ring-inset ring-emerald-300/30 sm:py-1">
                            Active
                        </span>
                    @elseif($statusLower === 'suspended' || $statusLower === 'locked')
                        <span class="inline-flex items-center rounded-md bg-red-400/15 px-2.5 py-0.5 text-[11px] font-bold text-red-100 ring-1 ring-inset ring-red-300/30 sm:py-1">
                            Suspended
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-amber-400/15 px-2.5 py-0.5 text-[11px] font-bold text-amber-100 ring-1 ring-inset ring-amber-300/30 sm:py-1">
                            {{ $statusName }}
                        </span>
                    @endif
                </div>

                <div class="mt-3 grid gap-x-6 gap-y-1.5 text-xs font-medium text-blue-100 sm:mt-4 sm:gap-y-2 sm:grid-cols-2 xl:grid-cols-3">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                        <span class="truncate">ID: {{ $schoolId }}</span>
                    </span>
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <span class="w-4 shrink-0 text-center font-black text-blue-300">@</span>
                        <span class="truncate">{{ $user->username ?? 'N/A' }}</span>
                    </span>
                    <span class="inline-flex min-w-0 items-center gap-2 sm:col-span-2 xl:col-span-1" title="{{ $user->email ?? 'No email' }}">
                        <svg class="h-4 w-4 shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span class="truncate">{{ $user->email ?? 'No email' }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- <div class="grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-white/15 ring-1 ring-white/15 w-full sm:w-fit lg:min-w-[270px]">
            <div class="bg-[#0D2664]/80 px-3.5 py-3 sm:px-4 sm:py-3.5">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-200">Account created</p>
                <p class="mt-1 text-xs sm:text-sm font-bold text-white">{{ $user?->created_at ? $user->created_at->format('M d, Y') : 'Not available' }}</p>
            </div>
            <div class="bg-[#0D2664]/80 px-3.5 py-3 sm:px-4 sm:py-3.5">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-200">Last login</p>
                <p class="mt-1 text-xs sm:text-sm font-bold text-white">{{ $user?->last_login ? $user->last_login->diffForHumans() : 'Never' }}</p>
            </div>
        </div> --}}
    </div>
</section>
