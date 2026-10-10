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
    x-on:copy-added.window="if (!$event.detail || !$event.detail.isMultiple) { isAddingCopy = false }"
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
                    x-show="isFetching"
                    x-cloak
                    class="animate-pulse"
                >
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] items-start">
                        <!-- Left Side Skeleton: Cover, Metadata -->
                        <div class="grid gap-6 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <aside class="w-full sm:w-[180px] shrink-0">
                                <!-- Cover Skeleton -->
                                <div class="w-full h-[260px] sm:h-[270px] rounded-lg bg-slate-100 flex flex-col items-center justify-center border border-slate-200/80 shadow-sm">
                                    <div class="h-8 w-8 animate-spin rounded-full border-3 border-[#102B70]/20 border-t-[#102B70]"></div>
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

                <!-- 2. ACTUAL BOOK CONTENT -->
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
                        <!-- Left Side: Cover and Book Details -->
                        <div class="grid gap-6 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <aside class="w-full sm:w-[180px] shrink-0">
                                <div
                                    wire:key="modal-book-cover-{{ $bookDetail->id }}-{{ md5($coverUrl ?? 'no-cover') }}"
                                    x-data="{
                                        imgLoaded: false,
                                        imgError: false,
                                        coverSrc: @js($coverUrl)
                                    }"
                                    x-init="
                                        imgLoaded = false;
                                        imgError = false;
                                        if (!coverSrc) {
                                            imgError = true;
                                            imgLoaded = true;
                                        } else {
                                            $nextTick(() => {
                                                const img = $refs.coverImg;
                                                if (img && img.complete && img.naturalHeight !== 0) {
                                                    imgLoaded = true;
                                                }
                                            });
                                        }
                                    "
                                    class="relative w-full h-[260px] sm:h-[270px] overflow-hidden rounded-lg bg-[#E8EEFC] shadow-[0_8px_20px_rgba(15,23,42,0.18)] flex items-center justify-center border border-slate-200/80"
                                >
                                    <!-- Image Loading Spinner & Shimmer inside the placeholder while image is downloading -->
                                    <div
                                        x-show="!imgLoaded && !imgError"
                                        class="absolute inset-0 bg-slate-100/95 backdrop-blur-xs flex flex-col items-center justify-center p-3 text-center transition-opacity duration-300 z-10"
                                    >
                                        <div class="h-8 w-8 animate-spin rounded-full border-3 border-[#102B70]/20 border-t-[#102B70]"></div>
                                    </div>

                                    <!-- Actual Book Cover Image (when URL exists) -->
                                    @if(!empty($coverUrl))
                                        <img
                                            x-ref="coverImg"
                                            src="{{ $coverUrl }}"
                                            alt="{{ $data ? $data->book_title : 'Book cover' }}"
                                            x-on:load="imgLoaded = true; imgError = false;"
                                            x-on:error="imgError = true; imgLoaded = true;"
                                            x-show="!imgError"
                                            class="h-full w-full object-cover select-none transition-opacity duration-300"
                                            :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                                            loading="eager"
                                        >
                                    @endif

                                    <!-- Fallback / No Cover / Error State -->
                                    <div
                                        x-show="imgError || !coverSrc"
                                        x-cloak
                                        class="absolute inset-0 h-full w-full bg-slate-100 flex flex-col items-center justify-center"
                                    >
                                        <img
                                            src="{{ asset('images/book-cover.webp') }}"
                                            alt="No cover available"
                                            class="h-full w-full object-cover select-none pointer-events-none"
                                            draggable="false"
                                        >
                                    </div>
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

                        <!--Physical Copies Table OR Add Physical Copy Form -->
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
                                    @can('create', \App\Models\Book::class)
                                        <div class="relative" x-data="{ addDropdownOpen: false }">
                                            <button
                                                type="button"
                                                @click="addDropdownOpen = !addDropdownOpen"
                                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#102B70] bg-white px-3 text-xs font-semibold text-[#102B70] transition-colors hover:bg-[#F5F9FF] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                                            >
                                                <span>Add Copy</span>
                                                <svg class="h-3 w-3 text-[#102B70] transition-transform duration-150" :class="addDropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                            </button>

                                            <div
                                                x-show="addDropdownOpen"
                                                @click.outside="addDropdownOpen = false"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute right-0 z-30 mt-1.5 w-48 rounded-xl border border-[#DCE3EC] bg-white p-1.5 shadow-lg focus:outline-none"
                                            >
                                                <button
                                                    type="button"
                                                    @click="addDropdownOpen = false; $dispatch('open-add-copy', { bookDetailId: {{ $bookDetail->id }}, isMultiple: false })"
                                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-700 transition-colors hover:bg-blue-50 hover:text-[#102B70]"
                                                >
                                                    <svg class="h-5 w-5 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                                    <div>
                                                        <div class="text-sm">Single Copy</div>
                                                    </div>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="addDropdownOpen = false; $dispatch('open-add-copy', { bookDetailId: {{ $bookDetail->id }}, isMultiple: true })"
                                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-700 transition-colors hover:bg-blue-50 hover:text-[#102B70]"
                                                >
                                                    <svg class="h-5 w-5 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                                                    <div>
                                                        <div class="text-sm">Multiple Copies</div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    @endcan
                                </div>

                                <div class="overflow-x-auto rounded-lg border border-[#DCE3EC] max-h-[500px] custom-scrollbar overflow-y-[100px]">
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
                @can('update', \App\Models\Book::class)
                    <a
                        href="{{ $bookDetail ? route('admin.book-management.edit', $bookDetail->id) : '#' }}"
                        wire:navigate
                        :class="isFetching ? 'opacity-40 pointer-events-none' : ''"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m14.7 6.3 3 3M5 19l3.7-.7L19 8a2.1 2.1 0 0 0-3-3L5.7 15.3 5 19Z"/></svg>
                        <span>Edit Book</span>
                    </a>
                @endcan
            </footer>
        </section>
    </div>
</div>
