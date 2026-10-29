<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-base text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        <!-- ======================================================== -->
        <!-- 1. HERO HEADER                                           -->
        <!-- ======================================================== -->
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 sm:p-6 text-slate-800">

            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="space-y-2 max-w-xl">
                    <h1 class="text-xl font-semibold tracking-tight text-[#102B70]">Reserve Books Online</h1>
                    <p class="text-base leading-relaxed text-slate-600">
                        Reserve available academic and reference resources in advance. Claim your reserved copies at the circulation desk within your scheduled pickup window.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <a
                        href="{{ route('student.profile') }}"
                        wire:navigate
                        class="inline-flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border border-[#CBD5E1] bg-white px-4 text-base font-semibold text-[#102B70] transition-colors hover:bg-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#102B70]/20 sm:w-auto"
                    >
                        <span>My Profile</span>
                        @if($activeCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border border-[#CBD5E1] bg-[#F8FAFC] text-[#102B70]">
                                {{ $activeCount }} Active
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. SEARCH & CATEGORY FILTERS                             -->
        <!-- ======================================================== -->
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 sm:p-5  space-y-4">
            <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                <!-- Search bar: 56px height, 16px radius -->
                <div class="relative flex-1 max-w-2xl">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search books by title, author name, ISBN, or subject..."
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

                <div class="flex flex-col gap-2 sm:flex-row">
                    <select
                        wire:model.live="selectedCategory"
                        class="h-12 rounded-xl border border-[#CBD5E1] bg-white px-4 text-base font-bold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Category quick-filter pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 no-scrollbar">
                <button
                    type="button"
                    wire:click="$set('selectedCategory', 'all')"
                    class="h-9 px-4 rounded-xl text-base font-bold transition-all cursor-pointer whitespace-nowrap {{ $selectedCategory === 'all' ? 'bg-[#102B70] text-white ' : 'bg-white text-slate-700 border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}"
                >
                    All Disciplines
                </button>
                @foreach($categories->take(8) as $cat)
                    <button
                        type="button"
                        wire:click="$set('selectedCategory', '{{ $cat->id }}')"
                        class="h-9 px-4 rounded-xl text-base font-bold transition-all cursor-pointer whitespace-nowrap {{ (string)$selectedCategory === (string)$cat->id ? 'bg-[#102B70] text-white ' : 'bg-white text-slate-700 border border-[#CBD5E1] hover:bg-[#F8FAFC]' }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. AVAILABLE BOOKS GRID                                 -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @forelse($books as $item)
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
                    <div class="space-y-3">
                        <!-- Book Cover and Badges -->
                        <div class="relative overflow-hidden rounded-xl bg-[#F8FAFC] aspect-[3/4] flex items-center justify-center border border-slate-100">
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $title }}"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full text-base font-bold bg-white text-[#102B70] border border-[#CBD5E1] ">
                                {{ $availableCopiesCount }} Available
                            </span>
                            <span class="absolute bottom-2.5 right-2.5 px-2.5 py-1 rounded text-base font-mono font-bold bg-white text-slate-600 border border-[#CBD5E1]">
                                {{ $firstCopy?->accession_number ?: 'N/A' }}
                            </span>
                        </div>

                        <!-- Book Details -->
                        <div class="space-y-1">
                            <span class="text-base font-bold text-[#102B70] uppercase tracking-wider block">
                                {{ $categoryName }}
                            </span>
                            <h3 class="text-base font-bold text-[#0F172A] line-clamp-2 group-hover:text-[#102B70] transition-colors leading-snug" title="{{ $title }}">
                                {{ $title }}
                            </h3>
                            <p class="text-base text-slate-500 line-clamp-1 font-medium">By {{ $authors }}</p>
                        </div>

                        <div class="pt-1.5 text-base text-slate-500 font-mono space-y-0.5 border-t border-slate-100">
                            <div><span class="text-slate-500">Call No:</span> {{ $item->call_number ?: 'General Stack' }}</div>
                            <div><span class="text-slate-500">Location:</span> {{ $firstCopy?->location ?: 'Main Library' }}</div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-4 mt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="openReservationModal({{ $item->id }})"
                            class="w-full h-11 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-bold transition-all flex items-center justify-center cursor-pointer active:scale-[0.98]"
                        >
                            <span>Reserve This Book</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-xl border border-[#E2E8F0] p-6 space-y-3">
                    <h3 class="text-lg font-bold text-[#102B70]">No available books found</h3>
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
        @if($books->hasPages())
            <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 ">
                {{ $books->links() }}
            </div>
        @endif

    </div>

    <!-- ======================================================== -->
    <!-- 4. CONFIRM RESERVATION MODAL                             -->
    <!-- ======================================================== -->
    @if($showModal && $this->selectedBookDetail)
        @php
            $mBook = $this->selectedBookDetail;
            $mData = $mBook->bookData;
            $mTitle = $mData?->book_title ?? 'Book';
            $mAuthors = $mData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
            $mCover = $mBook->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#071943]/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-lg rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-5 text-base">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-[#102B70]">Confirm Book Reservation</h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-base font-medium text-slate-600 hover:bg-[#F8FAFC] hover:text-slate-700">
                    Close</button>
                </div>

                <!-- Book Preview -->
                <div class="flex gap-4 p-4 rounded-xl border border-slate-200 bg-[#F8FAFC]">
                    <img
                        src="{{ $mCover }}"
                        alt="{{ $mTitle }}"
                        class="h-24 w-16 rounded-lg object-cover  border border-slate-200 shrink-0"
                        onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                    >
                    <div class="min-w-0 space-y-1">
                        <h4 class="text-base font-bold text-slate-900 line-clamp-2 leading-snug">{{ $mTitle }}</h4>
                        <p class="text-base text-slate-600 font-medium">By {{ $mAuthors }}</p>
                        <div class="text-base font-mono text-slate-500 pt-1">
                            Call Number: {{ $mBook->call_number ?: 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Reservation Form -->
                <div class="space-y-4">
                    <!-- Available Copy Selection -->
                    <div class="space-y-1.5">
                        <label class="text-base font-bold text-[#334155]">Select Copy to Reserve <span class="text-[#EF4444]">*</span></label>
                        <select
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

                    <!-- Pickup Hold Duration -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-base font-bold text-[#334155]">Expected Pickup Window</label>
                            <span class="text-base font-bold text-[#102B70]">Pick up by {{ now()->addDays($pickupDays)->format('M d, Y') }}</span>
                        </div>
                        <select
                            wire:model.live="pickupDays"
                            class="h-12 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                            <option value="1">Within 1 Day (Tomorrow)</option>
                            <option value="2">Within 2 Days</option>
                            <option value="3">Within 3 Days (Standard)</option>
                            <option value="5">Within 5 Days</option>
                            <option value="7">Within 7 Days (1 Week Max)</option>
                        </select>
                        <p class="text-base text-slate-500">Hold is released back to inventory if not collected by the deadline.</p>
                    </div>

                    <!-- Comment / Note -->
                    <div class="space-y-1.5">
                        <label class="text-base font-bold text-[#334155]">Note / Remarks (Optional)</label>
                        <input
                            type="text"
                            wire:model="studentComment"
                            placeholder="e.g. Needed for Thesis research / Capstone work..."
                            class="h-12 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:justify-end">
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
                        <span wire:loading.remove wire:target="submitReservation">Confirm Reservation</span>
                        <span wire:loading wire:target="submitReservation">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
