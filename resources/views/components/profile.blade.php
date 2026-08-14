<!-- Topbar Profile Button & Dropdown -->
<div x-data="{ dropdownOpen: false }" class="relative">
    <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false" class="flex items-center gap-3 pl-3 md:pl-4 border-l border-white/10 md:border-gray-200 group focus:outline-none" title="My Profile">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-white md:bg-[#102B70] text-[#102B70] md:text-white flex items-center justify-center text-xs font-black overflow-hidden shadow-sm group-hover:ring-2 group-hover:ring-white/50 md:group-hover:ring-[#102B70]/30 transition-all">
                {{ strtoupper(substr(auth()->user()?->name ?? auth()->user()?->username ?? 'S', 0, 1)) }}
            </div>
            <!-- Name & Chevron for Desktop -->
            <div class="hidden md:flex items-center gap-1.5">
                <span class="text-sm font-semibold text-gray-700 group-hover:text-[#102B70] transition-colors">
                    {{ auth()->user()?->name ?? auth()->user()?->username ?? 'Student' }}
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-[#102B70] transition-transform duration-200" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
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
                 class="absolute right-0 top-full mt-2 w-56 z-50">
                <x-profile-actions />
            </div>
        </div>