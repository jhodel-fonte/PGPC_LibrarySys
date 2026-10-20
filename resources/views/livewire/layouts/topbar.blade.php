
@props(['activepage' => null, 'subpage' => null, 'activepageRoute' => null])

@php
    $activepage = $activepage ?? $title ?? 'Dashboard';
    $staffRole = ucfirst(auth()->user()?->role?->name ?? 'Librarian');

    $isSubpage = !empty($subpage) || !empty($activepageRoute);

    $mainPageUrl = null;
    if ($isSubpage) {
        $prefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
        $resolvedRoute = !empty($activepageRoute) ? str_replace(['admin.', 'librarian.'], $prefix, $activepageRoute) : null;
        if (!empty($resolvedRoute) && \Illuminate\Support\Facades\Route::has($resolvedRoute)) {
            $mainPageUrl = route($resolvedRoute);
        } elseif (!empty($activepageRoute) && \Illuminate\Support\Facades\Route::has($activepageRoute)) {
            $mainPageUrl = route($activepageRoute);
        } elseif (url()->previous() !== url()->current() && str_starts_with(url()->previous(), request()->root())) {
            $mainPageUrl = url()->previous();
        } else {
            $dashRoute = request()->is('librarian*') ? 'librarian.dashboard' : 'admin.dashboard';
            $mainPageUrl = \Illuminate\Support\Facades\Route::has($dashRoute) ? route($dashRoute) : url('/');
        }
    }

    $backTitle = $isSubpage && !empty($activepage) ? "Back to {$activepage}" : "You are on the main page";
@endphp

<div class="h-[70px] bg-navy-primary md:bg-white border-b border-white/5 md:border-[#E2E8F0] px-4 md:px-6 flex justify-between items-center w-full shrink-0 z-30 sticky top-0 transition-colors">
    
    <!-- Left: Hamburger Menu, Global Back Button, Logo & Title on Mobile, Breadcrumbs on Desktop -->
    <div class="flex items-center gap-2 sm:gap-2.5 md:gap-3 text-sm font-medium text-white/70 md:text-[#475569] min-w-0">
        
        <!-- Hamburger Button (Mobile: toggles sidebarOpen, Desktop: toggles sidebarMinimized) -->
        <button 
            @click="window.innerWidth < 768 ? (sidebarOpen = !sidebarOpen) : (sidebarMinimized = !sidebarMinimized)" 
            class="p-2 -ml-2 rounded-xl text-white/90 md:text-[#475569] hover:text-white hover:bg-white/10 md:hover:bg-[#F8FAFC] md:hover:text-[#102B70] transition-colors focus:outline-none focus:ring-2 focus:ring-[#102B70]/20" 
            aria-label="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Global Back Button -->
        @if($isSubpage && !empty($mainPageUrl))
            <a
                href="{{ $mainPageUrl }}"
                wire:navigate
                class="inline-flex h-9 w-9 md:h-10 md:w-10 shrink-0 items-center justify-center rounded-xl border border-white/15 md:border-[#E2E8F0] bg-white/10 md:bg-white text-white md:text-[#475569] shadow-xs transition hover:bg-white/20 md:hover:bg-[#EFF6FF] md:hover:text-[#102B70] md:hover:border-[#102B70]/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] cursor-pointer"
                title="{{ $backTitle }}"
                aria-label="{{ $backTitle }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
        @else
            <button
                type="button"
                disabled
                aria-disabled="true"
                class="inline-flex h-9 w-9 md:h-10 md:w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 md:border-[#E2E8F0] bg-white/5 md:bg-[#F8FAFC] text-white/30 md:text-[#94A3B8] opacity-40 cursor-not-allowed select-none pointer-events-none"
                title="{{ $backTitle }}"
                aria-label="{{ $backTitle }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </button>
        @endif

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
        <nav aria-label="Breadcrumb" class="hidden md:flex items-center text-sm font-semibold">
            <span class="text-[#64748B]">{{ $staffRole }}</span>
            <span class="mx-2 text-[#CBD5E1] select-none" aria-hidden="true">&gt;</span>
            @if($subpage)
                @if(!empty($mainPageUrl))
                    <a href="{{ $mainPageUrl }}" wire:navigate class="text-[#475569] hover:text-[#102B70] hover:underline transition-colors capitalize">{{ $activepage }}</a>
                @elseif($activepageRoute)
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

    <!-- Middle: Live Search Bar (Desktop / Tablet) -->
    <div class="hidden md:flex flex-1 max-w-md lg:max-w-lg mx-3 lg:mx-6 min-w-0">
        <livewire:components.widgets.top-searchbar />
    </div>

    <!-- Right: Live Clock & Notifications -->
    <div class="flex items-center justify-end gap-3 sm:gap-5 shrink-0">
        
        <!-- Search trigger for mobile view only -->
        <a href="{{ route('opac.index') }}" wire:navigate class="md:hidden p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10" aria-label="Search catalog">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </a>

        <!-- Live Clock powered entirely by Alpine.js -->
        <x-widget.clock />

        <!-- Overdue items notification bell -->
        <x-notification.notification-card />

        <x-widget.profile />
    </div>
</div>

@once
<script>
    document.addEventListener('livewire:navigated', () => {
        window.__hasInternalNav = true;
    });
</script>
@endonce