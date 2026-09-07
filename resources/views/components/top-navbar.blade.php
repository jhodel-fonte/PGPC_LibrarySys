@props(['activepage' => 'Dashboard', 'subpage' => null, 'activepageRoute' => null])

@php
    $staffRole = ucfirst(auth()->user()?->role?->name ?? 'Librarian');
@endphp

<div class="h-[70px] bg-navy-primary md:bg-white border-b border-white/10 md:border-[#E2E8F0] px-4 md:px-6 flex justify-between items-center w-full shrink-0 z-30 sticky top-0 transition-colors">

    <!-- Left: Hamburger Menu, Logo & Title on Mobile, Breadcrumbs on Desktop -->
    <div class="flex items-center gap-3 md:gap-4 text-sm font-medium text-white/70 md:text-[#475569] min-w-0">

        <!-- Hamburger Button (Mobile: toggles sidebarOpen, Desktop: toggles sidebarMinimized) -->
        <button
            @click="window.innerWidth < 768 ? (sidebarOpen = !sidebarOpen) : (sidebarMinimized = !sidebarMinimized)"
            class="p-2 -ml-2 rounded-xl text-white/90 md:text-[#475569] hover:text-white hover:bg-white/10 md:hover:bg-[#F8FAFC] md:hover:text-[#102B70] transition-colors focus:outline-none focus:ring-2 focus:ring-[#102B70]/20"
            aria-label="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Logo & Title for Mobile/Tablet -->
        <div class="flex md:hidden items-center gap-2 overflow-hidden">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white shadow-xs">
                <img src="{{ asset('images/logo.webp') }}" alt="PGPC Logo" class="h-full w-full object-cover">
            </div>
            <div class="text-left flex flex-col min-w-0">
                <p class="text-xs font-bold leading-tight text-white truncate">{{ config('settings.name') }} Library</p>
                <p class="text-xs font-semibold uppercase tracking-wider text-white/60 leading-none truncate mt-0.5">{{ $staffRole }} Portal</p>
            </div>
        </div>

        <!-- Breadcrumbs for Desktop -->
        <nav aria-label="Breadcrumb" class="hidden md:flex items-center text-s font-semibold">
            <span class="text-[#64748B]">{{ $staffRole }}</span>
            <span class="mx-2 text-[#CBD5E1] select-none" aria-hidden="true">&gt;</span>
            @if($subpage)
                @if($activepageRoute)
                    <a href="{{ route($activepageRoute) }}" wire:navigate class="text-[#475569] hover:text-[#102B70] hover:underline transition-colors capitalize">{{ $activepage }}</a>
                @else
                    <span class="text-[#475569] capitalize">{{ $activepage }}</span>
                @endif
                <span class="mx-2 text-[#CBD5E1] select-none" aria-hidden="true">&gt;</span>
                <span class="text-[#0F172A] font-bold capitalize">{{ $subpage }}</span>
            @else
                <span class="text-[#0F172A] font-bold capitalize">{{ $activepage }}</span>
            @endif
        </nav>
    </div>

    <!-- Right: Live Clock, Notifications & Profile -->
    <div class="flex items-center justify-end gap-3 sm:gap-4">

        <!-- Search trigger for mobile view only -->
        <a href="" class="md:hidden p-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-white/20" aria-label="Search catalog">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </a>

        <!-- Live Clock powered by Alpine.js -->
        <x-clock />

        <!-- Overdue items notification card -->
        <x-notification-card />

        <!-- Profile Avatar / Dropdown -->
        <x-profile />
    </div>
</div>
