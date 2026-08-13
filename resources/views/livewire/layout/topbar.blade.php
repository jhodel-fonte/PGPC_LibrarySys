<header class="h-[60px] bg-[#102b70] lg:bg-white border-b border-white/5 lg:border-gray-200 px-4 md:px-6 flex justify-between items-center w-full shrink-0 z-30 relative">
    
    <!-- Left: Mobile Menu, Logo & Title, Breadcrumbs on Desktop -->
    <div class="flex items-center gap-2.5 text-sm font-medium text-white/70 lg:text-gray-500 min-w-0">
        <!-- Logo & Title for Mobile/Tablet -->
        <div class="flex lg:hidden items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white">
                <img src="{{ Vite::asset('resources/images/webp/hd-pgpc-logo.webp') }}" alt="PGPC Logo" class="h-full w-full object-cover">
            </div>
            <div class="text-left">
                <p class="text-xs font-bold leading-tight text-white">PGPC Library</p>
                <!-- NOTE: Change this to Admin Portal if this is for the Admin -->
                <p class="text-[9px] font-semibold uppercase tracking-wider text-white/50 leading-none">Student Portal</p>
            </div>
        </div>

        <!-- Breadcrumbs for Desktop -->
        <div class="hidden lg:block">
            <!-- NOTE: Change this to Admin if this is for the Admin -->
            <span class="text-gray-500">Student</span>
            <span class="mx-1 text-gray-400">&gt;</span>
            {{-- <span class="text-gray-800 font-bold capitalize">{{ $section }}</span> --}}
        </div>
    </div>

    <!-- Middle: Live Unified Search Bar (Desktop Only) with Alpine.js -->
    <!-- x-data manages the dropdown visibility automatically -->
    {{-- <div 
        x-data="{ showDropdown: false, query: '{{ request('q') }}' }" 
        @click.away="showDropdown = false"
        class="hidden lg:block w-full max-w-md mx-4 relative"
    >
        <form action="" method="GET" class="relative group w-full" @submit.prevent="/* Add your Livewire/JS search logic here */">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400 group-focus-within:text-[#102b70] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                class="block w-full pl-9 pr-20 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:border-[#102b70] focus:ring-1 focus:ring-[#102b70] outline-none transition-all shadow-sm placeholder:text-gray-400" 
                placeholder="Search history, reservations, saved items, books..."
            >   

            <button type="submit" class="absolute top-1/2 -translate-y-1/2 right-1.5 px-3 py-1.5 bg-[#102b70] hover:bg-[#0b225e] text-white text-xs font-medium rounded-md transition-colors shadow-sm focus:outline-none">
                Search
            </button>
        </form>

        <!-- Floating Live Search Results Dropdown -->
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
            <!-- Results container -->
            <div class="p-4 text-sm text-gray-500 text-center">
                Search results for "<span x-text="query" class="font-bold"></span>" will appear here.
            </div>
        </div>
    </div> --}}

    <!-- Right: Live Clock & Notifications -->
    <div class="flex items-center justify-end gap-3 sm:gap-5">
        
        <!-- Search trigger for mobile view only -->
        <a href="" class="lg:hidden p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10" aria-label="Search catalog">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </a>

        <!-- Live Clock powered entirely by Alpine.js -->
        <div 
            x-data="{ 
                time: '', 
                date: '',
                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },
                updateClock() {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('en-US', { hour12: true });
                    this.date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                }
            }" 
            class="hidden lg:flex flex-col items-end leading-tight"
        >
            <span x-text="time" class="text-sm font-bold text-gray-800 leading-tight"></span>
            <span x-text="date" class="text-[10px] text-gray-500 font-medium uppercase tracking-wider"></span>
        </div>

        <!-- Overdue items notification bell -->
        <livewire:components.notification-bell />

        <!-- Topbar Profile Button -->
        <a href="" class="flex items-center gap-2 pl-2 border-l border-white/10 lg:border-gray-200 group" title="My Profile">
            <div class="w-8 h-8 rounded-full bg-[#102b70] text-[#fcc719] flex items-center justify-center text-xs font-black overflow-hidden border border-white/20 lg:border-gray-200 shadow-2xs group-hover:ring-2 group-hover:ring-[#fcc719] lg:group-hover:ring-[#102b70]/20 transition-all">
                {{-- @if(Auth::guard('member')->user()?->profile_image)
                    <img src="{{ route('images.user-avatar', ['memberAuth' => Auth::guard('member')->user()->member_auth_id, 'v' => strtotime((string)(Auth::guard('member')->user()->updated_at ?? 1))]) }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(Auth::guard('member')->user()?->member?->first_name ?? 'S', 0, 1)) }}
                @endif --}}
            </div>
        </a>
    </div>
</header>