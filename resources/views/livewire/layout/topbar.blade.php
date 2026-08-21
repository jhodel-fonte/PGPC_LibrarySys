@php
    $staffRole = ucfirst(auth()->user()?->role?->name ?? 'Librarian');
@endphp

<div class="h-[60px] bg-navy-primary md:bg-white border-b border-white/5 md:border-gray-200 px-4 md:px-6 flex justify-between items-center w-full shrink-0 z-30 sticky top-0 transition-colors">
    
    <!-- Left: Hamburger Menu, Logo & Title, Breadcrumbs on Desktop -->
    <div class="flex items-center gap-3 md:gap-4 text-sm font-medium text-white/70 md:text-gray-500 min-w-0">
        
        <!-- Hamburger Button (Mobile: toggles sidebarOpen, Desktop: toggles sidebarMinimized) -->
        <button 
            @click="window.innerWidth < 768 ? (sidebarOpen = !sidebarOpen) : (sidebarMinimized = !sidebarMinimized)" 
            class="p-2 -ml-2 rounded-lg text-white/90 md:text-gray-500 hover:text-white hover:bg-white/10 md:hover:bg-gray-100 md:hover:text-navy-primary transition-colors focus:outline-none" 
            aria-label="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Logo & Title for Mobile/Tablet -->
        <div class="flex md:hidden items-center gap-2 overflow-hidden">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white">
                <img src="{{ asset('images/logo.webp') }}" alt="PGPC Logo" class="h-full w-full object-cover">
            </div>
            <div class="text-left flex flex-col min-w-0">
                <p class="text-xs font-bold leading-tight text-white truncate">{{ config('settings.name') }} Library</p>
                <p class="text-[9px] font-semibold uppercase tracking-wider text-white/50 leading-none truncate mt-0.5">{{ $staffRole }} Portal</p>
            </div>
        </div>

        <!-- Breadcrumbs for Desktop -->
        <div class="hidden md:block">
            <span class="text-gray-500">{{ $staffRole }}</span>
            <span class="mx-1 text-gray-400">&gt;</span>
            <span class="text-gray-800 font-bold capitalize">Dashboard</span>
        </div>
    </div>

    <!-- Right: Live Clock & Notifications -->
    <div class="flex items-center justify-end gap-3 sm:gap-5">
        
        <!-- Search trigger for mobile view only -->
        <a href="" class="md:hidden p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10" aria-label="Search catalog">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </a>

        <!-- Live Clock powered entirely by Alpine.js -->
        <x-clock />

        <!-- Overdue items notification bell -->
        <x-notification-card />

        <x-profile />
    </div>
</div>