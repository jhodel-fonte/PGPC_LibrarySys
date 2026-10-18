    <!-- Middle: Live Unified Search Bar (Desktop Only) with Alpine.js -->
    {{-- <div 
        x-data="{ showDropdown: false, query: '{{ request('q') }}' }" 
        @click.away="showDropdown = false"
        class="hidden lg:block w-full max-w-md mx-4 relative"
    >
        <form action="" method="GET" class="relative group w-full" @submit.prevent="/* Add your Livewire/JS search logic here */">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400 group-focus-within:text-navy-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            
            <input 
                x-model="query"
                @focus="showDropdown = true"
                @input="showDropdown = query.length > 0"
                type="text" 
                name="q" 
                autocomplete="off"
                class="block w-full pl-9 pr-20 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:border-navy-primary focus:ring-1 focus:ring-navy-primary outline-none transition-all shadow-sm placeholder:text-gray-400" 
                placeholder="Search history, reservations, saved items, books..."
            >   

            <button type="submit" class="absolute top-1/2 -translate-y-1/2 right-1.5 px-3 py-1.5 bg-navy-primary hover:bg-navy-hover text-white text-xs font-medium rounded-md transition-colors shadow-sm focus:outline-none">
                Search
            </button>
        </form>

        <div 
            x-show="showDropdown"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            style="display: none;"
            class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 max-h-[420px] overflow-y-auto z-50 divide-y divide-gray-100 font-sans"
        >
            <div class="p-4 text-sm text-gray-500 text-center">
                Search results for "<span x-text="query" class="font-bold"></span>" will appear here.
            </div>
        </div>
    </div> --}}