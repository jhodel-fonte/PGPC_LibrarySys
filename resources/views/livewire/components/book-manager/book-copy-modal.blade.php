<div
    x-data="{
        init() {
            this.$nextTick(() => {
                this.focusFirstInput();
            });
        },
        focusFirstInput() {
            setTimeout(() => {
                const el = document.getElementById('copy-accession-number');
                if (el) {
                    el.focus();
                    el.select();
                }
            }, 60);
        }
    }"
    x-on:focus-accession-input.window="focusFirstInput()"
    x-init="focusFirstInput()"
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
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold tracking-[-0.01em] text-[#102B70]">
                        {{ $isEditing ? 'Edit Physical Copy' : 'Add Physical Copy' }}
                    </h3>
                    @if(!$isEditing && $isMultiple)
                        <span class="inline-flex items-center gap-1 bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-[#102B70]">
                            @if($addedCount > 0)
                                {{ $addedCount }} added
                            @endif
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-slate-500 leading-relaxed max-w-sm">
                    @if($isEditing)
                        Update details and status for copy #{{ $accessionNumber }}.
                    @elseif($isMultiple)
                        Continuous entry mode. Each saved copy is added immediately.
                    @else
                        Add another physical copy of this title.
                    @endif
                </p>
            </div>
            <button
                type="button"
                wire:click="close"
                class="-mr-1 inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                aria-label="{{ $isEditing ? 'Close Edit Physical Copy' : 'Close Add Physical Copy' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <!-- Form Body -->
        <form wire:submit.prevent="saveCopy" class="flex min-h-0 flex-1 flex-col overflow-hidden">
            <!-- Scrollable Content Only -->
            <div class="custom-scrollbar min-h-0 flex-1 overflow-y-auto p-5 space-y-3.5">

                <!-- Accession Number -->
                <div>
                    <label for="copy-accession-number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Accession Number <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        wire:model.defer="accessionNumber"
                        type="text"
                        id="copy-accession-number"
                        tabindex="1"
                        placeholder="Enter accession number"
                        class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                    >
                    @error('accessionNumber')
                        <p class="text-[11px] font-semibold text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unique Code / Barcode (Auto-Generated or Custom) -->
                <div>
                    <label for="copy-unique-code" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Unique Code / Barcode
                    </label>
                    <div class="relative">
                        <input
                            wire:model.defer="uniqueCode"
                            type="text"
                            id="copy-unique-code"
                            tabindex="2"
                            placeholder="e.g. BK-2026-XXXX or custom code"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3.5 pr-9 text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                        >
                        <button
                            type="button"
                            wire:click="generateUniqueCode"
                            tabindex="-1"
                            title="Generate New Code"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-[#102B70] transition-colors cursor-pointer"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">Auto-generated. You can also manually enter your own barcode or custom unique code.</p>
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
                                tabindex="3"
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
                                tabindex="4"
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
                        {{ $isEditing ? 'Status' : 'Initial Status' }} <span class="text-red-500 font-bold">*</span>
                    </label>
                    <div class="relative">
                        <select
                            wire:model.defer="status"
                            id="copy-initial-status"
                            tabindex="5"
                            class="h-10 w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white px-3.5 pr-8 text-xs font-semibold text-slate-800 outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                        >
                            <option value="available">Available</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="reserved">Reserved</option>
                            <option value="damaged">Damaged/Lost</option>
                            <option value="lost">Lost</option>
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
                        tabindex="6"
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
            <footer class="flex shrink-0 items-center justify-between border-t border-slate-200 bg-white px-6 py-3.5">
                <div>
                    @if(!$isEditing && $isMultiple && $addedCount > 0)
                        <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $addedCount }} {{ $addedCount === 1 ? 'copy' : 'copies' }} saved
                        </span>
                    @elseif($isEditing && auth()->check() && auth()->user()->can('delete', \App\Models\Book::class))
                        <button
                            type="button"
                            wire:click="confirmDelete"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/60 px-3 text-xs font-semibold text-red-600 transition-colors hover:border-red-300 hover:bg-red-100 hover:text-red-700 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                            title="Delete this physical copy"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span>Delete Copy</span>
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        wire:click="close"
                        tabindex="7"
                        class="h-9 rounded-lg border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 active:translate-y-px"
                    >
                        {{ !$isEditing && $isMultiple && $addedCount > 0 ? 'Done' : 'Cancel' }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        tabindex="6"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-[#102B70] px-4 text-xs font-bold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px disabled:opacity-60"
                    >
                        <svg wire:loading.remove wire:target="saveCopy" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            @if($isEditing)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            @endif
                        </svg>
                        <svg wire:loading wire:target="saveCopy" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ $isEditing ? 'Save Changes' : ($isMultiple ? 'Save & Add Another' : 'Add Copy') }}</span>
                    </button>
                </div>
            </footer>
        </form>

        @if($isEditing)
            @include('livewire.components.usermanager.alert-modal', [
                'show' => $showDeleteModal,
                'type' => 'danger',
                'title' => 'Delete Physical Copy',
                'subtitle' => 'Confirm Copy Deletion',
                'targetRole' => 'copy',
                'targetName' => $accessionNumber,
                'message' => 'Are you sure you want to delete physical copy <strong>' . e($accessionNumber) . '</strong>? This copy will be permanently removed from circulation records.',
                'closeAction' => 'cancelDelete',
                'confirmAction' => 'deleteCopy',
                'confirmText' => 'Delete Copy',
            ])
        @endif
    @endif
</div>
