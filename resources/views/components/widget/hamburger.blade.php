    <!-- Left: Mobile Menu, Logo & Title, Breadcrumbs on Desktop -->
    <div class="flex items-center gap-2.5 text-sm font-medium text-white/70 lg:text-gray-500 min-w-0">
        <!-- Logo & Title for Mobile/Tablet -->
        <div class="flex lg:hidden items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white">
                <img src="{{ asset('images/logo.webp') }}" alt="PGPC Logo" class="h-full w-full object-cover">
            </div>
            <div class="text-left">
                <p class="text-xs font-bold leading-tight text-white">{{ config('settings.acronym') }} Library</p>
                <!-- NOTE: Change this to Admin Portal if this is for the Admin -->
                <p class="text-[9px] font-semibold uppercase tracking-wider text-white/50 leading-none">Student Portal</p>
            </div>
        </div>

        <!-- Breadcrumbs for Desktop -->
        <div class="hidden lg:block">
            <!-- NOTE: Change this to Admin if this is for the Admin -->
            <span class="text-gray-500">Student</span>
            <span class="mx-1 text-gray-400">&gt;</span>
            {{-- <span class="text-gray-800 font-bold capitalize">{{ $section }}</span> --}}
        </div>
    </div>