@if($showReserveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
        <div class="my-auto w-full max-w-lg rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] border border-[#DBEAFE] text-[#102B70] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#102B70]">Reserve a Library Book</h3>
                        <p class="text-xs text-slate-500 font-medium">Search available books and hold a copy for pickup.</p>
                    </div>
                </div>
                <button type="button" wire:click="closeReserveModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Form Body -->
            <div class="space-y-4">
                <!-- Search Book Input -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#334155]">Search Available Book <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="reserveSearchBook"
                            placeholder="Type title, author, or accession number..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3.5 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @if($reserveSearchBook)
                            <button
                                type="button"
                                wire:click="$set('reserveSearchBook', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        @endif
                    </div>
                    @error('selectedBookId') <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p> @enderror

                    <!-- Search Results Dropdown -->
                    @if($this->searchedAvailableBooks->isNotEmpty() && !$selectedBookId)
                        <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden divide-y divide-slate-100 max-h-48 overflow-y-auto">
                            @foreach($this->searchedAvailableBooks as $bk)
                                @php
                                    $bkTitle = $bk->bookDetail?->bookData?->book_title ?? 'Untitled Book';
                                    $bkAuthors = $bk->bookDetail?->bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Author';
                                    $bkCover = $bk->bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                                @endphp
                                <button
                                    type="button"
                                    wire:click="selectReserveBook({{ $bk->id }})"
                                    class="w-full text-left p-2.5 hover:bg-blue-50/50 flex items-center gap-3 text-xs cursor-pointer transition-colors"
                                >
                                    <img src="{{ $bkCover }}" alt="{{ $bkTitle }}" class="h-10 w-7 rounded object-cover shadow-2xs border border-slate-200 shrink-0" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-slate-900 truncate">{{ $bkTitle }}</div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $bkAuthors }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">Acc: {{ $bk->accession_number }} | {{ $bk->location ?: 'Main Library' }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">Available</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Selected Book Preview -->
                    @if($selectedBookId)
                        @php
                            $selBk = \App\Models\Book::with(['bookDetail.bookData.authors'])->find($selectedBookId);
                            $selTitle = $selBk?->bookDetail?->bookData?->book_title ?? 'Selected Book';
                            $selAuthors = $selBk?->bookDetail?->bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Author';
                            $selCover = $selBk?->bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                        @endphp
                        @if($selBk)
                            <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/50 flex items-center gap-3 text-xs">
                                <img src="{{ $selCover }}" alt="{{ $selTitle }}" class="h-12 w-8 rounded object-cover shadow-2xs border border-slate-200 shrink-0" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-[#102B70] truncate">{{ $selTitle }}</div>
                                    <div class="text-[11px] text-slate-600 truncate">{{ $selAuthors }}</div>
                                    <div class="text-[10px] font-mono text-slate-500">Accession: {{ $selBk->accession_number }}</div>
                                </div>
                                <button type="button" wire:click="$set('selectedBookId', null)" class="text-xs font-bold text-slate-400 hover:text-rose-600 cursor-pointer">
                                    Change
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Hold Window Selection -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-[#334155]">Hold Window (Days)</label>
                        <span class="text-xs font-bold text-[#102B70]">Claim by {{ now()->addDays($reservePickupDays)->format('M d, Y') }}</span>
                    </div>
                    <select
                        wire:model.live="reservePickupDays"
                        class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 font-medium focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                    >
                        <option value="1">1 Day (Tomorrow)</option>
                        <option value="2">2 Days</option>
                        <option value="3">3 Days (Recommended)</option>
                        <option value="5">5 Days</option>
                        <option value="7">7 Days (Maximum)</option>
                    </select>
                </div>

                <!-- Remarks -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#334155]">Note / Remarks (Optional)</label>
                    <input
                        type="text"
                        wire:model="reserveComment"
                        placeholder="e.g. For class project or thesis review..."
                        class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                    >
                </div>

                <!-- Quick browse alternative link -->
                <div class="text-center pt-1">
                    <a href="{{ route('student.reservations') }}" wire:navigate class="text-xs font-bold text-[#102B70] hover:underline">
                        Or browse the full catalog with book covers &rarr;
                    </a>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button
                    type="button"
                    wire:click="closeReserveModal"
                    class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="confirmProfileReservation"
                    wire:loading.attr="disabled"
                    class="h-10 px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold shadow-xs hover:shadow-md transition-all flex items-center gap-2 cursor-pointer active:scale-[0.98] disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="confirmProfileReservation">Confirm Hold</span>
                    <span wire:loading wire:target="confirmProfileReservation">Processing...</span>
                </button>
            </div>
        </div>
    </div>
@endif
