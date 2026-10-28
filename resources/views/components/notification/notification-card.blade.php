@props(['title' => 'Notifications'])

<div x-data="{ dropdownOpen: false }" class="relative">
    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="relative text-white/80 md:text-gray-400 hover:text-white md:hover:text-[#102B70] transition-colors p-1 focus:outline-none" aria-label="View notifications">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Notification Badge Indicator -->
        @php
            $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
        @endphp
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EF4444] text-white text-[10px] font-black flex items-center justify-center border-2 border-white shadow-2xs">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
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
                @if($unreadCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                        {{ $unreadCount }} new
                    </span>
                @endif
            </div>
            <button 
                type="button" 
                @click="$dispatch('mark-all-notifications-read')"
                class="text-xs text-[#3B82F6] hover:text-[#102B70] font-semibold transition-colors cursor-pointer"
            >
                Mark all as read
            </button>
        </div>

        <!-- Content -->
        <div class="max-h-80 overflow-y-auto">
            <livewire:components.notification.notification-content />
        </div>
        
        <!-- Footer -->
        <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 text-center">
            <a href="#" class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] transition-colors">View All Notifications</a>
        </div>
    </div>
</div>