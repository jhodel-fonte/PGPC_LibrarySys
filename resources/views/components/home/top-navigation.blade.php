@props([
    'active' => 'home',
])

@php
    $signedInUser = auth()->user();
    $roleName = strtolower(str_replace(' ', '', $signedInUser?->role?->name ?? ''));

    $dashboardUrl = match (true) {
        in_array($roleName, ['admin', 'headlibrarian', 'librarian'], true) => Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard'),
        in_array($roleName, ['student', 'member'], true) => Route::has('student.dashboard') ? route('student.dashboard') : url('/'),
        default => Route::has('admin.dashboard') ? route('admin.dashboard') : url('/'),
    };

    // Load Online Resources sublinks from list.json
    $onlineResourceSublinks = [];

    try {
        $resourcesPath = storage_path('app/public/online_resources/list.json');
        if (file_exists($resourcesPath)) {
            $content = file_get_contents($resourcesPath);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                $resources = $decoded['online_resources'] ?? [];
                foreach ($resources as $res) {
                    $onlineResourceSublinks[] = [
                        'name' => $res['name'] ?? '',
                        'url' => $res['external_url'] ?? '#',
                        'desc' => 'Academic journal & database',
                    ];
                }
            }
        }
    } catch (\Throwable $e) {
        $onlineResourceSublinks = [];
    }

    if (empty($onlineResourceSublinks)) {
        $onlineResourceSublinks = [
            ['name' => 'ScienceDirect', 'url' => 'https://www.sciencedirect.com', 'desc' => 'Academic journal & database'],
            ['name' => 'Emerald Insight', 'url' => 'https://www.emerald.com', 'desc' => 'Academic journal & database'],
            ['name' => 'SpringerLink', 'url' => 'https://link.springer.com/', 'desc' => 'Academic journal & database'],
            ['name' => 'JSTOR', 'url' => 'https://www.jstor.org/', 'desc' => 'Academic journal & database'],
            ['name' => 'Wiley Online Library', 'url' => 'https://onlinelibrary.wiley.com/', 'desc' => 'Academic journal & database'],
            ['name' => 'IEEEXplore', 'url' => 'https://ieeexplore.ieee.org/', 'desc' => 'Academic journal & database'],
        ];
    }

    $navLinks = [
        ['name' => 'About', 'url' => url('/#about'), 'key' => 'about'],
        ['name' => 'OPAC', 'url' => Route::has('opac.index') ? route('opac.index') : url('/#opac'), 'key' => 'opac'],
        [
            'name' => 'Online Resources',
            'url' => url('/#online-resources'),
            'key' => 'online-resources',
            'sublinks' => $onlineResourceSublinks,
        ],
        ['name' => 'Services', 'url' => url('/#services'), 'key' => 'services'],
        ['name' => 'Contact', 'url' => url('/#contact'), 'key' => 'contact'],
    ];
@endphp

<header
    x-data="{
        mobileMenuOpen: false,
        isScrolled: false,
        updateScroll() {
            const y = window.pageYOffset || document.documentElement.scrollTop;
            if (y > 55) {
                this.isScrolled = true;
            } else if (y < 20) {
                this.isScrolled = false;
            }
        }
    }"
    x-init="updateScroll()"
    @scroll.window.passive="updateScroll()"
    @keydown.escape.window="mobileMenuOpen = false"
    class="sticky top-0 z-50 w-full transition-all duration-300 ease-in-out select-none border-none"
    :class="{
        'shadow-xl shadow-black/40 bg-[#091b45]': isScrolled || mobileMenuOpen,
        'bg-transparent': !isScrolled && !mobileMenuOpen && ('{{ $active }}' === 'home'),
        'bg-[#091b45]': !isScrolled && !mobileMenuOpen && ('{{ $active }}' !== 'home')
    }"
    :style="(isScrolled || mobileMenuOpen) ? 'background-color: #091b45 !important;' : ''"
>
    <!-- Main Top Bar Container (Responsive height) -->
    <div
        class="relative z-10 mx-auto flex max-w-[1380px] items-center justify-between gap-3 sm:gap-4 px-4 sm:px-6 lg:px-8 transition-[height] duration-300 ease-in-out"
        :class="isScrolled ? 'h-[68px] sm:h-[76px]' : 'h-[74px] sm:h-[96px] lg:h-[100px]'"
    >

        <!-- Left: Seal & Institutional Name -->
        <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="group flex shrink-0 items-center gap-2.5 sm:gap-3.5 focus:outline-none" aria-label="Padre Garcia Polytechnic College Library Home">
            <div
                class="relative flex shrink-0 items-center justify-center rounded-full bg-white/10 p-0.5 shadow-sm transition-all duration-300 group-hover:scale-105 overflow-hidden"
                :class="isScrolled ? 'h-[38px] w-[38px] sm:h-[42px] sm:w-[42px]' : 'h-[44px] w-[44px] sm:h-[52px] sm:w-[52px] lg:h-[56px] lg:w-[56px]'">

                <img src="{{ asset('logo.webp') }}" alt="PGPC Logo" class="h-full w-full max-h-full max-w-full object-contain rounded-full" onerror="this.src='{{ asset('images/logo.webp') }}'">

            </div>
            <div class="flex flex-col shrink-0 transition-all duration-300">
                <span
                    class="font-bold leading-tight text-white tracking-tight whitespace-nowrap transition-all duration-300 group-hover:text-slate-100 text-[13.5px] sm:text-[15.5px] lg:text-[17px]">
                    Padre Garcia Polytechnic College
                </span>
                <span
                    class="mt-0.5 font-bold uppercase tracking-[0.2em] text-[#FCC719] leading-none whitespace-nowrap transition-all duration-300 text-[10px] sm:text-[11px] lg:text-[12px]">
                    Library System
                </span>
            </div>
        </a>

        <!-- Center / Right: Desktop Navigation Links -->
        <nav class="hidden lg:flex shrink-0 items-center gap-5 xl:gap-8" aria-label="Main Navigation">
            @foreach ($navLinks as $link)
                @php
                    $isActive = ($active === $link['key']);
                @endphp

                @if (!empty($link['sublinks']))
                    <x-home.navbar-sublink
                        :title="$link['name']"
                        :url="$link['url']"
                        :active="$isActive"
                        :items="$link['sublinks']"
                    />
                @else
                    <a
                        href="{{ $link['url'] }}"
                        class="group relative flex flex-col items-center justify-center py-2 text-[16px] whitespace-nowrap transition-colors focus:outline-none {{ $isActive ? 'text-white font-semibold' : 'text-slate-200/90 hover:text-white font-medium' }}"
                    >
                        <span>{{ $link['name'] }}</span>
                        @if ($isActive)
                            <!-- Gold Active Indicator Bar matching design -->
                            <span class="absolute -bottom-1.5 left-0 right-0 h-[3px] rounded-full bg-[#FCC719]"></span>
                        @else
                            <!-- Subtle Hover Indicator Bar -->
                            <span class="absolute -bottom-1.5 left-1/2 h-[3px] w-0 -translate-x-1/2 rounded-full bg-[#FCC719] transition-all duration-200 group-hover:w-full"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        <!-- Right: Action Buttons (Log in & Register / Dashboard) -->
        <div class="hidden lg:flex shrink-0 items-center gap-3">
            @if ($signedInUser)
                <!-- Authenticated State -->
                <a
                    href="{{ $dashboardUrl }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#FCC719] px-5 py-2 text-[13.5px] font-bold text-[#071943] shadow-sm transition-all duration-200 hover:bg-[#ffd84c] hover:shadow-md active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#FCC719]"
                >
                    Dashboard
                </a>
                <form action="{{ Route::has('logout') ? route('logout') : url('/logout') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full border border-white/25 px-4 py-2 text-[13.5px] font-semibold text-white transition-all duration-200 hover:bg-white/10 hover:border-white/50 focus:outline-none focus:ring-2 focus:ring-white/30 cursor-pointer"
                    >
                        Log out
                    </button>
                </form>
            @else
                <!-- Guest State: Log in (Outlined) & Register (Gold Filled) -->
                <a
                    href="{{ Route::has('login') ? route('login') : url('/login') }}"
                    class="inline-flex items-center justify-center rounded-full border border-white/30 px-5 py-2 text-[13.5px] font-semibold text-white transition-all duration-200 hover:bg-white/10 hover:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/30"
                >
                    Log in
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : url('/register') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#FCC719] px-5 py-2 text-[13.5px] font-bold text-[#071943] shadow-sm transition-all duration-200 hover:bg-[#ffd84c] hover:shadow-md hover:shadow-yellow-500/20 active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#FCC719]"
                >
                    Register
                </a>
            @endif
        </div>

        <!-- Mobile / Tablet Hamburger Button -->
        <div class="flex items-center lg:hidden">
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="inline-flex items-center justify-center rounded-xl p-2 text-slate-200 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/30"
                aria-label="Toggle navigation menu"
                :aria-expanded="mobileMenuOpen"
            >
                <!-- Hamburger Icon -->
                <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <!-- Close Icon -->
                <svg x-show="mobileMenuOpen" style="display: none;" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Panel -->
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        @click.outside="mobileMenuOpen = false"
        style="display: none; background-color: #091b45 !important;"
        class="lg:hidden w-full border-t border-white/15 bg-[#091b45] px-4 sm:px-6 py-5 shadow-2xl select-none"
    >
        <nav class="flex flex-col space-y-1">
            @foreach ($navLinks as $link)
                @php
                    $isActive = ($active === $link['key']);
                @endphp

                @if (!empty($link['sublinks']))
                    <div x-data="{ subOpen: false }" class="flex flex-col">
                        <button
                            type="button"
                            @click="subOpen = !subOpen"
                            class="flex items-center justify-between rounded-xl px-4 py-2.5 text-[15.5px] font-semibold transition-colors text-white hover:bg-white/10"
                        >
                            <span>{{ $link['name'] }}</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="subOpen ? 'rotate-180 text-[#FCC719]' : 'text-slate-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="subOpen" style="display: none; background-color: #061332 !important;" class="pl-4 pr-3 py-2 space-y-1 bg-[#061332] rounded-xl my-1 border border-white/10">
                            @foreach ($link['sublinks'] as $sub)
                                @php
                                    $subUrl = $sub['url'] ?? '#';
                                    $isSubExternal = str_starts_with($subUrl, 'http://') || str_starts_with($subUrl, 'https://');
                                @endphp
                                <a
                                    href="{{ $subUrl }}"
                                    @if ($isSubExternal) target="_blank" rel="noopener noreferrer" @endif
                                    @click="mobileMenuOpen = false"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 text-[14px] font-medium text-slate-200 hover:bg-white/10 hover:text-[#FCC719] transition-colors"
                                >
                                    <span>{{ $sub['name'] }}</span>
                                    @if ($isSubExternal)
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $link['url'] }}"
                        @click="mobileMenuOpen = false"
                        class="flex items-center justify-between rounded-xl px-4 py-2.5 text-[15.5px] font-semibold transition-colors {{ $isActive ? 'bg-white/15 text-white font-bold' : 'text-slate-100 hover:bg-white/10 hover:text-white' }}"
                    >
                        <span>{{ $link['name'] }}</span>
                        @if ($isActive)
                            <span class="h-2 w-2 rounded-full bg-[#FCC719]"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        <!-- Mobile Action Buttons -->
        <div class="mt-5 border-t border-white/10 pt-4 flex flex-col gap-2.5">
            @if ($signedInUser)
                <a
                    href="{{ $dashboardUrl }}"
                    class="flex h-11 w-full items-center justify-center rounded-full bg-[#FCC719] text-[14px] font-bold text-[#071943] transition hover:bg-[#ffd84c]"
                >
                    Dashboard
                </a>
                <form action="{{ Route::has('logout') ? route('logout') : url('/logout') }}" method="POST" class="w-full">
                    @csrf
                    <button
                        type="submit"
                        class="flex h-11 w-full items-center justify-center rounded-full border border-white/25 text-[14px] font-semibold text-white transition hover:bg-white/10 cursor-pointer"
                    >
                        Log out
                    </button>
                </form>
            @else
                <a
                    href="{{ Route::has('login') ? route('login') : url('/login') }}"
                    class="flex h-11 w-full items-center justify-center rounded-full border border-white/30 text-[14px] font-semibold text-white transition hover:bg-white/10"
                >
                    Log in
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : url('/register') }}"
                    class="flex h-11 w-full items-center justify-center rounded-full bg-[#FCC719] text-[14px] font-bold text-[#071943] shadow-md transition hover:bg-[#ffd84c]"
                >
                    Register
                </a>
            @endif
        </div>
    </div>
</header>
