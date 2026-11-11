<div wire:init="refreshRecommendations" class="student-page min-w-0 w-full min-h-full bg-[#F8FAFC] pb-16 font-sans text-base text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        <!-- ======================================================== -->
        <!-- 1. HERO HEADER                                           -->
        <!-- ======================================================== -->
        <div class="student-page-heading flex flex-col justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#102B70]">Find &amp; Reserve Books</h1>
                <p class="mt-1 text-base font-medium text-slate-500">
                    Find a book and request a hold. Once approved, collect it at the circulation desk by your pickup deadline.
                </p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <a
                    href="{{ route('student.profile', ['tab' => 'reservations']) }}"
                    wire:navigate
                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#102B70] px-5 text-base font-bold text-white transition-colors hover:bg-[#0B225E] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF] sm:w-auto"
                >
                    <span>My Reservations</span>
                </a>
            </div>
        </div>

        @if($waitingEntries->isNotEmpty())
            <section class="rounded-xl border border-[#E2E8F0] bg-white p-5" aria-labelledby="waitlist-heading">
                <h2 id="waitlist-heading" class="text-lg font-semibold text-[#102B70]">My waitlist</h2>
                <p class="mt-1 text-sm text-slate-600">We will notify you when a copy is reserved for you.</p>
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach($waitingEntries as $entry)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <span>{{ $entry->book_title }} @if($entry->status === 'lost_unavailable')<span class="text-amber-700">— unavailable; waitlist paused</span>@endif</span>
                            <button type="button" wire:click="leaveWaitlist({{ $entry->id }})" class="min-h-11 px-2 text-sm font-semibold text-[#102B70] underline">Leave waitlist</button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        <!-- 2. SEARCH & CATEGORY FILTERS                             -->
        <!-- ======================================================== -->
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 sm:p-5  space-y-4">
            <div class="student-catalog-filters grid min-w-0 grid-cols-1 gap-3">
                <!-- Search bar: 56px height, 16px radius -->
                <div class="relative min-w-0">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        aria-label="Search books" placeholder="Search title, author, ISBN..."
                        class="h-12 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 pr-16 text-base font-medium text-[#0F172A] placeholder-slate-400 transition-all focus:border-[#102B70] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#EFF6FF]"
                    >
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 px-3 text-base font-medium text-slate-600 hover:text-[#102B70]"
                        >
                        Clear</button>
                    @endif
                </div>

                <div class="flex min-w-0 flex-col gap-2 sm:flex-row">
                    <select
                        wire:model.live="selectedCategory"
                        aria-label="Filter books by category" class="h-12 w-full min-w-0 max-w-full rounded-xl border border-[#CBD5E1] bg-white px-4 text-base font-bold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($search || $selectedCategory !== 'all')
                <button type="button" wire:click="showRecommendations" class="min-h-11 text-sm font-semibold text-[#102B70] underline underline-offset-4">Clear filters</button>
            @endif
        </div>

        @php
            $filtering = trim($search) !== '' || $selectedCategory !== 'all';
            $showRecommended = ! $filtering && ! $browseAll && $recommendedBooks->isNotEmpty();
            $displayBooks = $showRecommended ? $recommendedBooks : $books;
        @endphp
        <div class="flex flex-wrap items-end justify-between gap-3" aria-live="polite">
            <div>
                <h2 class="text-xl font-bold text-[#102B70]">{{ $filtering ? 'Search results' : ($showRecommended ? 'Recommended for you' : 'Browse all books') }}</h2>
                @if(! $filtering && ! $browseAll && $recommendedBooks->isEmpty())
                    <p class="mt-1 text-sm text-slate-600">No recommendations are available right now. Browse the catalog below.</p>
                @elseif($showRecommended)
                    <p class="mt-1 text-sm text-slate-600">Saved recommendations based on your library activity{{ auth()->user()->recommendation?->generated_at ? ' · Updated '.auth()->user()->recommendation->generated_at->diffForHumans() : '' }}.</p>
                @endif
            </div>
            @if(! $filtering)
                @if($showRecommended)
                    <button type="button" wire:click="$set('browseAll', true)" class="min-h-11 rounded-lg border border-[#102B70] px-4 text-sm font-semibold text-[#102B70] hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">Browse all books</button>
                @elseif($recommendedBooks->isNotEmpty())
                    <button type="button" wire:click="showRecommendations" class="min-h-11 rounded-lg border border-[#102B70] px-4 text-sm font-semibold text-[#102B70] hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">Recommended for you</button>
                @endif
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- 3. AVAILABLE BOOKS GRID                                 -->
        <!-- ======================================================== -->
        <div class="student-catalog-grid grid grid-cols-1 gap-4">
            @forelse($displayBooks as $item)
                @php
                    $bdata = $item->bookData;
                    $title = $bdata?->book_title ?? 'Untitled Book';
                    $authors = $bdata?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
                    $categoryName = $bdata?->categories?->first()?->name ?? 'General Collection';
                    $availableCopiesCount = $item->books->count();
                    $firstCopy = $item->books->first();
                    $coverUrl = $item->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                @endphp
                    <div class="group rounded-xl border border-[#E2E8F0] bg-white p-4 transition-all flex flex-col justify-between">
                    <div class="min-w-0 space-y-3">
                        <!-- Book Cover and Badges -->
                        <div class="relative rounded-xl bg-[#F8FAFC] flex items-center justify-center border border-slate-100 p-3">
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $title }}"
                                loading="lazy" width="80" height="120" class="h-28 w-20 object-contain"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                            <span class="absolute top-2 left-2 px-2 py-1 rounded-md text-xs font-bold bg-white text-[#102B70] border border-[#CBD5E1] ">
                                {{ $availableCopiesCount ? $availableCopiesCount . ' ' . \Illuminate\Support\Str::plural('copy', $availableCopiesCount) . ' available' : 'No copies available' }}
                            </span>
                        </div>

                        <!-- Book Details -->
                        <div class="space-y-1">
                            <span class="text-xs font-semibold text-[#102B70] uppercase tracking-wide block">
                                {{ $categoryName }}
                            </span>
                            <h3 class="text-base font-bold text-[#0F172A] line-clamp-2 group-hover:text-[#102B70] transition-colors leading-snug" title="{{ $title }}">
                                {{ $title }}
                            </h3>
                            <p class="text-sm text-slate-500 line-clamp-2 font-medium">By {{ $authors }}</p>
                        </div>

                        <div class="pt-2 text-sm text-slate-500 break-words space-y-0.5 border-t border-slate-100">
                            @if($item->call_number)<div>Call No: {{ $item->call_number }}</div>@endif
                            @if($firstCopy?->location)<div>Location: {{ $firstCopy->location }}</div>@endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-4 mt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="openReservationModal({{ $item->id }})"
                            class="w-full h-11 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-bold transition-all flex items-center justify-center cursor-pointer active:scale-[0.98]"
                        >
                            <span>{{ $availableCopiesCount ? 'Reserve' : 'Join waitlist' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-xl border border-[#E2E8F0] p-6 space-y-3">
                    <h3 class="text-lg font-bold text-[#102B70]">No books found</h3>
                    <p class="text-base text-slate-500 max-w-md mx-auto">
                        Try modifying your search keywords or clear the category filter to view more titles available for hold.
                    </p>
                    <button
                        type="button"
                        wire:click="$set('search', ''); $set('selectedCategory', 'all');"
                        class="h-10 px-5 rounded-xl border border-slate-300 text-base font-bold text-slate-700 hover:bg-slate-50"
                    >
                        Clear All Filters
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(! $showRecommended && $books->hasPages())
            <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 ">
                {{ $books->links('components.widget.cursor-pagination') }}
            </div>
        @endif

    </div>

    <!-- ======================================================== -->
    <!-- 4. CONFIRM RESERVATION MODAL                             -->
    <!-- ======================================================== -->
    <x-widget.modal-loading target="openReservationModal" title="Reservation details" />
    @if($showModal && $this->selectedBookDetail)
        @php
            $mBook = $this->selectedBookDetail;
            $mData = $mBook->bookData;
            $mTitle = $mData?->book_title ?? 'Book';
            $mAuthors = $mData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
            $mCover = $mBook->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#071943]/60 p-3 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="reservation-modal-title">
            <div class="my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 text-base shadow-2xl sm:max-h-[calc(100dvh-2rem)] sm:p-7">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                    <div>
                        <h3 id="reservation-modal-title" class="text-xl font-bold tracking-tight text-[#102B70]">{{ $selectedBookCopyId ? 'Confirm Book Reservation' : 'Join the Waitlist?' }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $selectedBookCopyId ? 'Review the book and pickup details below.' : 'Review the book details below.' }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="min-h-11 shrink-0 rounded-lg px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">
                    Close</button>
                </div>

                <!-- Book Preview -->
                <div class="my-5 flex min-w-0 gap-4 rounded-xl border border-slate-200 bg-[#F8FAFC] p-4 sm:p-5">
                    <img
                        src="{{ $mCover }}"
                        alt="{{ $mTitle }}"
                        class="h-24 w-16 rounded-lg object-cover  border border-slate-200 shrink-0"
                        onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                    >
                    <div class="min-w-0 space-y-1">
                        <h4 class="break-words text-base font-bold leading-snug text-slate-900 sm:text-lg">{{ $mTitle }}</h4>
                        <p class="break-words text-sm text-slate-600">By {{ $mAuthors }}</p>
                        <div class="break-words pt-1 text-sm text-slate-600">
                            Call Number: {{ $mBook->call_number ?: 'N/A' }}
                        </div>
                    </div>
                </div>

                @if(!$selectedBookCopyId)
                    <p class="text-base text-slate-700">All copies are borrowed or reserved. Would you like to join the waitlist? We will automatically create a reservation when a copy becomes available and notify you. The reservation will still need library approval.</p>
                @else
                <!-- Reservation Form -->
                <div class="space-y-4">
                    <!-- Available Copy Selection -->
                    <div class="space-y-1.5">
                        <label for="reservation-copy" class="text-base font-bold text-[#334155]">Select Copy to Reserve <span class="text-[#EF4444]">*</span></label>
                        <select
                            id="reservation-copy"
                            wire:model="selectedBookCopyId"
                            class="h-12 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                            @foreach($mBook->books as $copy)
                                <option value="{{ $copy->id }}">
                                    Copy #{{ $copy->accession_number }} — Location: {{ $copy->location ?: 'Main Library' }} (Condition: {{ $copy->condition?->status ?: 'Good' }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedBookCopyId') <p class="text-base text-[#B91C1C] font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="pickup-date" class="text-base font-bold text-[#334155]">Pickup deadline</label>
                        <input id="pickup-date" type="date" wire:model="pickupDate"
                            min="{{ now('Asia/Manila')->addDay()->toDateString() }}" max="{{ now('Asia/Manila')->addDays(\App\Services\ReservationSettings::maxPickupDays())->toDateString() }}"
                            class="h-12 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @error('pickupDate') <p class="text-base text-[#B91C1C]">{{ $message }}</p> @enderror
                        <p class="text-base text-slate-500">Collect after approval by 5:00 PM on your selected date. The hold expires after that.</p>
                    </div>

                    <!-- Comment / Note -->
                    <div class="space-y-1.5">
                        <label for="reservation-note" class="text-base font-bold text-[#334155]">Note / Remarks (Optional)</label>
                        <input
                            id="reservation-note"
                            type="text"
                            wire:model="studentComment"
                            placeholder="e.g. Needed for Thesis research / Capstone work..."
                            class="h-12 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                    </div>
                </div>

                @endif
                <div class="mt-5 flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="h-11 w-full px-5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-base font-bold transition-all cursor-pointer sm:w-auto"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="submitReservation"
                        wire:loading.attr="disabled"
                        class="h-11 w-full px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-bold transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98] disabled:opacity-50 sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="submitReservation">{{ $selectedBookCopyId ? 'Confirm Reservation' : 'Join Waitlist' }}</span>
                        <span wire:loading wire:target="submitReservation">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
