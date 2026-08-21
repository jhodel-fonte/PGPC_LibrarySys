@props(['title' => 'Notifications'])

<div x-data="{ dropdownOpen: false }" class="relative">
    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="relative text-white/80 md:text-gray-400 hover:text-white md:hover:text-[#102B70] transition-colors p-1 focus:outline-none" aria-label="View notifications">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Notification Badge Indicator -->
        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 border border-white md:border-gray-100"></span>
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
         class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-semibold text-sm text-gray-800">{{ $title }}</h3>
            <button class="text-xs text-[#3B82F6] hover:text-[#102B70] font-medium transition-colors">Mark all as read</button>
        </div>

        <!-- Content -->
        <div class="max-h-80 overflow-y-auto">
            <livewire:components.notification-content />
        </div>
        
        <!-- Footer -->
        <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 text-center">
            <a href="#" class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] transition-colors">View All Notifications</a>
        </div>
    </div>
</div>