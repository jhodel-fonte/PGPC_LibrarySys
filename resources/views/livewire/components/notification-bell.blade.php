<div class="relative cursor-pointer" wire:poll.15s="checkNotifications">
    <!-- Bell Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white hover:text-[#fcc719] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>

    <!-- Notification Badge (Only shows if there are unread notifications) -->
    @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</div>