<div
    class="flex flex-col w-full h-[500px] max-h-[500px] rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden animate-fade-in"
>
    @if($bookDetail)
        @php
            $data = $bookDetail->bookData;
            $authors = $data && $data->authors ? $data->authors : collect();
            $authorNames = $authors->isNotEmpty()
                ? $authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->filter()->implode(', ')
                : 'Unknown Author';
            $coverUrl = $bookDetail->cover_url ?: asset('images/book-cover.webp');
        @endphp

        <!-- Header (Pinned) -->
        <header class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3 bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold tracking-[-0.01em] text-[#102B70]">Add Physical Copy</h3>
                <p class="mt-0.5 text-xs text-slate-500 leading-relaxed max-w-sm">
                    Add another physical copy of this title.
                </p>
            </div>
            <button
                type="button"
                wire:click="close"
                class="-mr-1 inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                aria-label="Close Add Physical Copy"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <!-- Form Body -->
        <form wire:submit.prevent="saveCopy" class="flex min-h-0 flex-1 flex-col overflow-hidden">
            <!-- Scrollable Content Only -->
            <div class="custom-scrollbar min-h-0 flex-1 overflow-y-auto p-5 space-y-3.5">
                
                {{-- <!-- Book (Title) Preview Box -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Book (Title)</label>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200/90 bg-slate-50/70 p-3 shadow-xs">
                        <div class="relative h-12 w-9 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-[#E8EEFC] shadow-xs">
                            <img
                                src="{{ $coverUrl }}"
                                class="h-full w-full object-cover select-none"
                                alt="{{ $data ? $data->book_title : 'Book Cover' }}"
                            >
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-bold text-[#0F172A] truncate" title="{{ $data ? $data->book_title : 'Unknown Title' }}">
                                {{ $data ? $data->book_title : 'Unknown Title' }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Author: {{ $authorNames }}</p>
                            <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">ISBN: {{ $bookDetail->isbn ?: ($bookDetail->issn ?: 'N/A') }}</p>
                        </div>
                    </div>
                </div> --}}

                <!-- Accession Number -->
                <div>
                    <label for="copy-accession-number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Accession Number <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        wire:model.defer="accessionNumber"
                        type="text"
                        id="copy-accession-number"
                        placeholder="Enter accession number"
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                    >
                    @error('accessionNumber')
                        <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unique Code (Auto-Generated) -->
                <div>
                    <label for="copy-unique-code" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Unique Code
                    </label>
                    <div class="relative">
                        <input
                            wire:model.defer="uniqueCode"
                            type="text"
                            id="copy-unique-code"
                            readonly
                            class="h-10 w-full rounded-lg border border-slate-200 bg-slate-100/80 px-3.5 pr-9 text-xs font-semibold text-slate-600 outline-none select-all cursor-default"
                        >
                        <button
                            type="button"
                            wire:click="generateUniqueCode"
                            title="Generate New Code"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-[#102B70] transition-colors"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">Generated automatically</p>
                    @error('uniqueCode')
                        <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 2-Column: Shelf / Location & Condition -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Shelf / Location -->
                    <div>
                        <label for="copy-shelf-location" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Shelf / Location <span class="text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input
                                wire:model.defer="location"
                                type="text"
                                id="copy-shelf-location"
                                list="copy-location-options"
                                placeholder="e.g. 231"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3.5 pr-8 text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            >
                            <datalist id="copy-location-options">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}"></option>
                                @endforeach
                            </datalist>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                        @error('location')
                            <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Condition -->
                    <div>
                        <label for="copy-condition" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Condition <span class="text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <select
                                wire:model.defer="conditionId"
                                id="copy-condition"
                                class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white px-3.5 pr-8 text-xs font-semibold text-slate-800 outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            >
                                <option value="">Select Condition</option>
                                @foreach($conditions as $cond)
                                    <option value="{{ $cond->id }}">{{ $cond->status }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                        @error('conditionId')
                            <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Initial Status -->
                <div>
                    <label for="copy-initial-status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Initial Status <span class="text-red-500 font-bold">*</span>
                    </label>
                    <div class="relative">
                        <select
                            wire:model.defer="status"
                            id="copy-initial-status"
                            class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white px-3.5 pr-8 text-xs font-semibold text-slate-800 outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                        >
                            <option value="available">Available</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="reserved">Reserved</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                    @error('status')
                        <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes (Optional) -->
                <div x-data="{ noteVal: @entangle('notes').defer }">
                    <label for="copy-notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Notes (Optional)
                    </label>
                    <textarea
                        x-model="noteVal"
                        id="copy-notes"
                        maxlength="500"
                        rows="3"
                        placeholder="Add any notes about this physical copy..."
                        class="w-full rounded-lg border border-slate-200 bg-white p-3 text-xs font-medium text-slate-800 placeholder:text-slate-400 outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] resize-none"
                    ></textarea>
                    <div class="flex justify-end mt-1">
                        <span class="text-[11px] text-slate-400 tabular-nums">
                            <span x-text="(noteVal || '').length">0</span>/500
                        </span>
                    </div>
                    @error('notes')
                        <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Footer Actions -->
            <footer class="flex shrink-0 items-center justify-end gap-2.5 border-t border-slate-200 bg-white px-6 py-3.5">
                <button
                    type="button"
                    wire:click="close"
                    class="h-9 rounded-lg border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 active:translate-y-px"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#102B70] px-4 text-xs font-bold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px disabled:opacity-60"
                >
                    <svg wire:loading.remove wire:target="saveCopy" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    <svg wire:loading wire:target="saveCopy" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Add Copy</span>
                </button>
            </footer>
        </form>
    @endif
</div>
