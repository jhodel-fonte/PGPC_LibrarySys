@props(['title' => 'Notifications'])

@php
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
@endphp

<style>
    .notification-toggle { color: #475569; }
    .notification-toggle:hover { color: #102B70; }
    @media (max-width: 767px) {
        .notification-toggle { color: rgba(255, 255, 255, .9); }
        .notification-toggle:hover { color: #fff; }
    }
</style>

<div x-data="{ dropdownOpen: false, unreadCount: @js($unreadCount) }" @notifications-updated.window="unreadCount = $event.detail.unreadCount" class="relative">
    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="notification-toggle relative rounded-lg p-2 hover:bg-white/10 md:hover:bg-slate-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]/30" aria-label="View notifications" :aria-expanded="dropdownOpen">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Notification Badge Indicator -->
        <span x-cloak x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EF4444] text-white text-[10px] font-black flex items-center justify-center border-2 border-white shadow-2xs"></span>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="dropdownOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         style="display: none;"
         class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50 font-sans">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/70">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-slate-800">{{ $title }}</h3>
                <span x-cloak x-show="unreadCount > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]"><span x-text="unreadCount"></span> new</span>
            </div>
            <button 
                type="button" 
                @click="$dispatch('mark-all-notifications-read')"
                x-show="unreadCount > 0"
                x-cloak
                class="text-xs text-[#3B82F6] hover:text-[#102B70] font-semibold transition-colors cursor-pointer"
            >
                Mark all as read
            </button>
        </div>

        <!-- Content -->
        <div class="max-h-80 overflow-y-auto">
            <livewire:components.notification.notification-content />
        </div>
    </div>
</div>
