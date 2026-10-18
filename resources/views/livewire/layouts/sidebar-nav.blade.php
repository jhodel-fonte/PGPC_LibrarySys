@props(['active' => ''])
@php
    $signedInUser = auth()->user();
    $accountType = strtolower((string) ($signedInUser?->account_type ?? ''));
    $isStudent = request()->is('student*') || ($signedInUser && $signedInUser->hasRole('Student') && !request()->is('admin*') && !request()->is('librarian*'));
    $routePrefix = $isStudent ? 'student.' : (request()->is('librarian*') ? 'librarian.' : 'admin.');
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
            <x-widget.brand-title href="{{ route($routePrefix . 'dashboard') }}" />

            <!-- Close Button for Mobile -->
            <button @click="sidebarOpen = false" class="md:hidden p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 shrink-0" aria-label="Close Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 custom-scrollbar">

            @if($isStudent)
                @include('livewire.components.navigation-bar.student-nav', [
                    'routePrefix' => $routePrefix,
                ])
            @else
                @include('livewire.components.navigation-bar.librarian-nav', [
                    'routePrefix' => $routePrefix,
                    'signedInUser' => $signedInUser,
                ])
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
