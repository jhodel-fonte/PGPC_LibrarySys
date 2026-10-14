@props(['active' => ''])
@php
    $signedInUser = auth()->user();
    $accountType = strtolower((string) ($signedInUser?->account_type ?? ''));
    $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
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
        class="fixed inset-y-0 left-0 w-[280px] bg-navy-primary text-white flex flex-col transition-all duration-300 ease-in-out md:translate-x-0 z-50 shadow-[4px_0_24px_rgba(7,25,67,0.18)] overflow-hidden h-dvh"
    >

        <!-- Header / Logo Area -->
        <div class="min-h-[60px] pt-6 px-4 flex items-center justify-between shrink-0" :class="sidebarMinimized ? 'justify-center' : ''">
            <x-brand-title href="{{ route($routePrefix . 'dashboard') }}" />

            <!-- Close Button for Mobile -->
            <button @click="sidebarOpen = false" class="md:hidden p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 shrink-0" aria-label="Close Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">

            <!-- Dashboard Link -->
            <x-sidebar-button href="{{ route($routePrefix . 'dashboard') }}" :active="request()->routeIs($routePrefix . 'dashboard')" class="mb-3" wire:navigate>
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </x-slot:icon>
                Dashboard
            </x-sidebar-button>

            <!-- CIRCULATION SECTION -->
            @if($signedInUser && ($signedInUser->hasRole('Admin') || $signedInUser->hasPermission('manage_circulation') || $signedInUser->hasPermission('view_circulation')))
                <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                    <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Circulation</span>
                </div>

                <x-sidebar-button href="{{ route($routePrefix . 'circulation-desk.index') }}" :active="request()->routeIs($routePrefix . 'circulation-desk.*')" class="mb-6" wire:navigate>
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                    Circulation Desk
                </x-sidebar-button>
            @endif

            <!-- CATALOG SECTION -->
            @if($signedInUser && ($signedInUser->hasRole('Admin') || $signedInUser->hasPermission('manage_catalog') || $signedInUser->hasPermission('view_catalog')))
                <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                    <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Catalog</span>
                </div>

                <x-sidebar-button href="{{ route($routePrefix . 'book-management.index') }}" :active="request()->routeIs($routePrefix . 'book-management.*')" class="mb-3" wire:navigate>
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                    Books
                </x-sidebar-button>
            @endif

            <!-- USERS SECTION -->
            @php
                $canManageStudents = $signedInUser && ($signedInUser->hasRole('Admin') || $signedInUser->hasPermission('manage_users') || $signedInUser->hasPermission('view_students'));
                $canManageLibrarians = $signedInUser && ($signedInUser->hasRole('Admin') || $signedInUser->hasPermission('manage_librarians'));
            @endphp
            @if($canManageStudents || $canManageLibrarians)
                <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                    <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Users</span>
                </div>

                @if($canManageStudents && $canManageLibrarians)
                    <x-sidebar-button :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')">
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </x-slot:icon>
                        User Management
                        <x-slot:subitems>
                            <x-sidebar-subitem href="{{ route($routePrefix . 'user-management', ['type' => 'student']) }}" :active="request()->routeIs($routePrefix . 'user-management*') && request('type', 'student') === 'student'" wire:navigate>
                                Students
                            </x-sidebar-subitem>
                            <x-sidebar-subitem href="{{ route($routePrefix . 'user-management', ['type' => 'librarian']) }}" :active="request()->routeIs($routePrefix . 'user-management*') && request('type') === 'librarian'" wire:navigate>
                                Librarians
                            </x-sidebar-subitem>
                        </x-slot:subitems>
                    </x-sidebar-button>
                @elseif($canManageStudents)
                    <x-sidebar-button href="{{ route($routePrefix . 'user-management', ['type' => 'student']) }}" :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')" wire:navigate>
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </x-slot:icon>
                        Students
                    </x-sidebar-button>
                @elseif($canManageLibrarians)
                    <x-sidebar-button href="{{ route($routePrefix . 'user-management', ['type' => 'librarian']) }}" :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')" wire:navigate>
                        <x-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </x-slot:icon>
                        Librarians
                    </x-sidebar-button>
                @endif
            @endif

            <!-- SYSTEM SECTION -->
            @if($signedInUser && ($signedInUser->hasRole('Admin') || $signedInUser->hasPermission('manage_settings')))
                <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                    <span class="text-xs font-bold text-white/50 uppercase tracking-wider">System</span>
                </div>

                <x-sidebar-button href="{{ route($routePrefix . 'settings') }}" :active="request()->routeIs($routePrefix . 'settings')" wire:navigate>
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                    Settings
                </x-sidebar-button>
            @endif
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
