<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-6 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        <!-- ======================================================== -->
        <!-- 1. HERO HEADER                                           -->
        <!-- ======================================================== -->
        <div class="rounded-3xl bg-gradient-to-r from-[#102B70] via-[#0B225E] to-[#071943] p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <!-- Decorative background circles -->
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-2xl"></div>
            <div class="absolute right-40 -bottom-20 h-48 w-48 rounded-full bg-[#FCC719]/10 blur-xl"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[#FCC719] text-xs font-bold backdrop-blur-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        <span>PGPC Library Book Reservations</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Reserve Books Online</h1>
                    <p class="text-xs sm:text-sm text-white/80 leading-relaxed">
                        Reserve available academic and reference resources in advance. Claim your reserved copies at the circulation desk within your scheduled pickup window.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('student.profile') }}"
                        wire:navigate
                        class="h-11 px-5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-bold backdrop-blur-xs transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <span>My Profile</span>
                        @if($activeCount > 0)
                            <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FCC719] text-[#102B70]">
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                <!-- Search bar: 56px height, 16px radius -->
                <div class="relative flex-1 max-w-2xl">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search books by title, author name, ISBN, or subject..."
                        class="h-14 w-full rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] pl-11 pr-4 text-sm font-medium text-[#0F172A] placeholder-slate-400 transition-all focus:border-[#102B70] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#EFF6FF]"
                    >
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <select
                        wire:model.live="selectedCategory"
                        class="h-11 rounded-2xl border border-[#CBD5E1] bg-white px-4 text-xs sm:text-sm font-bold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
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
                    class="h-8 px-3.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap {{ $selectedCategory === 'all' ? 'bg-[#102B70] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    All Disciplines
                </button>
                @foreach($categories->take(8) as $cat)
                    <button
                        type="button"
                        wire:click="$set('selectedCategory', '{{ $cat->id }}')"
                        class="h-8 px-3.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap {{ (string)$selectedCategory === (string)$cat->id ? 'bg-[#102B70] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. AVAILABLE BOOKS GRID                                 -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
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
                <div class="group rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                    <div class="space-y-3">
                        <!-- Book Cover and Badges -->
                        <div class="relative overflow-hidden rounded-xl bg-slate-100 aspect-[3/4] flex items-center justify-center border border-slate-100">
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $title }}"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-600/90 text-white backdrop-blur-xs shadow-xs">
                                {{ $availableCopiesCount }} Available
                            </span>
                            <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/60 text-white backdrop-blur-xs">
                                {{ $firstCopy?->accession_number ?: 'N/A' }}
                            </span>
                        </div>

                        <!-- Book Details -->
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-[#102B70] uppercase tracking-wider block">
                                {{ $categoryName }}
                            </span>
                            <h3 class="text-sm font-bold text-[#0F172A] line-clamp-2 group-hover:text-[#102B70] transition-colors" title="{{ $title }}">
                                {{ $title }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-1 font-medium">By {{ $authors }}</p>
                        </div>

                        <div class="pt-1 text-[11px] text-slate-500 font-mono space-y-0.5 border-t border-slate-100">
                            <div><span class="text-slate-400">Call No:</span> {{ $item->call_number ?: 'General Stack' }}</div>
                            <div><span class="text-slate-400">Location:</span> {{ $firstCopy?->location ?: 'Main Library' }}</div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-4 mt-3 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="openReservationModal({{ $item->id }})"
                            class="w-full h-11 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]"
                        >
                            <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span>Reserve This Book</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-[#E2E8F0] p-6 space-y-3">
                    <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-[#102B70]">No available books found</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Try modifying your search keywords or clear the category filter to view more titles available for hold.
                    </p>
                    <button
                        type="button"
                        wire:click="$set('search', ''); $set('selectedCategory', 'all');"
                        class="h-9 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50"
                    >
                        Clear All Filters
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($books->hasPages())
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-xs">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-lg rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-[#102B70]">Confirm Book Reservation</h3>
                    </div>
                    <button type="button" wire:click="closeModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Book Preview -->
                <div class="flex gap-3.5 p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <img
                        src="{{ $mCover }}"
                        alt="{{ $mTitle }}"
                        class="h-20 w-14 rounded-lg object-cover shadow-xs border border-slate-200 shrink-0"
                        onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                    >
                    <div class="min-w-0 space-y-1">
                        <h4 class="text-sm font-bold text-slate-900 line-clamp-2">{{ $mTitle }}</h4>
                        <p class="text-xs text-slate-500 font-medium">By {{ $mAuthors }}</p>
                        <div class="text-[11px] font-mono text-slate-400 pt-1">
                            Call Number: {{ $mBook->call_number ?: 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Reservation Form -->
                <div class="space-y-4">
                    <!-- Available Copy Selection -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Select Copy to Reserve <span class="text-rose-500">*</span></label>
                        <select
                            wire:model="selectedBookCopyId"
                            class="h-11 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                            @foreach($mBook->books as $copy)
                                <option value="{{ $copy->id }}">
                                    Copy #{{ $copy->accession_number }} — Location: {{ $copy->location ?: 'Main Library' }} (Condition: {{ $copy->condition?->status ?: 'Good' }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedBookCopyId') <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <!-- Pickup Hold Duration -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#334155]">Expected Pickup Window</label>
                            <span class="text-xs font-bold text-[#102B70]">Pick up by {{ now()->addDays($pickupDays)->format('M d, Y') }}</span>
                        </div>
                        <select
                            wire:model.live="pickupDays"
                            class="h-11 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                            <option value="1">Within 1 Day (Tomorrow)</option>
                            <option value="2">Within 2 Days</option>
                            <option value="3">Within 3 Days (Standard)</option>
                            <option value="5">Within 5 Days</option>
                            <option value="7">Within 7 Days (1 Week Max)</option>
                        </select>
                        <p class="text-[11px] text-slate-500">Hold is released back to inventory if not collected by the deadline.</p>
                    </div>

                    <!-- Comment / Note -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Note / Remarks (Optional)</label>
                        <input
                            type="text"
                            wire:model="studentComment"
                            placeholder="e.g. Needed for Thesis research / Capstone work..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="submitReservation"
                        wire:loading.attr="disabled"
                        class="h-10 px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold shadow-xs hover:shadow-md transition-all flex items-center gap-2 cursor-pointer active:scale-[0.98] disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="submitReservation">Confirm Reservation</span>
                        <span wire:loading wire:target="submitReservation">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
