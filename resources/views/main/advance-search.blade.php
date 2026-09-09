<x-layouts.home active="opac">
    <!-- Advanced Search Canvas -->
    <div class="min-h-[calc(100vh-140px)] bg-[#F8FAFC] py-8 sm:py-10 lg:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-[1380px] mx-auto w-full space-y-10">

            <!-- 1. The Advanced Search Card -->
            <x-home.advance-search-card :has-results="!empty($hasSearched)" />

            <!-- 2. Results Workspace (Displayed whenever an advanced search is performed) -->
            @if(!empty($hasSearched))
                @php
                    $criteriaList = [];
                    if (!empty($title)) $criteriaList[] = ['label' => 'Title', 'value' => $title];
                    if (!empty($author)) $criteriaList[] = ['label' => 'Author', 'value' => $author];
                    if (!empty($subject)) $criteriaList[] = ['label' => 'Subject', 'value' => $subject];
                    if (!empty($isbn)) $criteriaList[] = ['label' => 'ISBN', 'value' => $isbn];
                    if (!empty($selectedType) && $selectedType !== 'all') $criteriaList[] = ['label' => 'Type', 'value' => ucfirst($selectedType)];
                    if (!empty($selectedAvailabilities)) $criteriaList[] = ['label' => 'Status', 'value' => implode(', ', array_map('ucfirst', $selectedAvailabilities))];
                    if (!empty($yearFrom) || !empty($yearTo)) $criteriaList[] = ['label' => 'Year', 'value' => ($yearFrom ?: '...') . ' - ' . ($yearTo ?: '...')];
                    if (!empty($location) && $location !== 'all') $criteriaList[] = ['label' => 'Location', 'value' => $location];
                    if (!empty($matchType)) $criteriaList[] = ['label' => 'Match', 'value' => $matchType === 'all' ? 'All criteria' : 'Any criteria'];
                @endphp

                <script>
                function advanceSearchResults() {
                    return {
                        results: @json($items ?? []),
                        totalCount: {{ (int) ($totalResults ?? 0) }},
                        pagination: @json($paginationData ?? null),
                        isLoading: false,
                        reservationModalOpen: false,
                        selectedBook: null,
                        reserveStatus: null,
                        perPage: {{ (int) ($perPage ?? 5) }},
                        sortBy: @json($sortBy ?? 'relevance'),
                        isLoggedIn: {{ !empty($isLoggedIn) ? 'true' : 'false' }},

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
                                if (!res.ok) throw new Error('Network response error');
                                const data = await res.json();
                                this.results = data.results || [];
                                this.totalCount = data.totalResults || 0;
                                this.pagination = data.pagination || null;
                                this.perPage = data.perPage || this.perPage;
                                if (updateHistory) {
                                    window.history.pushState(null, '', url);
                                }
                            } catch (err) {
                                console.error('Failed to load results:', err);
                            } finally {
                                setTimeout(() => {
                                    this.isLoading = false;
                                }, 150);
                            }
                        },
                        goToPage(pageUrl) {
                            if (!pageUrl) return;
                            this.fetchResults(pageUrl);
                            const resultsTop = this.$el.getBoundingClientRect().top + window.pageYOffset - 90;
                            window.scrollTo({ top: Math.max(0, resultsTop), behavior: 'smooth' });
                        },
                        changePerPage(val) {
                            this.perPage = val;
                            const url = new URL(window.location.href);
                            url.searchParams.set('per_page', val);
                            url.searchParams.delete('page');
                            this.fetchResults(url.toString());
                        },
                        openReserve(book) {
                            this.selectedBook = book;
                            this.reserveStatus = null;
                            this.reservationModalOpen = true;
                        },
                        async submitReservation() {
                            if (!this.selectedBook) return;
                            this.reserveStatus = 'loading';
                            try {
                                const res = await fetch(`/opac/reserve/${this.selectedBook.id}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await res.json();
                                if (res.ok && data.success) {
                                    this.reserveStatus = 'success';
                                    if (this.selectedBook) {
                                        this.selectedBook.status = 'reserved';
                                        this.selectedBook.status_label = 'Reserved';
                                        this.selectedBook.status_color = 'text-amber-600';
                                        this.selectedBook.dot_color = 'bg-amber-500';
                                        this.selectedBook.can_reserve = false;
                                    }
                                } else {
                                    this.reserveStatus = 'error';
                                    alert(data.message || 'Could not place reservation.');
                                }
                            } catch (err) {
                                this.reserveStatus = 'error';
                                alert('An error occurred. Please try again.');
                            }
                        }
                    };
                }
                </script>

                <div
                    x-data="advanceSearchResults()"
                    class="rounded-[14px] border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.06)]"
                >
                    <!-- Header Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-xl sm:text-2xl font-bold text-[#0B2454] tracking-tight">
                                    Search Results
                                </h3>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                                    <span x-text="totalCount">{{ $totalResults }}</span>
                                    <span>found</span>
                                </span>
                            </div>
                            <p class="text-[13.5px] text-slate-500 mt-1">
                                Matching resources based on your specified advanced criteria.
                            </p>
                        </div>

                    </div>

                    <!-- Active Filter Badges -->
                    @if(!empty($criteriaList))
                        <div class="flex flex-wrap items-center gap-2 py-4 border-b border-slate-100 text-xs">
                            <span class="text-slate-400 font-semibold uppercase tracking-wider text-[11px] mr-1">
                                Criteria:
                            </span>
                            @foreach($criteriaList as $crit)
                                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-md bg-[#F8FAFC] border border-[#D7E0EC] text-[#132B53] font-medium">
                                    <span class="text-slate-400 font-semibold">{{ $crit['label'] }}:</span>
                                    <span>{{ $crit['value'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Preloader / Skeletal Pulse -->
                    <div x-show="isLoading" class="space-y-4 pt-6" style="display: none;">
                        <div class="animate-pulse flex flex-col md:flex-row gap-5 p-6 rounded-xl border border-slate-100 bg-slate-50/60">
                            <div class="h-36 w-28 bg-slate-200 rounded-lg shrink-0"></div>
                            <div class="flex-1 space-y-3">
                                <div class="h-5 bg-slate-200 rounded w-3/4"></div>
                                <div class="h-4 bg-slate-200 rounded w-1/3"></div>
                                <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Results List -->
                    <div x-show="!isLoading" class="space-y-4 pt-6">
                        <template x-for="book in results" :key="book.id">
                            <x-home.book-query-card :is-logged-in="$isLoggedIn" />
                        </template>

                        <!-- Empty State -->
                        <div
                            x-show="results.length === 0"
                            class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-12 text-center my-4"
                        >
                            <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center text-slate-400 mb-3 shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-[#0B2454]">No resources found</h4>
                            <p class="text-[13.5px] text-slate-500 mt-1 max-w-sm mx-auto">
                                We couldn't find any resources matching your criteria. Try loosening your terms or switching to "Match any criteria".
                            </p>
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div
                        x-show="!isLoading && results && results.length > 0"
                        class="mt-8 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4"
                    >
                        <!-- Results Range Note -->
                        <div class="text-[13px] text-slate-500 font-medium">
                            Showing page <span class="font-bold text-[#102B70]" x-text="pagination ? pagination.currentPage : 1"></span> of <span class="font-bold text-[#102B70]" x-text="pagination ? pagination.lastPage : 1"></span> (<span class="font-bold text-[#102B70]" x-text="totalCount"></span> total results)
                        </div>

                        <!-- Page Navigation Buttons -->
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

                            <!-- Dynamic Numbers -->
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

                        <!-- Per-Page Selector -->
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
                                <svg width="12" height="12" class="h-3 w-3 text-slate-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Modal (Logged in users) -->
                    <template x-if="reservationModalOpen">
                        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div
                                @click="reservationModalOpen = false"
                                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                            ></div>

                            <div
                                class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100"
                                @click.stop
                            >
                                <h4 class="text-lg font-bold text-[#0B2454] mb-2">
                                    Confirm Reservation
                                </h4>
                                <p class="text-sm text-slate-600 mb-4">
                                    Are you sure you want to place a hold on
                                    <span class="font-bold text-[#0B2454]" x-text="selectedBook ? selectedBook.title : ''"></span>?
                                </p>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="reservationModalOpen = false"
                                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="button"
                                        @click="submitReservation()"
                                        :disabled="reserveStatus === 'loading'"
                                        class="px-5 py-2 rounded-xl bg-[#102B70] text-xs font-bold text-white hover:bg-[#0B225E] transition shadow-xs cursor-pointer flex items-center gap-1.5"
                                    >
                                        <span x-show="reserveStatus !== 'loading'">Confirm Hold</span>
                                        <span x-show="reserveStatus === 'loading'">Processing...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @endif

        </div>
    </div>
</x-layouts.home>
