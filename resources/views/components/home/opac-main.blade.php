@props([
    'results' => [],
    'pagination' => null,
    'totalResults' => 0,
    'search' => '',
    'selectedType' => 'all',
    'selectedAvailabilities' => [],
    'selectedSubjects' => [],
    'yearFrom' => null,
    'yearTo' => null,
    'sortBy' => 'relevance',
    'availabilities' => [],
    'resourceTypes' => [],
    'subjects' => [],
    'isLoggedIn' => auth()->check(),
    'currentUser' => auth()->user(),
])

@php
    $searchTerm = $search ?: request('search', '');
    $totalCount = $totalResults ?: (is_countable($results) ? count($results) : 0);
@endphp

<!-- Results Area (Light background: #F8FAFC) -->
<section
    x-data="{
        isLoading: true,
        mobileFilterOpen: false,
        reservationModalOpen: false,
        selectedBook: null,
        reserveStatus: null,
        sortBy: '{{ $sortBy }}',
        init() {
            setTimeout(() => {
                this.isLoading = false;
            }, 350);
        },
        openReserve(book) {
            this.selectedBook = book;
            this.reserveStatus = null;
            this.reservationModalOpen = true;
        },
        changeSort(newSort) {
            this.isLoading = true;
            const url = new URL(window.location.href);
            url.searchParams.set('sort', newSort);
            window.location.href = url.toString();
        }
    }"
    class="relative w-full bg-[#F8FAFC] py-8 sm:py-10 lg:py-12 select-none min-h-[600px]"
>
    <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">

        <!-- Flash messages -->
        @if (session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-[14px] text-emerald-800 flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-[14px] text-rose-800 flex items-center gap-3">
                <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Mobile Filter & Sort Bar (< lg screens) -->
        <div class="lg:hidden flex items-center justify-between gap-3 mb-5">
            <button
                type="button"
                @click="mobileFilterOpen = true"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-white border border-slate-200 text-[14px] font-semibold text-[#0B2454] shadow-xs hover:bg-slate-50 transition"
            >
                <svg width="16" height="16" class="h-4 w-4 text-[#0B2454]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filters</span>
            </button>

            <!-- Mobile Sort Trigger -->
            <div class="relative">
                <select
                    x-model="sortBy"
                    @change="changeSort($event.target.value)"
                    class="appearance-none py-2.5 pl-4 pr-9 rounded-xl bg-white border border-slate-200 text-[14px] font-semibold text-[#0B2454] shadow-xs focus:outline-none"
                >
                    <option value="relevance">Relevance</option>
                    <option value="newest">Newest First</option>
                    <option value="title_asc">Title (A-Z)</option>
                    <option value="year_desc">Year (New to Old)</option>
                </select>
                <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Mobile Filter Drawer Modal -->
        <div
            x-show="mobileFilterOpen"
            style="display: none;"
            class="fixed inset-0 z-50 lg:hidden"
        >
            <div
                x-show="mobileFilterOpen"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="mobileFilterOpen = false"
                class="fixed inset-0 bg-black/50 backdrop-blur-xs"
            ></div>

            <div
                x-show="mobileFilterOpen"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative h-full w-[310px] max-w-[85%] bg-white p-5 shadow-2xl overflow-y-auto"
            >
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <span class="font-bold text-[#0B2454] text-[16px]">Catalog Filters</span>
                    <button
                        type="button"
                        @click="mobileFilterOpen = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <x-home.opac-filter
                    :availabilities="$availabilities"
                    :resourceTypes="$resourceTypes"
                    :subjects="$subjects"
                    :selectedAvailabilities="$selectedAvailabilities"
                    :selectedType="$selectedType"
                    :selectedSubjects="$selectedSubjects"
                    :yearFrom="$yearFrom"
                    :yearTo="$yearTo"
                    :search="$search"
                    formId="mobileOpacFilterForm"
                />
            </div>
        </div>

        <!-- Desktop Two-Column Layout: Sidebar (~270px) + Results Workspace (remaining) -->
        <div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">

            <!-- Left: Filter Sidebar (Desktop) -->
            <div class="hidden lg:block w-[270px] shrink-0 sticky top-[96px]">
                <x-home.opac-filter
                    :availabilities="$availabilities"
                    :resourceTypes="$resourceTypes"
                    :subjects="$subjects"
                    :selectedAvailabilities="$selectedAvailabilities"
                    :selectedType="$selectedType"
                    :selectedSubjects="$selectedSubjects"
                    :yearFrom="$yearFrom"
                    :yearTo="$yearTo"
                    :search="$search"
                    formId="desktopOpacFilterForm"
                />
            </div>

            <!-- Right: Results Main Panel -->
            <div class="flex-1 w-full min-w-0">

                <!-- 7. Results Header: Count & Sort Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-[17px] sm:text-[18px] font-extrabold text-[#0B2454] tracking-tight">
                            <span>{{ $totalCount }}</span>
                            @if (!empty($searchTerm))
                                <span>results for</span> <span class="text-[#0B2454]">"{{ $searchTerm }}"</span>
                            @else
                                <span>results in Catalog</span>
                            @endif
                        </h2>
                    </div>

                    <!-- Sort Control (Desktop) -->
                    <div class="hidden sm:flex items-center gap-2 text-[13.5px]">
                        <span class="text-slate-500 font-medium">Sort by:</span>
                        <div class="relative">
                            <select
                                x-model="sortBy"
                                @change="changeSort($event.target.value)"
                                class="appearance-none py-2 pl-3.5 pr-8 rounded-xl bg-white border border-slate-200 text-[13.5px] font-semibold text-[#0B2454] shadow-xs hover:border-slate-300 focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 cursor-pointer"
                            >
                                <option value="relevance">Relevance</option>
                                <option value="newest">Newest First</option>
                                <option value="title_asc">Title (A-Z)</option>
                                <option value="year_desc">Year (New to Old)</option>
                            </select>
                            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ================= SKELETON LOADER STATE ================= -->
                <div x-show="isLoading" class="space-y-4" aria-hidden="true">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs animate-pulse">
                            <div class="flex flex-col sm:flex-row items-start gap-5">
                                <!-- Cover Skeleton -->
                                <div class="w-[110px] sm:w-[120px] h-[155px] sm:h-[170px] shrink-0 rounded-xl bg-slate-200/80"></div>

                                <!-- Middle Info Skeleton -->
                                <div class="flex-1 w-full space-y-3">
                                    <!-- Title Skeleton -->
                                    <div class="h-5 bg-slate-200/90 rounded-md w-3/4"></div>
                                    <div class="h-4 bg-slate-200/60 rounded-md w-1/3"></div>

                                    <!-- Metadata line Skeleton -->
                                    <div class="flex items-center gap-3 pt-2">
                                        <div class="h-3.5 bg-slate-200/70 rounded-md w-16"></div>
                                        <div class="h-3.5 bg-slate-200/70 rounded-md w-16"></div>
                                        <div class="h-3.5 bg-slate-200/70 rounded-md w-20"></div>
                                    </div>

                                    <!-- Call No / Location Skeleton -->
                                    <div class="pt-4 border-t border-slate-100 space-y-2">
                                        <div class="h-3.5 bg-slate-200/60 rounded-md w-44"></div>
                                        <div class="h-3.5 bg-slate-200/60 rounded-md w-56"></div>
                                    </div>
                                </div>

                                <!-- Right Actions Skeleton -->
                                <div class="w-full sm:w-48 flex sm:flex-col items-start sm:items-end justify-between sm:justify-start gap-4 shrink-0 sm:self-stretch">
                                    <div class="space-y-1.5 sm:text-right w-28">
                                        <div class="h-4 bg-slate-200/80 rounded-full w-20 sm:ml-auto"></div>
                                        <div class="h-3 bg-slate-200/50 rounded-md w-24 sm:ml-auto"></div>
                                    </div>

                                    <div class="flex items-center gap-2 mt-auto">
                                        <div class="h-9 w-24 bg-slate-200/80 rounded-xl"></div>
                                        <div class="h-9 w-20 bg-slate-200/80 rounded-xl"></div>
                                        <div class="h-9 w-9 bg-slate-200/80 rounded-xl"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- ================= REAL RESULTS LIST ================= -->
                <div
                    x-show="!isLoading"
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    style="display: none;"
                    class="space-y-4"
                >
                    @forelse ($results as $book)
                        <!-- Book Result Card -->
                        <article
                            x-data="{ bookmarked: false }"
                            class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs hover:border-slate-300 transition-all duration-150"
                        >
                            <div class="flex flex-col sm:flex-row items-start gap-5">

                                <!-- 9. Book Cover (110–125px wide × 150–175px high) -->
                                <div class="w-[110px] sm:w-[120px] h-[155px] sm:h-[170px] shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shadow-xs relative">
                                    @if (!empty($book['cover']))
                                        <img
                                            src="{{ $book['cover'] }}"
                                            alt="{{ $book['title'] }}"
                                            loading="lazy"
                                            decoding="async"
                                            class="h-full w-full object-cover"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                    @endif
                                    <!-- Fallback Clean Cover Placeholder -->
                                    <div style="{{ !empty($book['cover']) ? 'display: none;' : 'display: flex;' }}" class="h-full w-full flex-col items-center justify-center p-3 text-center bg-slate-100 text-slate-400">
                                        <svg width="32" height="32" class="h-8 w-8 mb-1.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Book Cover</span>
                                    </div>
                                </div>

                                <!-- Middle: Book Metadata & Information Hierarchy -->
                                <div class="flex-1 min-w-0 pr-0 sm:pr-2">

                                    <!-- Level 1: Book Title -->
                                    <h3 class="text-[16px] sm:text-[17.5px] font-bold text-[#0B2454] leading-snug tracking-tight">
                                        <a
                                            href="{{ route('opac.index', ['view' => $book['id']]) }}"
                                            class="hover:text-[#3B82F6] transition-colors"
                                        >
                                            {{ $book['title'] }}
                                        </a>
                                    </h3>

                                    <!-- Level 2: Author -->
                                    <p class="mt-1 text-[13.5px] sm:text-[14px] font-medium text-slate-600">
                                        {{ $book['author'] }}
                                    </p>

                                    <!-- Level 3: Year · Format · Pages -->
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[12.5px] sm:text-[13px] text-slate-500 font-medium">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $book['year'] }}</span>
                                        </span>

                                        <span class="text-slate-300">•</span>

                                        <span class="inline-flex items-center gap-1.5">
                                            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <span>{{ $book['format'] }}</span>
                                        </span>

                                        <span class="text-slate-300">•</span>

                                        <span class="inline-flex items-center gap-1.5">
                                            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>{{ $book['pages'] }}</span>
                                        </span>
                                    </div>

                                    <!-- Level 4: Call Number & Location -->
                                    <div class="mt-4 pt-3 border-t border-slate-100 space-y-1 text-[12.5px] sm:text-[13px]">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-700 w-16 shrink-0">Call No.</span>
                                            <span class="font-semibold text-slate-800 font-mono text-[12px] sm:text-[12.5px]">{{ $book['call_no'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-700 w-16 shrink-0">Location</span>
                                            <span class="text-slate-600">{{ $book['location'] }}</span>
                                        </div>
                                    </div>

                                </div>

                                <!-- Right Side: Availability Badge & Actions -->
                                <div class="w-full sm:w-auto flex sm:flex-col items-start sm:items-end justify-between sm:justify-start gap-4 shrink-0 sm:self-stretch pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">

                                    <!-- Availability Status -->
                                    <div class="text-left sm:text-right">
                                        <div class="inline-flex items-center gap-1.5 font-bold text-[13px] {{ $book['status_color'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $book['dot_color'] }} shrink-0"></span>
                                            <span>{{ $book['status_label'] }}</span>
                                        </div>

                                        <!-- Checkout & Accession Details (Role / Logged-in Conditional) -->
                                        @if ($isLoggedIn)
                                            @if (!empty($book['due_date']))
                                                <p class="text-[11.5px] text-rose-600 mt-0.5 font-semibold">
                                                    {{ $book['due_date'] }}
                                                </p>
                                            @elseif (!empty($book['pickup_date']))
                                                <p class="text-[11.5px] text-amber-700 mt-0.5 font-semibold">
                                                    {{ $book['pickup_date'] }}
                                                </p>
                                            @endif

                                            <p class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                                Accession No. {{ $book['accession_no'] }}
                                            </p>
                                        @else
                                            <!-- Subtle hint for guests -->
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                Sign in for copy details
                                            </p>
                                        @endif
                                    </div>

                                    <!-- 11. Action Buttons Hierarchy: [ View Details ] [ Reserve ] [ ♡ ] -->
                                    <div class="flex items-center gap-2 mt-auto">
                                        <!-- Secondary: View Details (Always visible) -->
                                        <a
                                            href="{{ route('opac.index', ['view' => $book['id']]) }}"
                                            class="inline-flex items-center justify-center py-2 px-3.5 rounded-xl border border-slate-300 bg-white text-[13px] font-semibold text-[#0B2454] shadow-2xs hover:bg-slate-50 hover:border-[#0B2454] transition-all cursor-pointer whitespace-nowrap"
                                        >
                                            View Details
                                        </a>

                                        <!-- Primary: Reserve Button (Determined by login state!) -->
                                        @if ($isLoggedIn)
                                            @if ($book['can_reserve'])
                                                <!-- Logged-in & Available: Active Gold Reserve Button -->
                                                <button
                                                    type="button"
                                                    @click="openReserve({{ json_encode($book) }})"
                                                    class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-xl bg-[#F9C000] text-[13px] font-bold text-[#071A3D] shadow-xs hover:bg-[#e6b000] active:scale-95 transition-all cursor-pointer whitespace-nowrap"
                                                >
                                                    <svg width="14" height="14" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                    </svg>
                                                    <span>Reserve</span>
                                                </button>
                                            @endif
                                        @else
                                            <!-- Not Logged In (Guest): Sign in to Reserve Gateway -->
                                            <a
                                                href="{{ route('login') }}"
                                                class="inline-flex items-center gap-1.5 py-2 px-3 rounded-xl border border-amber-300/80 bg-amber-50/80 text-[12.5px] font-bold text-[#0B2454] hover:bg-amber-100 transition-all shadow-2xs whitespace-nowrap"
                                                title="Sign in with your account to reserve this resource"
                                            >
                                                <svg width="13" height="13" class="h-3.5 w-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>Sign in to Reserve</span>
                                            </a>
                                        @endif

                                        <!-- Tertiary: Bookmark Heart -->
                                        <button
                                            type="button"
                                            @click="bookmarked = !bookmarked"
                                            class="h-9 w-9 shrink-0 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 hover:text-rose-500 hover:border-rose-200 shadow-2xs transition cursor-pointer"
                                            :class="bookmarked ? 'text-rose-500 border-rose-200 bg-rose-50/50' : ''"
                                            aria-label="Bookmark"
                                        >
                                            <svg width="16" height="16" class="h-4 w-4" :fill="bookmarked ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </button>
                                    </div>

                                </div>

                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-[#0B2454]">No resources found</h3>
                            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                                We couldn't find any resources matching your search or filters. Try adjusting your search query or reset the filters.
                            </p>
                            <a
                                href="{{ route('opac.index') }}"
                                class="mt-4 inline-flex items-center gap-1.5 py-2 px-4 rounded-xl bg-[#0B2454] text-white text-xs font-semibold hover:bg-[#071943] transition"
                            >
                                Reset all filters
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- 13. Pagination Controls -->
                @if ($pagination instanceof \Illuminate\Pagination\LengthAwarePaginator && $pagination->hasPages())
                    <div class="mt-8 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">

                        <!-- Results Range Note -->
                        <div class="text-[13px] text-slate-500 font-medium">
                            Showing <span class="font-bold text-[#0B2454]">{{ $pagination->firstItem() }}</span> to <span class="font-bold text-[#0B2454]">{{ $pagination->lastItem() }}</span> of <span class="font-bold text-[#0B2454]">{{ $pagination->total() }}</span> results
                        </div>

                        <!-- Page Numbers -->
                        <nav class="inline-flex items-center gap-1 text-[13px] font-semibold" aria-label="Pagination">
                            <!-- Prev -->
                            @if ($pagination->onFirstPage())
                                <span class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center cursor-not-allowed">
                                    <svg width="16" height="16" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </span>
                            @else
                                <a
                                    href="{{ $pagination->previousPageUrl() }}"
                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 flex items-center justify-center transition"
                                >
                                    <svg width="16" height="16" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>
                            @endif

                            @foreach ($pagination->getUrlRange(1, min(5, $pagination->lastPage())) as $page => $url)
                                @if ($page == $pagination->currentPage())
                                    <span class="h-9 w-9 rounded-xl bg-[#0B2454] text-white flex items-center justify-center font-bold shadow-xs">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a
                                        href="{{ $url }}"
                                        class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 flex items-center justify-center transition"
                                    >
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            <!-- Next -->
                            @if ($pagination->hasMorePages())
                                <a
                                    href="{{ $pagination->nextPageUrl() }}"
                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 flex items-center justify-center transition"
                                >
                                    <svg width="16" height="16" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                                <span class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-300 flex items-center justify-center cursor-not-allowed">
                                    <svg width="16" height="16" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            @endif
                        </nav>

                        <!-- Results Per Page -->
                        <div class="hidden sm:flex items-center gap-2 text-[13px] text-slate-500">
                            <span>Results per page:</span>
                            <div class="relative">
                                <select
                                    onchange="const u = new URL(window.location.href); u.searchParams.set('per_page', this.value); window.location.href = u.toString();"
                                    class="appearance-none py-1.5 pl-3 pr-7 rounded-lg border border-slate-200 bg-white text-[13px] font-semibold text-[#0B2454] focus:outline-none cursor-pointer"
                                >
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <svg width="12" height="12" class="h-3 w-3 text-slate-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                    </div>
                @endif

            </div>

        </div>

    </div>

    <!-- ================= RESERVATION MODAL (LOGGED IN ONLY) ================= -->
    <template x-if="reservationModalOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div
                @click="reservationModalOpen = false"
                class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
            ></div>

            <!-- Modal Window -->
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl z-10 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-lg font-bold text-[#0B2454] flex items-center gap-2">
                        <svg width="20" height="20" class="h-5 w-5 text-[#F9C000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <span>Confirm Book Reservation</span>
                    </h3>
                    <button
                        type="button"
                        @click="reservationModalOpen = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Book Preview -->
                <div class="mb-4 rounded-xl bg-slate-50 p-3.5 border border-slate-200/80">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Resource</p>
                    <p class="text-[14.5px] font-bold text-[#0B2454] mt-0.5 leading-snug" x-text="selectedBook?.title"></p>
                    <p class="text-[13px] text-slate-600 mt-1" x-text="selectedBook?.author"></p>
                    <div class="mt-2 flex items-center gap-4 text-xs text-slate-500 font-mono">
                        <span>Call: <strong x-text="selectedBook?.call_no"></strong></span>
                        <span>Accession: <strong x-text="selectedBook?.accession_no"></strong></span>
                    </div>
                </div>

                <!-- Policy Information -->
                <div class="mb-5 rounded-xl bg-amber-50/70 p-3.5 border border-amber-200/70 text-[12.5px] text-amber-900 space-y-1.5">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Library Reservation Policy</span>
                    </p>
                    <p class="leading-relaxed">
                        Reserved items will be placed on hold at the <strong>Circulation Desk</strong> for <strong>3 school days</strong>. If not claimed within this period, the reservation will automatically expire.
                    </p>
                </div>

                <!-- Form to Submit Reservation -->
                <form :action="'{{ url('opac/reserve') }}/' + (selectedBook?.id || '')" method="POST" class="flex items-center justify-end gap-3">
                    @csrf
                    <button
                        type="button"
                        @click="reservationModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl bg-[#F9C000] text-[#071A3D] hover:bg-[#e6b000] font-bold text-xs shadow-xs transition active:scale-95"
                    >
                        Confirm Reservation
                    </button>
                </form>
            </div>
        </div>
    </template>
</section>
