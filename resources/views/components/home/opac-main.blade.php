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
    'perPage' => 5,
    'availabilities' => [],
    'resourceTypes' => [],
    'subjects' => [],
    'isLoggedIn' => auth()->check(),
    'currentUser' => auth()->user(),
])

@php
    $searchTerm = $search ?: request('search', '');

    // Fix: $results may be a Laravel Collection on initial page load —
    // normalize to a plain array so @json() and count() are consistent.
    $resultsArray = is_array($results) ? $results
        : (method_exists($results, 'values') ? $results->values()->toArray() : (array) $results);

    $totalCount = (int) $totalResults ?: count($resultsArray);

    $paginationData = null;
    if ($pagination instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $paginationData = [
            'currentPage'  => $pagination->currentPage(),
            'lastPage'     => $pagination->lastPage(),
            'hasMorePages' => $pagination->hasMorePages(),
            'total'        => $pagination->total(),   // use paginator directly, not the prop
            'perPage'      => $pagination->perPage(),
            'nextUrl'      => $pagination->nextPageUrl(),
            'prevUrl'      => $pagination->previousPageUrl(),
            'links'        => $pagination->linkCollection()->toArray(),
        ];
    }
@endphp

<script>
function opacCatalog() {
    return {
        results: @json($resultsArray),
        totalCount: {{ (int) $totalCount }},
        searchTerm: @json($searchTerm),
        isLoading: false,
        mobileFilterOpen: false,
        reservationModalOpen: false,
        selectedBook: null,
        reserveStatus: null,
        sortBy: @json($sortBy),
        perPage: {{ (int) $perPage }},
        pagination: @json($paginationData),
        isLoggedIn: {{ $isLoggedIn ? 'true' : 'false' }},

        init() {
            window.addEventListener('popstate', () => {
                this.fetchResults(window.location.href, false);
            });
        },
        openReserve(book) {
            this.selectedBook = book;
            this.reserveStatus = null;
            this.reservationModalOpen = true;
        },
        async fetchResults(url, updateHistory = true) {
            this.isLoading = true;
            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Alpine-Request': 'true'
                    }
                });
                if (!res.ok) throw new Error('Network response not ok');
                const data = await res.json();
                this.results                = data.results || [];
                this.totalCount             = data.totalResults || 0;
                this.pagination             = data.pagination  || null;
                this.searchTerm             = data.search      || '';
                this.sortBy                 = data.sortBy      || this.sortBy;
                this.perPage                = parseInt(data.perPage, 10) || this.perPage;
                // Sync filter selection state from AJAX response so back/forward
                // navigation (popstate) correctly reflects the current filter state.
                if (Array.isArray(data.selectedAvailabilities)) {
                    this.selectedAvailabilities = data.selectedAvailabilities;
                }
                if (Array.isArray(data.selectedSubjects)) {
                    this.selectedSubjects = data.selectedSubjects.map(Number);
                }
                if (updateHistory) {
                    window.history.pushState(null, '', url);
                }
                window.dispatchEvent(new CustomEvent('opac-updated', { detail: data }));
            } catch (err) {
                console.error('Failed to load OPAC results:', err);
            } finally {
                setTimeout(() => {
                    this.isLoading = false;
                }, 200);
            }
        },
        changePerPage(val) {
            // Fix: coerce to int — $event.target.value is always a string
            this.perPage = parseInt(val, 10);
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            this.fetchResults(url.toString());
        },
        changeSort(newSort) {
            this.sortBy = newSort;
            const url = new URL(window.location.href);
            url.searchParams.set('sort', newSort);
            url.searchParams.delete('page');
            this.fetchResults(url.toString());
        },
        handleSearch(detail) {
            const url = new URL('{{ route('opac.index') }}', window.location.origin);
            if (detail.search) url.searchParams.set('search', detail.search);
            if (detail.type && detail.type !== 'all') url.searchParams.set('type', detail.type);
            // Preserve current per_page and sort so the user's preferences carry over
            if (this.perPage && this.perPage !== 5) url.searchParams.set('per_page', this.perPage);
            if (this.sortBy && this.sortBy !== 'relevance') url.searchParams.set('sort', this.sortBy);
            this.fetchResults(url.toString());
            this.scrollToTarget(true);
        },
        handleFilter(formData) {
            const url = new URL('{{ route('opac.index') }}', window.location.origin);
            const currentUrl = new URL(window.location.href);
            if (currentUrl.searchParams.has('search') && !formData.has('search')) {
                url.searchParams.set('search', currentUrl.searchParams.get('search'));
            }
            for (const [key, value] of formData.entries()) {
                if (value) {
                    // Fix: strip the trailing [] from the param name before appending.
                    // url.searchParams.append('availability[]', 'available') sends the
                    // literal key 'availability[]' which PHP does NOT parse as an array.
                    // We must send 'availability' (no brackets) with multiple appends instead.
                    const cleanKey = key.endsWith('[]') ? key.slice(0, -2) : key;
                    if (key.endsWith('[]')) {
                        url.searchParams.append(cleanKey, value);
                    } else {
                        url.searchParams.set(cleanKey, value);
                    }
                }
            }
            if (this.sortBy) url.searchParams.set('sort', this.sortBy);
            this.mobileFilterOpen = false;
            this.fetchResults(url.toString());
        },
        resetAllFilters() {
            const url = new URL('{{ route('opac.index') }}', window.location.origin);
            const currentUrl = new URL(window.location.href);
            if (currentUrl.searchParams.has('search')) {
                url.searchParams.set('search', currentUrl.searchParams.get('search'));
            }
            // Fix: also reset Alpine state to match the cleared URL so the
            // sort/perPage controls don't stay in a stale state after a reset.
            this.sortBy                 = 'relevance';
            this.perPage                = 5;
            this.selectedAvailabilities = [];
            this.selectedSubjects       = [];
            this.mobileFilterOpen       = false;
            this.fetchResults(url.toString());
        },
        goToPage(pageUrl) {
            if (!pageUrl) return;
            this.fetchResults(pageUrl);
            this.scrollToTarget(false);
        },
        scrollToTarget(toSearchBar = false) {
            const navbar = document.querySelector('header');
            const navHeight = navbar ? navbar.offsetHeight : 76;

            let targetElement = this.$el;
            if (toSearchBar) {
                const searchForm = document.getElementById('opacHeroSearchForm');
                if (searchForm) {
                    targetElement = searchForm;
                }
            }

            const targetY = window.pageYOffset + targetElement.getBoundingClientRect().top - navHeight - 24;

            window.scrollTo({
                top: Math.max(0, Math.round(targetY)),
                behavior: 'smooth'
            });
        }
    };
}
</script>

<!-- Results Area (Light background: #F8FAFC) -->
<section
    x-data="opacCatalog()"
    @opac-search-trigger.window="handleSearch($event.detail)"
    @opac-filter-trigger.window="handleFilter($event.detail)"
    @opac-filter-reset.window="resetAllFilters()"
    class="relative w-full bg-[#F8FAFC] py-8 sm:py-10 lg:py-12 select-none min-h-[600px] scroll-mt-28"
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
            <div class="hidden lg:block w-[270px] shrink-0">
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
                        <h2 class="text-[17px] sm:text-[18px] font-extrabold text-[#0B2454] tracking-tight flex items-center gap-1.5 flex-wrap">
                            <span x-text="totalCount"></span>
                            <template x-if="searchTerm">
                                <span>results for "<span class="text-[#0B2454]" x-text="searchTerm"></span>"</span>
                            </template>
                            <template x-if="!searchTerm">
                                <span>results in Catalog</span>
                            </template>
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
                    <template x-for="book in results" :key="book.id">
                        <x-home.book-query-card :is-logged-in="$isLoggedIn" />
                    </template>

                    <!-- Empty State -->
                    <div
                        x-show="!isLoading && results.length === 0"
                        class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center"
                    >
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-[#0B2454]">No resources found</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                            We couldn't find any resources matching your search or filters. Try adjusting your search query or reset the filters.
                        </p>
                        <button
                            type="button"
                            @click="resetAllFilters()"
                            class="mt-4 inline-flex items-center gap-1.5 py-2 px-4 rounded-xl bg-[#0B2454] text-white text-xs font-semibold hover:bg-[#071943] transition cursor-pointer"
                        >
                            Reset all filters
                        </button>
                    </div>
                </div>

                <!-- 13. Pagination Controls (Alpine Dynamic) -->
                <div
                    x-show="!isLoading && results && results.length > 0"
                    class="mt-8 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4"
                >
                    <!-- Left: Results Range Note -->
                    <div class="text-[13px] text-slate-500 font-medium">
                        Showing page <span class="font-bold text-[#102B70]" x-text="pagination ? pagination.currentPage : 1"></span> of <span class="font-bold text-[#102B70]" x-text="pagination ? pagination.lastPage : 1"></span> (<span class="font-bold text-[#102B70]" x-text="totalCount"></span> total results)
                    </div>

                    <!-- Center: Page Numbers -->
                    <nav class="inline-flex items-center gap-1.5 text-[13px] font-semibold" aria-label="Pagination">
                        <!-- Prev -->
                        <button
                            type="button"
                            :disabled="!pagination || pagination.currentPage <= 1"
                            @click="goToPage(pagination ? pagination.prevUrl : null)"
                            class="h-9 px-3 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 flex items-center justify-center gap-1 transition shadow-2xs disabled:opacity-30 disabled:pointer-events-none cursor-pointer"
                            title="Previous page"
                        >
                            <svg width="15" height="15" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden sm:inline text-xs font-semibold">Prev</span>
                        </button>

                        <!-- Dynamic Page Number Buttons -->
                        <template x-if="pagination && pagination.links && pagination.links.length > 3">
                            <div class="inline-flex items-center gap-1">
                                <template x-for="(link, idx) in pagination.links" :key="idx">
                                    <template x-if="!isNaN(link.label)">
                                        <button
                                            type="button"
                                            @click="goToPage(link.url)"
                                            class="h-9 w-9 rounded-xl flex items-center justify-center text-xs font-bold transition shadow-2xs cursor-pointer"
                                            :class="link.active ? 'bg-[#102B70] text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300'"
                                            x-text="link.label"
                                        ></button>
                                    </template>
                                </template>
                            </div>
                        </template>

                        <!-- Fallback single page button if 1 page -->
                        <template x-if="!pagination || !pagination.links || pagination.links.length <= 3">
                            <button
                                type="button"
                                class="h-9 w-9 rounded-xl flex items-center justify-center text-xs font-bold bg-[#102B70] text-white shadow-xs"
                            >
                                1
                            </button>
                        </template>

                        <!-- Next -->
                        <button
                            type="button"
                            :disabled="!pagination || !pagination.hasMorePages"
                            @click="goToPage(pagination ? pagination.nextUrl : null)"
                            class="h-9 px-3 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 flex items-center justify-center gap-1 transition shadow-2xs disabled:opacity-30 disabled:pointer-events-none cursor-pointer"
                            title="Next page"
                        >
                            <span class="hidden sm:inline text-xs font-semibold">Next</span>
                            <svg width="15" height="15" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>

                    <!-- Right: Per-Page Selector -->
                    <div class="flex items-center gap-2 text-[12.5px] text-slate-500 font-medium">
                        <span>Per page:</span>
                        <div class="relative">
                            <select
                                :value="perPage"
                                @change="changePerPage($event.target.value)"
                                class="appearance-none py-1.5 pl-3 pr-7 rounded-xl border border-slate-200 bg-white text-[12.5px] font-bold text-[#102B70] focus:outline-none focus:ring-1 focus:ring-[#102B70] shadow-2xs cursor-pointer"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

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
