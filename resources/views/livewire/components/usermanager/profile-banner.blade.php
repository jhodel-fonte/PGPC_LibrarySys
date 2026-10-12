@php
    $user = $user ?? null;
    $student = $student ?? $user?->student;
    $librarian = $librarian ?? $user?->librarian;
    $person = $student ?? $librarian;
    $fullName = $fullName ?? 'N/A';
    $statusName = $user?->status?->status_name ?? 'Active';
    $statusLower = strtolower($statusName);
    $schoolId = $person?->school_id_number ?? 'Not assigned';
    $contactNum = $person?->contact_num ?? 'Not provided';
    $gender = strtolower($person?->gender ?? '');
    $defaultKey = $gender === 'female' ? 'female' : 'male';
    $defaultImage = asset(config("settings.profile_image_dir.default.{$defaultKey}", "images/default-{$defaultKey}.webp"));
    $profileImage = $person?->image_url ?: $defaultImage;
@endphp

<section class="relative isolate overflow-hidden rounded-3xl bg-[#102B70] text-white shadow-lg shadow-blue-950/15">
    <!-- School Building Background Image & Gradient Overlays -->
    <div class="absolute inset-y-0 right-0 hidden w-[45%] overflow-hidden lg:block pointer-events-none" aria-hidden="true">
        <img src="{{ asset('images/school-img.webp') }}" alt="" class="h-full w-full object-cover object-center opacity-25">
        <div class="absolute inset-0 bg-gradient-to-r from-[#102B70] via-[#102B70]/80 to-[#102B70]/20"></div>
    </div>
    <div class="absolute -bottom-24 -left-16 h-56 w-56 rounded-full bg-blue-400/10 blur-3xl pointer-events-none" aria-hidden="true"></div>

    <div class="relative z-10 p-3 sm:p-5 lg:p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-6 items-center">
            
            <!-- Left: Profile Photo & Basic Identity Info (xl:col-span-5) -->
            <div class="xl:col-span-4 flex items-center gap-4 sm:gap-5 min-w-0">
                <div class="relative shrink-0">
                    <img
                        src="{{ $profileImage }}"
                        alt="{{ $fullName }}"
                        class="h-16 w-16 sm:h-20 sm:w-20 rounded-full object-cover border-2 border-white/80 shadow-md bg-white/10"
                        onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                    >
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-xl font-bold text-white truncate">
                        {{ $fullName }}
                    </h2>

                    <div class="space-y-1">
                        <!-- School ID (larger font size) -->
                        <div class="flex items-center gap-2 text-sm sm:text-base font-semibold text-blue-100">
                            <svg class="w-4 h-4 text-[#FCC719] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.374a4.125 4.125 0 0 0-6.338 0 .375.375 0 0 0 .285.626h5.768a.375.375 0 0 0 .285-.626Z" />
                            </svg>
                            <span class="font-mono text-white font-bold text-sm sm:text-base">{{ $schoolId }}</span>
                        </div>

                        <!-- Email & Contact -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-blue-200/90 font-medium">
                            <div class="flex items-center gap-1.5 truncate">
                                <svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                <span class="truncate">{{ $user->email ?? 'test@test.com' }}</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                                <span>{{ $contactNum }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-7 xl:border-l xl:border-white/15 xl:pl-6 space-y-1">
                @if($student)
                    <div class="space-y-0.5 min-w-0">
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-blue-200 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 text-[#FCC719] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                            </svg>
                            <span>Program</span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-white truncate" title="{{ $student?->program ?: 'Not assigned' }}">
                            {{ $student?->program ?: 'Not assigned' }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-blue-200 uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 text-[#FCC719] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                            </svg>
                            <span>Year Level</span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-white">
                            {{ $student?->year_level ?: '2nd Year' }}
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
