@props(['active' => ''])
@php
    $signedInUser = auth()->user();
    $accountType = strtolower((string) ($signedInUser?->account_type ?? ''));
    $dashboardUrl = '#';
@endphp

<!-- Single Root Element for Livewire -->
<div class="relative z-50">
    <!-- Sidebar Overlay (Click to close sidebar on mobile) -->
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 bg-black/60 backdrop-blur-sm md:hidden z-40"
        style="display: none;"
    ></div>

    <!-- Main Vertical Sidebar -->
    <aside
        :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            sidebarMinimized ? 'md:w-[80px]' : 'md:w-[280px]'
        ]"
        class="fixed inset-y-0 left-0 w-[280px] bg-navy-primary text-white flex flex-col transition-all duration-300 ease-in-out md:translate-x-0 z-50 shadow-[4px_0_24px_rgba(0,0,0,0.15)] overflow-hidden h-dvh"
    >

        <!-- Header / Logo Area -->
        <div class="min-h-[60px] pt-6 px-4 flex items-center justify-between shrink-0" :class="sidebarMinimized ? 'justify-center' : ''">
            <x-brand-title href="{{ route('admin.dashboard') }}" />

            <!-- Close Button for Mobile -->
            <button @click="sidebarOpen = false" class="md:hidden p-1.5 text-white/60 hover:text-white hover:bg-white/10 rounded-lg transition-colors focus:outline-none shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">

            <!-- Dashboard Link (Active State Example) -->
            <x-sidebar-button href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" class="mb-3" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </x-slot:icon>
                Dashboard
            </x-sidebar-button>

            <!-- CIRCULATION SECTION -->
            <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                <span class="text-[11px] font-bold text-white/40 uppercase tracking-widest">Circulation</span>
            </div>

            <x-sidebar-button href="{{ route('admin.circulation-desk.index') }}" :active="request()->routeIs('admin.circulation-desk.*')" class="mb-6" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </x-slot:icon>
                Circulation Desk
            </x-sidebar-button>

            <x-sidebar-button href="#" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </x-slot:icon>
                Reservations
            </x-sidebar-button>

            <!-- CATALOG SECTION -->
            <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                <span class="text-[11px] font-bold text-white/40 uppercase tracking-widest">Catalog</span>
            </div>

            <x-sidebar-button href="{{ route('admin.book-management.index') }}" :active="request()->routeIs('admin.book-management.*')" class="mb-3" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </x-slot:icon>
                Books
                <x-slot:subitems>
                    <x-sidebar-subitem href="{{ route('admin.book-management.index') }}" wire:navigate>Cataloging</x-sidebar-subitem>
                    <x-sidebar-subitem href="#" wire:navigate>Add New Book</x-sidebar-subitem>
                    <x-sidebar-subitem href="#" wire:navigate>Book Import</x-sidebar-subitem>
                    <x-sidebar-subitem href="#" wire:navigate>Categories</x-sidebar-subitem>
                </x-slot:subitems>
            </x-sidebar-button>

            <x-sidebar-button href="#">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </x-slot:icon>
                Digital Collections
                <x-slot:subitems>
                    <x-sidebar-subitem href="#" wire:navigate>E-Resource</x-sidebar-subitem>
                    <x-sidebar-subitem href="#" wire:navigate>Add New E-Resource</x-sidebar-subitem>
                    <x-sidebar-subitem href="#" wire:navigate>E-Resource Collections</x-sidebar-subitem>
                </x-slot:subitems>
            </x-sidebar-button>

            <!-- USERS SECTION -->
            <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                <span class="text-[11px] font-bold text-white/40 uppercase tracking-widest">Users</span>
            </div>

            <x-sidebar-button href="{{ route('admin.user-management') }}" :active="request()->routeIs('admin.user-management')" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </x-slot:icon>
                User Management
            </x-sidebar-button>

            <!-- SYSTEM SECTION -->
            <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                <span class="text-[11px] font-bold text-white/40 uppercase tracking-widest">System</span>
            </div>

            <x-sidebar-button href="{{ route('admin.settings') }}" :active="request()->routeIs('admin.settings')" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </x-slot:icon>
                Settings
            </x-sidebar-button>
        </nav>

    </aside>

    <!-- Custom Scrollbar Styles for the Sidebar -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</div>
