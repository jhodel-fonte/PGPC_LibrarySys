@props([
    'routePrefix' => 'librarian.',
    'signedInUser' => null,
    'textSize' => 'text-[16px]'
])

@php
    $signedInUser = $signedInUser ?? auth()->user();
    $textClass = $textSize;
@endphp

<!-- ADMIN / LIBRARIAN NAVIGATION -->
<!-- Dashboard Link -->
<x-widget.sidebar-button href="{{ route($routePrefix . 'dashboard') }}" :active="request()->routeIs($routePrefix . 'dashboard')" :textSize="$textClass" class="mb-2" wire:navigate>
    <x-slot:icon>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
    </x-slot:icon>
    Dashboard
</x-widget.sidebar-button>

<!-- CIRCULATION SECTION -->
@if($signedInUser && $signedInUser->canManageCirculation())
    <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
        <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Circulation</span>
    </div>

    <x-widget.sidebar-button href="{{ route($routePrefix . 'circulation-desk.index') }}" :active="request()->routeIs($routePrefix . 'circulation-desk.index') || request()->routeIs($routePrefix . 'circulation-desk.borrow') || request()->routeIs($routePrefix . 'circulation-desk.return*') || request()->routeIs($routePrefix . 'circulation-desk.borrowed-books') || request()->routeIs($routePrefix . 'circulation-desk.reservations') || request()->routeIs($routePrefix . 'reservations*')" :textSize="$textClass" class="mb-2" wire:navigate>
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
        </x-slot:icon>
        Circulation Desk
    </x-widget.sidebar-button>


@endif

<!-- CATALOG SECTION -->
@if($signedInUser && $signedInUser->canManageCatalog())
    <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
        <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Catalog</span>
    </div>

    <x-widget.sidebar-button href="{{ route($routePrefix . 'book-management.index') }}" :active="request()->routeIs($routePrefix . 'book-management.*')" :textSize="$textClass" class="mb-3" wire:navigate>
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </x-slot:icon>
        Cataloging
    </x-widget.sidebar-button>
@endif

<!-- USERS SECTION -->
@php
    $canManageStudents = $signedInUser && $signedInUser->canManageStudents();
    $canManageLibrarians = $signedInUser && $signedInUser->canManageLibrarians();
@endphp
@if($canManageStudents || $canManageLibrarians)
    <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
        <span class="text-xs font-bold text-white/50 uppercase tracking-wider">Users</span>
    </div>

    @if($canManageStudents && $canManageLibrarians)
        <x-widget.sidebar-button :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')" :textSize="$textClass">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot:icon>
            User Management
            <x-slot:subitems>
                <x-widget.sidebar-subitem href="{{ route($routePrefix . 'user-management', ['type' => 'student']) }}" :active="!request()->routeIs($routePrefix . 'user-management.approvals') && request()->routeIs($routePrefix . 'user-management*') && request('type', 'student') === 'student'" wire:navigate>
                    Students
                </x-widget.sidebar-subitem>
                <x-widget.sidebar-subitem href="{{ route($routePrefix . 'user-management.approvals') }}" :active="request()->routeIs($routePrefix . 'user-management.approvals')" wire:navigate>
                    Account Approvals
                </x-widget.sidebar-subitem>
                <x-widget.sidebar-subitem href="{{ route($routePrefix . 'user-management', ['type' => 'librarian']) }}" :active="!request()->routeIs($routePrefix . 'user-management.approvals') && request()->routeIs($routePrefix . 'user-management*') && request('type') === 'librarian'" wire:navigate>
                    Librarians
                </x-widget.sidebar-subitem>
            </x-slot:subitems>
        </x-widget.sidebar-button>
    @elseif($canManageStudents)
        <x-widget.sidebar-button :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')" :textSize="$textClass">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot:icon>
            User Management
            <x-slot:subitems>
                <x-widget.sidebar-subitem href="{{ route($routePrefix . 'user-management', ['type' => 'student']) }}" :active="!request()->routeIs($routePrefix . 'user-management.approvals') && request()->routeIs($routePrefix . 'user-management*')" wire:navigate>
                    Students
                </x-widget.sidebar-subitem>
                <x-widget.sidebar-subitem href="{{ route($routePrefix . 'user-management.approvals') }}" :active="request()->routeIs($routePrefix . 'user-management.approvals')" wire:navigate>
                    Account Approvals
                </x-widget.sidebar-subitem>
            </x-slot:subitems>
        </x-widget.sidebar-button>
    @elseif($canManageLibrarians)
        <x-widget.sidebar-button href="{{ route($routePrefix . 'user-management', ['type' => 'librarian']) }}" :active="request()->routeIs($routePrefix . 'user-management*') || request()->routeIs($routePrefix . 'user-details')" :textSize="$textClass" wire:navigate>
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot:icon>
            Librarians
        </x-widget.sidebar-button>
    @endif
@endif

<!-- Analytics Accordion with Subitems -->
@if($signedInUser && $signedInUser->canViewReports())
<x-widget.sidebar-button :active="request()->routeIs($routePrefix . 'analytics*')" :textSize="$textClass">
    <x-slot:icon>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
    </x-slot:icon>
    Analytics
    <x-slot:subitems>
        <x-widget.sidebar-subitem
            href="{{ route($routePrefix . 'analytics') }}"
            :active="request()->routeIs($routePrefix . 'analytics') && !request()->routeIs($routePrefix . 'analytics.circulation')"
            wire:navigate
        >
            Overview
        </x-widget.sidebar-subitem>
        <x-widget.sidebar-subitem
            href="{{ route($routePrefix . 'analytics.ai') }}"
            :active="request()->routeIs($routePrefix . 'analytics.ai')"
            wire:navigate
        >
            AI Analytics
        </x-widget.sidebar-subitem>
        {{-- Circulation analytics subitem is hidden while its route is preserved --}}
    </x-slot:subitems>
</x-widget.sidebar-button>
@endif

<!-- SYSTEM SECTION -->
{{-- @if($signedInUser && $signedInUser->canManageSettings())
    <div class="px-2 mt-6 mb-2" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
        <span class="text-xs font-bold text-white/50 uppercase tracking-wider">System</span>
    </div>

    <x-widget.sidebar-button href="{{ route($routePrefix . 'settings') }}" :active="request()->routeIs($routePrefix . 'settings')" wire:navigate>
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </x-slot:icon>
        Settings
    </x-widget.sidebar-button>
@endif --}}
