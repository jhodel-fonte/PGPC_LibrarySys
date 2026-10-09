<div
    x-data="{
        open: @entangle('isOpen'),
        isFetching: true,
        isAddingCopy: false,
        previousFocus: null,
        fallbackCover: '{{ asset('images/book-cover.webp') }}',
        show() {
            this.previousFocus = document.activeElement;
            this.isFetching = true;
            this.isAddingCopy = false;
            this.open = true;
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },
        hide() {
            this.open = false;
            this.isFetching = true;
            this.isAddingCopy = false;
            $wire.close();
            this.$nextTick(() => this.previousFocus?.focus());
        }
    }"
    x-init="
        $wire.$watch('bookDetailId', (val) => {
            if (val) {
                isFetching = false;
            }
        });
    "
    x-on:open-book-details.window="show()"
    x-on:book-details-loaded.window="isFetching = false; isAddingCopy = false"
    x-on:open-add-copy.window="isAddingCopy = true"
    x-on:close-add-copy.window="isAddingCopy = false"
    x-on:copy-added.window="isAddingCopy = false"
    x-on:keydown.escape.window="if (isAddingCopy) { isAddingCopy = false } else if (open) { hide() }"
    x-cloak
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.self="hide()"
        class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-[#071943]/55 p-3 backdrop-blur-[2px] sm:p-6"
        role="presentation"
    >
        <section
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-3 scale-[0.98] opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-2 scale-[0.98] opacity-0"
            x-on:click.stop
            role="dialog"
            aria-modal="true"
            aria-labelledby="book-details-title"
            aria-describedby="book-details-description"
            class="my-auto flex max-h-[92dvh] w-full max-w-[1480px] flex-col overflow-hidden rounded-xl bg-white shadow-[0_24px_70px_rgba(7,25,67,0.28)]"
        >
            <header class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="book-details-title" class="text-xl font-bold tracking-[-0.02em] text-[#102B70]">Book Details</h2>
                    <p id="book-details-description" class="mt-0.5 text-sm text-slate-500">
                        <span x-show="!isFetching">View title information and all available physical copies.</span>
                    </p>
                </div>
                <button
                    x-ref="closeButton"
                    type="button"
                    x-on:click="hide()"
                    class="-mr-1 inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                    aria-label="Close book details"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </header>

            <div class="custom-scrollbar min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                <!-- 1. SKELETON LOADER (Shown first while fetching / switching books) -->
                <div
                    x-show="isFetching || !@js((bool) $bookDetail)"
                    class="animate-pulse"
                >
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] items-start">
                        <!-- Left Side Skeleton: Cover, QR, Metadata -->
                        <div class="grid gap-6 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <aside class="grid grid-cols-[112px_minmax(0,1fr)] gap-3 sm:block">
                                <!-- Cover Skeleton -->
                                <div class="aspect-[2/3] sm:aspect-[4/5] w-full rounded-lg bg-slate-200 flex items-center justify-center shadow-sm">
                                    <svg class="h-9 w-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>

                                <!-- QR Skeleton -->
                                <div class="flex min-h-[160px] flex-col items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-3 sm:mt-3">
                                    <div class="h-3 w-16 bg-slate-200 rounded mb-2"></div>
                                    <div class="h-28 w-28 sm:h-32 sm:w-32 rounded-lg bg-slate-200 flex items-center justify-center">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <div class="h-2.5 w-24 bg-slate-200 rounded mt-2.5"></div>
                                    <div class="h-2 w-28 bg-slate-200 rounded mt-1.5"></div>
                                </div>
                            </aside>

                            <!-- Metadata Skeleton -->
                            <div class="min-w-0">
                                <div class="space-y-4">
                                    <div>
                                        <div class="h-3 w-12 bg-slate-200 rounded mb-1.5"></div>
                                        <div class="h-6 w-3/4 bg-slate-200 rounded"></div>
                                        <div class="h-3.5 w-1/2 bg-slate-200 rounded mt-2"></div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3 pt-2">
                                        <div>
                                            <div class="h-3 w-14 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-28 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-20 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-24 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-16 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-20 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-20 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-24 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-16 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-28 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-18 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-14 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-24 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-16 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-24 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-14 bg-slate-200 rounded"></div>
                                        </div>
                                        <div>
                                            <div class="h-3 w-24 bg-slate-200 rounded mb-1.5"></div>
                                            <div class="h-4 w-14 bg-slate-200 rounded"></div>
                                        </div>
                                    </div>

                                    <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                        <div class="h-3.5 w-32 bg-slate-200 rounded mb-2.5"></div>
                                        <div class="space-y-2">
                                            <div class="h-3 w-full bg-slate-200 rounded"></div>
                                            <div class="h-3 w-5/6 bg-slate-200 rounded"></div>
                                            <div class="h-3 w-4/6 bg-slate-200 rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side Skeleton: Copies Table -->
                        <div class="flex flex-col min-w-0 xl:border-l xl:border-slate-200 xl:pl-6 pt-4 xl:pt-0 border-t border-slate-200 xl:border-t-0">
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <div class="h-4 w-36 bg-slate-200 rounded mb-1.5"></div>
                                    <div class="h-3 w-28 bg-slate-200 rounded"></div>
                                </div>
                                <div class="h-8 w-24 bg-slate-200 rounded-lg"></div>
                            </div>

                            <div class="rounded-lg border border-slate-200 overflow-hidden">
                                <div class="h-9 bg-slate-100 border-b border-slate-200"></div>
                                <div class="divide-y divide-slate-100 p-2 space-y-3">
                                    <div class="h-6 bg-slate-200/70 rounded"></div>
                                    <div class="h-6 bg-slate-200/70 rounded"></div>
                                    <div class="h-6 bg-slate-200/70 rounded"></div>
                                    <div class="h-6 bg-slate-200/70 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. ACTUAL BOOK CONTENT: Rendered when !isFetching and bookDetail is loaded -->
                @if($bookDetail)
                    @php
                        $data = $bookDetail->bookData;
                        $authors = $data && $data->authors ? $data->authors : collect();
                        $authorNames = $authors->isNotEmpty()
                            ? $authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->filter()->implode(', ')
                            : 'Unknown Author';
                        $categories = $data && $data->categories ? $data->categories : collect();
                        $categoryNames = $categories->isNotEmpty()
                            ? $categories->pluck('name')->implode(', ')
                            : 'Uncategorized';

                        $copies = $bookDetail->books ?: collect();
                        $totalCopies = $copies->count();
                        $availableCopies = $copies->where('status', 'available')->count();
                        $borrowedCopies = $copies->where('status', 'borrowed')->count();

                        $shelfLocation = $copies->pluck('location')->filter()->first() ?: ($bookDetail->call_number ?: 'Main Library');

                        $coverUrl = $bookDetail->cover_url;
                    @endphp

                    <div
                        x-show="!isFetching"
                        x-cloak
                        wire:key="modal-content-{{ $bookDetail->id }}"
                        class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] items-start"
                    >
                        <!-- Left Side: Cover, QR, and Book Details -->
                        <div class="grid gap-6 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <aside class="grid grid-cols-[112px_minmax(0,1fr)] gap-3 sm:block">
                                <div
                                    wire:key="modal-book-cover-{{ $bookDetail->id }}"
                                    x-data="{
                                        coverLoaded: false,
                                        coverError: false,
                                        coverUrl: @js($coverUrl),
                                        fallback: '{{ asset('images/book-cover.webp') }}',
                                        init() {
                                            if (!this.coverUrl) {
                                                this.coverError = true;
                                                this.coverLoaded = true;
                                                return;
                                            }
                                            const img = new Image();
                                            img.src = this.coverUrl;
                                            img.onload = () => { this.coverLoaded = true; };
                                            img.onerror = () => { this.coverError = true; this.coverLoaded = true; };
                                        }
                                    }"
                                    class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#E8EEFC] shadow-[0_8px_20px_rgba(15,23,42,0.18)] sm:aspect-[4/5]"
                                >
                                    <!-- Image Loading Shimmer -->
                                    <div
                                        x-show="!coverLoaded"
                                        class="absolute inset-0 bg-slate-200/80 animate-pulse flex items-center justify-center"
                                    >
                                        <svg class="h-8 w-8 text-slate-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <img
                                        :src="coverError || !coverUrl ? fallback : coverUrl"
                                        alt="{{ $data ? $data->book_title : 'Book cover' }}"
                                        class="h-full w-full object-cover select-none transition-opacity duration-300"
                                        :class="coverLoaded ? 'opacity-100' : 'opacity-0'"
                                        loading="eager"
                                    >
                                </div>

                                <div class="flex min-h-[160px] flex-col items-center justify-center rounded-lg border border-[#CFE0F6] bg-[#F7FAFF] p-3 text-center sm:mt-3">
                                    <div class="flex items-center justify-between w-full mb-2 px-0.5">
                                        <span class="text-xs font-bold text-[#102B70]">QR Code</span>
                                        @if($qrCodeSvg)
                                            <a
                                                href="data:image/svg+xml;utf8,{{ rawurlencode($qrCodeSvg) }}"
                                                download="QR_{{ $bookDetail->isbn ?: 'Book_' . $bookDetail->id }}.svg"
                                                class="text-[10px] font-semibold text-[#1D4ED8] hover:underline"
                                                title="Download QR Code"
                                            >
                                                Download
                                            </a>
                                        @endif
                                    </div>

                                    <div class="flex h-28 w-28 sm:h-32 sm:w-32 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm">
                                        @if($qrCodeSvg)
                                            <div class="h-full w-full flex items-center justify-center [&>svg]:h-full [&>svg]:w-full [&>svg]:block">
                                                {!! $qrCodeSvg !!}
                                            </div>
                                        @else
                                            <div class="h-6 w-6 animate-spin rounded-full border-2 border-[#102B70] border-t-transparent"></div>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-[11px] font-medium leading-tight text-slate-600 truncate max-w-[150px]">
                                        {{ $bookDetail->isbn ? 'ISBN: ' . $bookDetail->isbn : ($bookDetail->call_number ? 'Call: ' . $bookDetail->call_number : 'Title #' . $bookDetail->id) }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Scan to view in catalog</p>
                                </div>
                            </aside>

                            <div class="min-w-0">
                                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                                    <div class="col-span-2 sm:col-span-3">
                                        <dt class="text-sm font-medium text-slate-500">Title</dt>
                                        <dd class="mt-0.5 text-base font-bold text-black break-words">
                                            {{ $data ? $data->book_title : 'Untitled' }}
                                            @if($data && $data->subtitle)
                                                <span class="block text-xs font-medium text-slate-500 mt-0.5">{{ $data->subtitle }}</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Author</dt>
                                        <dd class="mt-0.5 text-sm font-bold text-black">{{ $authorNames }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">ISBN / ISSN</dt>
                                        <dd class="mt-0.5 text-sm font-bold tabular-nums text-black">{{ $bookDetail->isbn ?: ($bookDetail->issn ?: 'Not assigned') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Category</dt>
                                        <dd class="mt-0.5 text-sm font-bold text-black">{{ $categoryNames }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Shelf Location</dt>
                                        <dd class="mt-0.5 text-sm font-bold text-black">{{ $shelfLocation }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Publisher</dt>
                                        <dd class="mt-0.5 text-sm font-bold text-black">{{ $bookDetail->publisher ? $bookDetail->publisher->name : 'Not specified' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Total Copies</dt>
                                        <dd class="mt-0.5 text-sm font-bold tabular-nums text-black">{{ number_format($totalCopies) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Publication Year</dt>
                                        <dd class="mt-0.5 text-sm font-bold tabular-nums text-black">{{ $bookDetail->publication_year ?: ($data?->copyright_year ?: 'N/A') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Available Copies</dt>
                                        <dd class="mt-0.5 text-sm font-bold tabular-nums text-black">{{ number_format($availableCopies) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Borrowed Copies</dt>
                                        <dd class="mt-0.5 text-sm font-bold tabular-nums text-black">{{ number_format($borrowedCopies) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-slate-500">Last Updated</dt>
                                        <dd class="mt-0.5 text-sm font-bold text-black">{{ $bookDetail->updated_at ? $bookDetail->updated_at->format('M d, Y') : 'N/A' }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                                    <h3 class="text-sm font-bold text-black">Description / Notes</h3>
                                    <p class="mt-1.5 text-sm leading-relaxed text-black">
                                        {{ $data?->description ?: ($data?->note ?: 'No description or summary provided for this catalog record.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Physical Copies Table OR Add Physical Copy Form -->
                        <div class="flex flex-col min-w-0 xl:border-l xl:border-slate-200 xl:pl-6 pt-4 xl:pt-0 border-t border-slate-200 xl:border-t-0">
                            <!-- State A: Physical Copies Table & Management -->
                            <div x-show="!isAddingCopy" class="flex flex-col min-w-0">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h3 class="flex items-center gap-2 text-sm font-bold text-[#102B70]">
                                            Physical Copies ({{ number_format($totalCopies) }})
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $availableCopies }} available, {{ $borrowedCopies }} borrowed</p>
                                    </div>
                                    <button
                                        type="button"
                                        x-on:click="$dispatch('open-add-copy', { bookDetailId: {{ $bookDetail->id }} })"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#102B70] bg-white px-3 text-xs font-semibold text-[#102B70] transition-colors hover:bg-[#F5F9FF] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                        <span>Add Copy</span>
                                    </button>
                                </div>

                                <div class="overflow-x-auto rounded-lg border border-[#DCE3EC] max-h-[500px] custom-scrollbar overflow-y-auto">
                                    <table class="w-full min-w-[520px] text-left text-sm">
                                        <thead class="sticky top-0 z-10 bg-[#F5F8FC] text-sm font-semibold text-slate-600 border-b border-[#DCE3EC]">
                                            <tr>
                                                <th class="px-3 py-2.5">Accession No.</th>
                                                <th class="px-3 py-2.5">Unique Code</th>
                                                <th class="px-3 py-2.5">Location</th>
                                                <th class="px-3 py-2.5">Condition</th>
                                                <th class="px-3 py-2.5">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-slate-700">
                                            @forelse($copies as $copy)
                                                @php
                                                    $cond = strtolower($copy->condition ? $copy->condition->status : 'good');
                                                    $condTextClass = match($cond) {
                                                        'new' => 'text-emerald-600 font-semibold',
                                                        'good' => 'text-blue-600 font-semibold',
                                                        'worn', 'fair' => 'text-amber-600 font-semibold',
                                                        'damaged' => 'text-orange-600 font-semibold',
                                                        'lost' => 'text-red-600 font-semibold',
                                                        default => 'text-slate-600 font-semibold'
                                                    };

                                                    $stat = strtolower($copy->status);
                                                    $statTextClass = match($stat) {
                                                        'available' => 'text-emerald-600 font-semibold',
                                                        'borrowed' => 'text-blue-600 font-semibold',
                                                        'damaged' => 'text-orange-600 font-semibold',
                                                        'lost' => 'text-red-600 font-semibold',
                                                        'reserved' => 'text-amber-600 font-semibold',
                                                        'maintenance' => 'text-slate-600 font-semibold',
                                                        default => 'text-slate-600 font-semibold'
                                                    };
                                                @endphp
                                                <tr class="hover:bg-slate-50">
                                                    <td class="px-3 py-2.5 text-sm font-semibold tabular-nums text-black">{{ $copy->accession_number }}</td>
                                                    <td class="px-3 py-2.5 text-sm font-medium tabular-nums text-slate-600">{{ $copy->code ?: 'Not assigned' }}</td>
                                                    <td class="px-3 py-2.5 text-sm font-medium text-slate-700">{{ $copy->location ?: 'Main Library' }}</td>
                                                    <td class="px-3 py-2.5">
                                                        <span class="text-sm {{ $condTextClass }}">
                                                            {{ $copy->condition ? $copy->condition->status : 'Good' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <span class="text-sm {{ $statTextClass }}">
                                                            {{ ucfirst($copy->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-8 text-center text-sm font-medium text-slate-500">
                                                        No physical copies recorded for this title yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- State B: Add Physical Copy Form Component -->
                            <div x-show="isAddingCopy" x-cloak class="w-full">
                                <livewire:components.book-manager.book-copy-modal />
                            </div>
                        </div>
                    </div>
                @else
                    <div
                        x-show="!isFetching"
                        x-cloak
                        class="flex flex-col items-center justify-center py-16 text-center"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">No book details found</p>
                        <p class="mt-1 text-xs text-slate-400">The requested title could not be loaded.</p>
                    </div>
                @endif
            </div>

            <footer class="flex shrink-0 items-center justify-end gap-2 border-t border-slate-200 bg-white px-5 py-3.5 sm:px-6">
                <button
                    type="button"
                    x-on:click="hide()"
                    class="h-9 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                >
                    Close
                </button>
                <a
                    href="{{ $bookDetail ? route('admin.book-management.edit', $bookDetail->id) : '#' }}"
                    wire:navigate
                    :class="(isFetching || !@js((bool) $bookDetail)) ? 'opacity-40 pointer-events-none' : ''"
                    class="inline-flex h-9 items-center gap-2 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m14.7 6.3 3 3M5 19l3.7-.7L19 8a2.1 2.1 0 0 0-3-3L5.7 15.3 5 19Z"/></svg>
                    <span>Edit Book</span>
                </a>
            </footer>
        </section>
    </div>
</div>
