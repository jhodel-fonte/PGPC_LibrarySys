<div class="workstation-preview min-h-[220px] lg:flex-1 flex flex-col min-h-0">
    @if($bookData)
        <div class="workstation-preview-card bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] min-h-[220px] flex-1 flex flex-col justify-between gap-3.5 animate-fade-in overflow-y-auto">
            <div>
                <div class="flex items-start gap-3.5">
                    <!-- Book Cover Thumbnail -->
                    <div
                        wire:key="scanned-book-cover-{{ $bookData['accession'] ?? $bookData['book_id'] ?? 'preview' }}"
                        x-data="{ imgLoaded: false, imgError: false }"
                        class="w-[84px] h-[116px] rounded-xl overflow-hidden border border-[#E2E8F0] shadow-sm shrink-0 bg-[#F8FAFC] relative"
                    >
                        <!-- Loading Spinner Overlay -->
                        <div
                            x-show="!imgLoaded && !imgError"
                            class="absolute inset-0 z-10 bg-[#F8FAFC] flex flex-col items-center justify-center gap-1.5"
                        >
                            <svg class="animate-spin h-5 w-5 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-[9px] font-bold text-[#64748B] uppercase tracking-wider">Loading</span>
                        </div>

                        @if(!empty($bookData['cover_url']))
                            <img
                                src="{{ $bookData['cover_url'] }}"
                                alt="{{ $bookData['title'] ?? $bookData['book'] ?? 'Book Cover' }}"
                                loading="lazy"
                                decoding="async"
                                x-on:load="imgLoaded = true"
                                x-on:error="imgError = true; imgLoaded = true"
                                x-show="!imgError"
                                :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                                class="w-full h-full object-cover transition-opacity duration-200"
                            >
                        @endif

                        <div
                            x-show="imgError || !'{{ $bookData['cover_url'] ?? '' }}'"
                            style="{{ !empty($bookData['cover_url']) ? 'display: none;' : '' }}"
                            class="h-full w-full flex items-center justify-center bg-[#F8FAFC]"
                        >
                            <img
                                src="{{ asset('images/book-cover.webp') }}"
                                alt="Default book cover"
                                loading="lazy"
                                decoding="async"
                                x-on:load="imgLoaded = true"
                                class="w-full h-full object-cover select-none pointer-events-none"
                                draggable="false"
                            >
                        </div>
                    </div>

                    <!-- Book Title, Author & ISBN Tag -->
                    <div class="flex-1 min-w-0">
                        <h4 class="circulation-item-title text-sm font-bold text-[#0F172A] leading-snug" title="{{ $bookData['title'] ?? $bookData['book'] ?? 'Unknown Book' }}">
                            {{ $bookData['title'] ?? $bookData['book'] ?? 'Unknown Book' }}
                        </h4>
                        <p class="text-sm text-[#64748B] font-medium mt-1 truncate">
                            {{ $bookData['author'] ?? 'Unknown Author' }}
                        </p>
                        <div class="text-sm font-bold text-[#000000] mt-1.5">
                            <span class="truncate max-w-[150px]">{{ ($bookData['item_type'] ?? 'book') === 'book' ? 'ISBN' : 'Type' }}: {{ ($bookData['item_type'] ?? 'book') === 'book' ? ((!empty($bookData['isbn']) && $bookData['isbn'] !== 'N/A') ? $bookData['isbn'] : 'N/A') : ucfirst($bookData['item_type'] ?? 'item') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata & Action Section -->
            <div>
                <div class="border-t border-[#F1F5F9] mb-1"></div>

                <!-- Icon Metadata Rows -->
                <div class="flex flex-col gap-2.5 text-sm">
                    <!-- Accession No. -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#475569]">
                            <svg class="w-4 h-4 text-[#94A3B8] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2 5h2v14H2V5zm4 0h1v14H6V5zm3 0h3v14H9V5zm5 0h1v14h-1V5zm3 0h2v14h-2V5zm4 0h1v14h-1V5z"/>
                            </svg>
                            <span class="font-medium text-[#475569]">Accession No.</span>
                        </div>
                        <span class="font-bold text-sm text-[#000000]">{{ $bookData['accession'] ?? 'N/A' }}</span>
                    </div>

                    <!-- Borrowed / Added Date -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#475569]">
                            <svg class="w-4 h-4 text-[#94A3B8] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium text-[#475569]">{{ $mode === 'check-out' ? 'Added On' : 'Borrowed Date' }}</span>
                        </div>
                        <span class="font-bold text-sm text-[#000000]">
                            {{ $mode === 'check-out' ? ($bookData['added_on'] ?? $bookData['borrowed_on'] ?? now()->format('M. d, Y')) : ($bookData['borrowed_on'] ?? $bookData['added_on'] ?? 'N/A') }}
                        </span>
                    </div>

                    <!-- Due Date -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#475569]">
                            <svg class="w-4 h-4 text-[#94A3B8] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium text-[#475569]">Due Date</span>
                        </div>
                        <span class="font-bold text-sm text-[#000000]">{{ $bookData['due_date'] ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Bottom Action Button -->
                <div class="mt-4">
                    @if($mode === 'check-out')
                        @if($isInCheckout)
                            <button type="button"
                                    wire:click="removeCheckoutBook('{{ $bookData['accession'] }}')"
                                    class="w-full h-10 bg-white border border-[#FECACA] hover:bg-[#FEF2F2] active:scale-[0.99] text-[#B91C1C] rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-2xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Remove from Queue</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="addCheckoutBook('{{ $bookData['accession'] }}')"
                                    class="w-full h-10 bg-[#102B70] hover:bg-[#0B225E] active:scale-[0.99] text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#102B70]/30">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Add to Checkout</span>
                            </button>
                        @endif
                    @else
                        @if($isReturned)
                            <button type="button"
                                    wire:click="undoReturn('{{ $bookData['accession'] }}')"
                                    class="w-full h-10 bg-white border border-[#FECACA] hover:bg-[#FEF2F2] active:scale-[0.99] text-[#B91C1C] rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-2xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                <span>Undo Return</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="returnBook('{{ $bookData['accession'] }}')"
                                    class="w-full h-10 bg-[#102B70] hover:bg-[#0B225E] active:scale-[0.99] text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#102B70]/30">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Add as Returned</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] min-h-[220px] flex-1 flex flex-col items-center justify-center text-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center border border-[#DBEAFE] shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-[#0F172A]">Item Details Preview</h4>
                <p class="text-sm text-[#64748B] max-w-[220px] leading-relaxed mt-1">
                    {{ $mode === 'check-out' ? 'Scan an item barcode or click any queued item in the table to preview details.' : 'Scan an item barcode or click any borrowed item in the table to preview details.' }}
                </p>
            </div>
        </div>
    @endif
</div>
