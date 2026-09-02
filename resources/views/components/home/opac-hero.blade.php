@props([
    'actionUrl' => Route::has('opac.index') ? route('opac.index') : url('/opac.index'),
    'advancedSearchUrl' => '#advanced-search',
    'title' => 'Online Public Access Catalog',
    'eyebrow' => 'OPAC',
    'subtitle' => 'Search the library catalog to discover books, journals, theses and other resources available in PGPC Library.',
    'placeholder' => 'Search title, author, keyword, subject, or ISBN...',
    'selectedType' => request('type', 'all'),
    'searchValue' => request('search', ''),
])

<section
    class="relative flex min-h-[340px] sm:min-h-[360px] lg:min-h-[380px] w-full flex-col justify-center bg-[#071A3D] text-white select-none py-10 sm:py-12 lg:py-14 border-b-2 border-[#F9C000]"
>
    <!-- Background Wrapper (Has overflow-hidden so campus image & seal don't spill out, while allowing dropdown to pop out over bottom line) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Layer 1: Campus Building Photograph -->
        <img
            src="{{ asset('images/pgpc-ng.webp') }}"
            alt="Padre Garcia Polytechnic College Campus"
            loading="eager"
            decoding="async"
            class="absolute inset-0 h-full w-full object-cover object-right md:object-center select-none opacity-25"
        >

        <!-- Layer 2: Subtle PGPC School Seal -->
        <div
            class="absolute left-[35%] lg:left-[40%] top-1/2 -translate-y-1/2 select-none hidden md:block"
            style="opacity: 0.08;"
            aria-hidden="true"
        >
            <img
                src="{{ asset('images/logo.webp') }}"
                alt=""
                width="500"
                height="500"
                class="h-[440px] w-[440px] lg:h-[500px] lg:w-[500px] max-w-none object-contain mix-blend-luminosity grayscale contrast-125"
                onerror="this.src='{{ asset('logo.webp') }}'"
            >
        </div>

        <!-- Layer 3: Dark Navy Gradient Overlay (#071A3D to #0B2454) -->
        <div
            class="absolute inset-0"
            style="background: linear-gradient(90deg, rgba(7, 26, 61, 0.98) 0%, rgba(11, 36, 84, 0.94) 48%, rgba(11, 36, 84, 0.82) 100%);"
        ></div>
    </div>

    <!-- Content Container (Aligned with navbar: max-w-[1380px] px-4 sm:px-6 lg:px-8) -->
    <div class="relative z-20 mx-auto w-full max-w-[1380px] px-4 sm:px-6 lg:px-8">

        <h1 class="text-5xl sm:text-5xl lg:text-[50px] font-extrabold tracking-tight text-[#FFFFFF] leading-tight mb-2.5">
            {{ $title }}
        </h1>

        <!-- 3. Supporting Description (Muted White #D7E0F0) -->
        <p class="text-[14.5px] sm:text-[15px] leading-relaxed text-[#D7E0F0] max-w-2xl mb-6 font-normal">
            {{ $subtitle }}
        </p>

        <!-- 4. Main Search Row (Search Bar + Advanced Search) -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 sm:gap-4 lg:gap-5 max-w-[960px]">

            <!-- Main Unified Search Component -->
            <form
                action="{{ $actionUrl }}"
                method="GET"
                x-data="{
                    openDropdown: false,
                    selectedType: '{{ $selectedType }}',
                    selectedLabel: 'All Resources',
                    types: [
                        { value: 'all', label: 'All Resources' },
                        { value: 'books', label: 'Books' },
                        { value: 'theses', label: 'Theses' },
                        { value: 'journals', label: 'Journals' },
                    ],
                    init() {
                        const found = this.types.find(t => t.value === this.selectedType);
                        if (found) this.selectedLabel = found.label;
                    },
                    select(type) {
                        this.selectedType = type.value;
                        this.selectedLabel = type.label;
                        this.openDropdown = false;
                    }
                }"
                class="relative flex-1 max-w-[800px] w-full"
            >
                <input type="hidden" name="type" :value="selectedType">

                <!-- Desktop & Tablet: Unified Single White Bar (h-[62px]–h-[66px], rounded-[14px]) -->
                <div class="hidden sm:flex items-center h-[62px] sm:h-[66px] rounded-[14px] bg-white p-1.5 shadow-xl shadow-black/25 border border-white/20">

                    <!-- Resource Type Selector (200px–215px wide, self-stretch) -->
                    <div class="relative self-stretch flex items-center w-[195px] lg:w-[210px] shrink-0 border-r border-[#E2E8F0]" @click.outside="openDropdown = false">
                        <button
                            type="button"
                            @click="openDropdown = !openDropdown"
                            class="flex w-full items-center justify-between px-4 py-2 text-left focus:outline-none group/sel cursor-pointer select-none"
                            aria-haspopup="true"
                            :aria-expanded="openDropdown"
                        >
                            <span x-text="selectedLabel" class="text-[15px] sm:text-[15.5px] font-semibold text-[#071A3D] truncate pr-1">All Resources</span>
                            <!-- Downward Chevron -->
                            <svg width="18" height="18" class="h-[18px] w-[18px] shrink-0 text-[#64748B] transition-transform duration-200" :class="openDropdown ? 'rotate-180 text-[#071A3D]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Resource Dropdown Menu (Positioned directly below selector, aligned with search bar's outer edge) -->
                        <div
                            x-show="openDropdown"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                            style="display: none;"
                            class="absolute -left-1.5 top-[calc(100%+8px)] w-[230px] rounded-2xl border border-[#DCE4EF] bg-white py-2 shadow-2xl z-50 text-[#0F172A]"
                        >
                            <template x-for="t in types" :key="t.value">
                                <button
                                    type="button"
                                    @click="select(t)"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-[14.5px] font-medium transition-colors hover:bg-slate-50 hover:text-[#0B2454] cursor-pointer"
                                    :class="selectedType === t.value ? 'bg-[#EFF6FF] text-[#0B2454] font-semibold' : 'text-slate-700'"
                                >
                                    <span x-text="t.label"></span>
                                    <svg width="16" height="16" x-show="selectedType === t.value" class="h-4 w-4 shrink-0 text-[#0B2454]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Search Input Field (Clean font-medium with light font-normal placeholder) -->
                    <div class="flex flex-1 items-center px-4 h-full">
                        <svg width="20" height="20" class="h-5 w-5 shrink-0 text-[#94A3B8] mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ $searchValue }}"
                            placeholder="{{ $placeholder }}"
                            class="w-full border-0 border-none bg-transparent p-0 text-[16px] sm:text-[16.5px] text-[#071A3D] placeholder:text-[#94A3B8] placeholder:font-normal placeholder:text-[15px] sm:placeholder:text-[15.5px] focus:border-0 focus:border-none focus:outline-none focus:ring-0 shadow-none font-medium"
                        >
                    </div>

                    <!-- Search Button (Gold #F9C000, 68–72px wide, rounded 10px) -->
                    <button
                        type="submit"
                        class="grid h-[50px] sm:h-[54px] w-[66px] sm:w-[70px] shrink-0 place-items-center rounded-[10px] bg-[#F9C000] text-[#071A3D] shadow-xs transition-colors hover:bg-[#e6b000] active:scale-95 focus:outline-none cursor-pointer mr-0.5"
                        aria-label="Submit OPAC search"
                    >
                        <svg width="22" height="22" class="h-[22px] w-[22px] shrink-0 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile: Clean Stacked Layout (< sm) -->
                <div class="flex flex-col sm:hidden gap-2.5">
                    <!-- Resource Selector Mobile -->
                    <div class="relative w-full rounded-[10px] bg-white border border-white/20 shadow-md" @click.outside="openDropdown = false">
                        <button
                            type="button"
                            @click="openDropdown = !openDropdown"
                            class="flex w-full items-center justify-between px-4 py-3 text-left focus:outline-none"
                        >
                            <span x-text="selectedLabel" class="text-[14.5px] font-bold text-[#071A3D]">All Resources</span>
                            <svg width="16" height="16" class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-show="openDropdown"
                            style="display: none;"
                            class="absolute left-0 top-[calc(100%+4px)] w-full rounded-xl border border-[#DCE4EF] bg-white py-1.5 shadow-xl z-50 text-[#0F172A]"
                        >
                            <template x-for="t in types" :key="t.value">
                                <button
                                    type="button"
                                    @click="select(t)"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-[14px] font-medium hover:bg-slate-50"
                                    :class="selectedType === t.value ? 'bg-[#EFF6FF] text-[#0B2454] font-bold' : 'text-slate-700'"
                                >
                                    <span x-text="t.label"></span>
                                    <svg width="16" height="16" x-show="selectedType === t.value" class="h-4 w-4 shrink-0 text-[#0B2454]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Search Input + Button Mobile -->
                    <div class="flex items-center h-[54px] rounded-[10px] bg-white p-1 shadow-md">
                        <div class="flex flex-1 items-center px-3">
                            <svg width="18" height="18" class="h-[18px] w-[18px] shrink-0 text-[#94A3B8] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                name="search"
                                value="{{ $searchValue }}"
                                placeholder="{{ $placeholder }}"
                                class="w-full border-0 border-none bg-transparent p-0 text-[15px] font-semibold text-[#071A3D] placeholder:text-[#94A3B8] focus:outline-none focus:ring-0"
                            >
                        </div>
                        <button
                            type="submit"
                            class="grid h-[46px] w-[56px] shrink-0 place-items-center rounded-[8px] bg-[#F9C000] text-[#071A3D]"
                        >
                            <svg width="20" height="20" class="h-5 w-5 shrink-0 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Advanced Search Link (Right of Search Bar) -->
            <a
                href="{{ $advancedSearchUrl }}"
                class="group inline-flex shrink-0 items-center gap-2 py-2 text-[14px] font-semibold text-[#D7E0F0] hover:text-[#F9C000] transition-colors whitespace-nowrap cursor-pointer self-start sm:self-auto"
            >
                <svg width="16" height="16" class="h-4 w-4 shrink-0 text-[#D7E0F0]/80 transition-colors group-hover:text-[#F9C000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <span>Advanced Search</span>
            </a>

        </div>

    </div>
</section>
