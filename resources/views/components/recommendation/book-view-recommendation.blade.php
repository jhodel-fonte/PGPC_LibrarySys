@props([
    'title' => null,
    'subtitle' => null,
    'category' => null,
    'books' => [],
    'endpoint' => null,
    'limit' => 10,
    'viewAllUrl' => null,
    'class' => '',
])

@php
    $initialBooks = is_array($books) ? $books : (is_object($books) && method_exists($books, 'toArray') ? $books->toArray() : []);
    $hasEndpoint = !empty($endpoint);
    $initialLoading = $hasEndpoint && empty($initialBooks);

    // Determine primary category for badge & heading
    $activeCategory = $category;
    if (empty($activeCategory) && !empty($initialBooks)) {
        $firstBook = reset($initialBooks);
        $activeCategory = $firstBook['category'] ?? ($firstBook['categories'][0] ?? null);
    }
    if ($activeCategory === 'General Collection' || $activeCategory === 'General') {
        foreach ($initialBooks as $b) {
            $cat = $b['category'] ?? null;
            if ($cat && !in_array(strtolower($cat), ['general', 'general collection', 'n/a'])) {
                $activeCategory = $cat;
                break;
            }
        }
    }

    $hasSpecificCat = !empty($activeCategory) && !in_array(strtolower($activeCategory), ['general', 'general collection', 'n/a']);
    $finalTitle = $title ?? ($hasSpecificCat ? "Recommended Books" : "Related Books");
    $finalSubtitle = $subtitle ?? ($hasSpecificCat ? "Explore other recommended cataloged resources under {$activeCategory}" : "Explore related resources from the library collection");

    if (empty($viewAllUrl) && $hasSpecificCat && Route::has('opac.search')) {
        $viewAllUrl = route('opac.search', ['category' => $activeCategory]);
    }
@endphp

<section
    x-data="{
        items: {{ Js::from($initialBooks) }},
        endpoint: {{ Js::from($endpoint) }},
        isLoading: {{ $initialLoading ? 'true' : 'false' }},
        limit: {{ (int) $limit }},
        canScrollLeft: false,
        canScrollRight: false,

        init() {
            if (this.endpoint && (!this.items || this.items.length === 0)) {
                this.fetchData();
            } else {
                this.$nextTick(() => this.updateScrollState());
            }

            // Listen for window resize to update scroll arrow visibility
            window.addEventListener('resize', () => this.updateScrollState());
        },

        async fetchData() {
            this.isLoading = true;
            try {
                const res = await fetch(this.endpoint, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Failed to load recommendations');
                const data = await res.json();
                this.items = Array.isArray(data) ? data : (data.data || data.results || []);
                if (this.limit && this.items.length > this.limit) {
                    this.items = this.items.slice(0, this.limit);
                }
            } catch (err) {
                console.error('Error fetching recommendations:', err);
                this.items = [];
            } finally {
                this.isLoading = false;
                this.$nextTick(() => this.updateScrollState());
            }
        },

        scrollTrack(direction) {
            const track = this.$refs.sliderTrack;
            if (!track) return;
            const scrollAmount = track.clientWidth * 0.75;
            track.scrollBy({
                left: direction === 'left' ? -scrollAmount : scrollAmount,
                behavior: 'smooth'
            });
            setTimeout(() => this.updateScrollState(), 350);
        },

        updateScrollState() {
            const track = this.$refs.sliderTrack;
            if (!track) return;
            this.canScrollLeft = track.scrollLeft > 10;
            this.canScrollRight = track.scrollLeft + track.clientWidth < track.scrollWidth - 10;
        },

        getBookUrl(book) {
            if (!book) return '#';
            if (book.url) return book.url;
            const identifier = (book.accession_no && book.accession_no !== 'N/A') 
                ? encodeURIComponent(book.accession_no) 
                : (book.identifier || book.id);
            return '{{ route('opac.book.detail', '') }}/' + identifier;
        }
    }"
    x-init="init()"
    x-show="isLoading || items.length > 0"
    class="w-full rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 lg:p-7 shadow-[0_2px_12px_rgba(7,26,61,0.04)] select-none {{ $class }}"
>
    <!-- Header: Title, Subtitle, Category Badge, and Navigation Arrows -->
    <div class="flex items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="w-1.5 h-5 rounded-full bg-[#FCC719] shrink-0" aria-hidden="true"></span>
                <h3 class="text-lg sm:text-xl font-extrabold text-[#0B2454] tracking-tight truncate">
                    {{ $finalTitle }}
                </h3>
                @if($hasSpecificCat)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                        <svg class="w-3 h-3 text-[#3B82F6]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate max-w-[150px] sm:max-w-[200px]">{{ $activeCategory }}</span>
                    </span>
                @endif
            </div>
            @if(!empty($finalSubtitle))
                <p class="text-xs sm:text-[13px] text-[#64748B] mt-1 pl-4 truncate">
                    {{ $finalSubtitle }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if(!empty($viewAllUrl))
                <a
                    href="{{ $viewAllUrl }}"
                    class="hidden sm:inline-flex items-center gap-1 text-xs font-bold text-[#102B70] hover:text-[#0B225E] hover:underline mr-2"
                >
                    <span>View all</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif

            <!-- Scroll Left Button -->
            <button
                type="button"
                @click="scrollTrack('left')"
                :disabled="!canScrollLeft"
                :class="canScrollLeft ? 'opacity-100 hover:bg-slate-100 hover:border-slate-300 text-[#0B2454] cursor-pointer' : 'opacity-30 text-slate-400 cursor-not-allowed border-slate-200'"
                class="w-8 h-8 rounded-xl border border-slate-200 bg-white flex items-center justify-center transition shadow-2xs"
                title="Previous books"
                aria-label="Previous books"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Scroll Right Button -->
            <button
                type="button"
                @click="scrollTrack('right')"
                :disabled="!canScrollRight"
                :class="canScrollRight ? 'opacity-100 hover:bg-slate-100 hover:border-slate-300 text-[#0B2454] cursor-pointer' : 'opacity-30 text-slate-400 cursor-not-allowed border-slate-200'"
                class="w-8 h-8 rounded-xl border border-slate-200 bg-white flex items-center justify-center transition shadow-2xs"
                title="Next books"
                aria-label="Next books"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    <!-- 1. Skeleton Loader State (Pulse Animation) -->
    <div
        x-show="isLoading"
        x-transition:enter="transition ease-out duration-200"
        x-transition:leave="transition ease-in duration-150"
        class="flex items-stretch gap-4 sm:gap-5 overflow-hidden py-1"
        aria-hidden="true"
    >
        @for ($i = 0; $i < 6; $i++)
            <div class="w-[145px] sm:w-[165px] shrink-0 flex flex-col">
                <!-- Cover skeleton -->
                <div class="w-full aspect-[2/3] rounded-xl bg-slate-200/80 animate-pulse border border-slate-200 shadow-2xs"></div>
                <!-- Title skeleton lines -->
                <div class="mt-3 space-y-1.5">
                    <div class="h-3.5 bg-slate-200 rounded-md animate-pulse w-11/12"></div>
                    <div class="h-3.5 bg-slate-200 rounded-md animate-pulse w-3/4"></div>
                </div>
                <!-- Author skeleton -->
                <div class="h-3 bg-slate-200/70 rounded-md animate-pulse w-1/2 mt-2"></div>
            </div>
        @endfor
    </div>

    <!-- 2. Actual Content Slider / Row -->
    <div
        x-show="!isLoading && items && items.length > 0"
        x-ref="sliderTrack"
        @scroll.debounce.50ms="updateScrollState()"
        class="flex items-stretch gap-4 sm:gap-5 overflow-x-auto scroll-smooth no-scrollbar py-1 scroll-p-2"
        style="scrollbar-width: none; -ms-overflow-style: none;"
    >
        <template x-for="(book, index) in items" :key="book.id || book.accession_no || index">
            <div
                class="group w-[145px] sm:w-[165px] shrink-0 flex flex-col cursor-pointer"
                @click="window.location.href = getBookUrl(book)"
            >
                <!-- Book Cover Box -->
                <div
                    x-data="{
                        imgLoaded: false,
                        imgError: false,
                        coverUrl: book.cover || book.cover_url || null,
                        fallbackCover: '{{ asset('images/' . config('settings.book_covers.default', 'book-cover.webp')) }}'
                    }"
                    x-init="
                        if (!coverUrl) {
                            imgError = true;
                            imgLoaded = true;
                        } else {
                            $nextTick(() => {
                                const img = $el.querySelector('img.book-rec-img');
                                if (img && img.complete) {
                                    if (img.naturalHeight === 0) {
                                        imgError = true;
                                    } else {
                                        imgLoaded = true;
                                    }
                                }
                            });
                        }
                    "
                    class="relative w-full aspect-[2/3] rounded-xl overflow-hidden bg-slate-100 border border-slate-200/90 shadow-xs transition-all duration-200 group-hover:shadow-md group-hover:-translate-y-0.5 group-hover:border-[#102B70]/40"
                >

                    <!-- Image loading state -->
                    <template x-if="coverUrl">
                        <div class="w-full h-full relative">
                            <div
                                x-show="!imgLoaded && !imgError"
                                class="absolute inset-0 bg-slate-200/80 animate-pulse"
                            ></div>
                            <img
                                :src="coverUrl"
                                :alt="book.title"
                                loading="lazy"
                                decoding="async"
                                x-on:load="imgLoaded = true; imgError = false;"
                                x-on:error="imgError = true; imgLoaded = true;"
                                x-show="!imgError"
                                :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                                class="book-rec-img w-full h-full object-cover transition-opacity duration-300"
                            >
                        </div>
                    </template>

                    <!-- Fallback Cover -->
                    <div
                        x-show="imgError || !coverUrl"
                        class="h-full w-full flex items-center justify-center bg-slate-100"
                    >
                        <img
                            :src="fallbackCover"
                            alt="No cover"
                            class="w-full h-full object-cover select-none pointer-events-none"
                            draggable="false"
                            onerror="this.onerror=null; this.src='{{ asset('images/book-cover.webp') }}';"
                        >
                    </div>

                </div>

                <!-- Book Title, Category & Author Underneath -->
                <div class="mt-2.5 flex flex-col flex-1 min-w-0">
                    <!-- Title -->
                    <h4
                        class="text-[13.5px] sm:text-[14px] font-bold text-[#0B2454] leading-snug group-hover:text-[#102B70] line-clamp-2 transition-colors"
                        x-text="book.title"
                        :title="book.title"
                    ></h4>

                    <!-- Author -->
                    <p
                        x-show="book.author"
                        class="text-[12px] sm:text-[12.5px] text-[#64748B] mt-1 line-clamp-1 font-medium"
                        x-text="book.author"
                        :title="book.author"
                    ></p>

                    <!-- Category indicator -->
                    <div
                        x-show="book.category"
                        class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-[#102B70] line-clamp-1"
                    >
                        <svg class="w-3 h-3 text-[#FCC719] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate" x-text="book.category" :title="book.category"></span>
                    </div>

                    <!-- Additional Metadata (Year / Type) -->
                    <div
                        x-show="book.year || book.type"
                        class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-400 font-medium line-clamp-1"
                    >
                        <span x-show="book.year" x-text="book.year"></span>
                        <span x-show="book.year && book.type">•</span>
                        <span x-show="book.type" x-text="book.type"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State (When not loading and items array is empty) -->
    <div
        x-show="!isLoading && (!items || items.length === 0)"
        class="py-8 text-center text-slate-400 text-xs sm:text-sm font-medium"
    >
        No related books cataloged under this category at this moment.
    </div>
</section>