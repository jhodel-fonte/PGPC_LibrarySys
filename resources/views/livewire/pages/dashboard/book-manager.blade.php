<div
    x-data="{
        fallbackCover: '{{ asset('images/book-cover.webp') }}'
    }"
    class="bg-[#F8FAFC] lg:h-full lg:flex lg:flex-col lg:min-h-0 overflow-hidden"
>
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-4 lg:h-full lg:min-h-0 lg:flex-1 overflow-hidden">
        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:shrink-0">
            <div class="flex min-w-0 items-start gap-3.5">
                <div class="min-w-0 pl-4">
                    <h1 class="text-xl font-bold tracking-[-0.02em] text-[#102B70] sm:text-2xl">Book Management</h1>
                    <p class="mt-0.5 max-w-2xl text-sm font-medium text-slate-600">Manage catalog records, physical copies, shelf locations, and availability.</p>
                </div>
            </div>

            <!-- Primary Action -->
            <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0 sm:items-center">
                <a href="{{ route('admin.book-management.add') }}" wire:navigate
                    class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    <span>Add Book</span>
                </a>

                <!-- Secondary Action -->
                <button type="button" onclick="alert('Book Import feature coming soon!')" class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-[#CBD5E1] bg-white px-4 text-sm font-semibold text-[#102B70] shadow-sm transition-colors hover:border-[#102B70] hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <span>Import Book</span>
                </button>
            </div>
        </div>

    <!-- 2. Data Table Component -->
    <x-data-table
        :headers="$this->headers"
        :sort="$sort"
        headerTextSize="text-xs"
        :tabs="[]"
        :activeTab="$activeTab"
        searchPlaceholder="Search accession, code, title, ISBN, or author..."
        :paginator="$books"
        minWidth="1000px"
        :selectable="true"
        selectAllModel="selectAll"
        :selectedCount="count($selectedCopies)"
        :showFilter="true"
        :activeFilterCount="$this->activeFilterCount"
        perPageModel="perPage"
    >
        <x-slot:toolbarLeft>
            @php
                $statusTabs = [
                    'All Copies' => ['label' => 'All', 'count' => $totalCopies],
                    'Available' => ['label' => 'Available', 'count' => $availableCopies],
                    'Borrowed' => ['label' => 'Borrowed', 'count' => $borrowedCopies],
                    'Damaged/Lost' => ['label' => 'Damaged/Lost', 'count' => $damagedLostCopies],
                ];
            @endphp
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" aria-label="Inventory view and status filters">
                @foreach(['Titles' => $totalTitles, 'Copies' => $totalCopies] as $viewName => $viewCount)
                    <button
                        type="button"
                        wire:click="setInventoryView('{{ $viewName }}')"
                        aria-pressed="{{ $inventoryView === $viewName ? 'true' : 'false' }}"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] {{ $inventoryView === $viewName ? 'border-[#102B70] bg-[#102B70] text-white shadow-sm' : 'border-[#DCE3EC] bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50' }}"
                    >
                        <span>{{ $viewName }}</span>
                        <span class="inline-flex min-w-[22px] items-center justify-center rounded px-1.5 py-0.5 text-[10px] font-bold tabular-nums {{ $inventoryView === $viewName ? 'bg-[#071943] text-white' : 'bg-slate-100 text-slate-600' }}">{{ number_format($viewCount) }}</span>
                    </button>
                @endforeach

                <span class="mx-1 h-6 w-px shrink-0 bg-slate-200" aria-hidden="true"></span>

                @foreach($statusTabs as $tabName => $tab)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tabName }}')"
                        aria-pressed="{{ $activeTab === $tabName ? 'true' : 'false' }}"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] {{ $activeTab === $tabName ? 'bg-[#102B70] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-[#102B70]' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex min-w-[22px] items-center justify-center rounded px-1.5 py-0.5 text-[10px] font-bold tabular-nums {{ $activeTab === $tabName ? 'bg-[#071943] text-white' : 'bg-slate-100 text-slate-600' }}">{{ number_format($tab['count']) }}</span>
                    </button>
                @endforeach
            </div>
        </x-slot:toolbarLeft>

        <!-- Filter Dropdown Slot -->
        <x-slot:filterDropdown>
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-[#0F172A]">Filter Options</h3>
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="text-xs font-semibold text-[#1D4ED8] hover:underline"
                >
                    Clear All
                </button>
            </div>

            <!-- Form Controls -->
            <div class="py-4 space-y-3.5">
                <!-- Location -->
                <div class="grid grid-cols-3 items-center gap-2">
                    <label for="book-filter-location" class="text-xs font-semibold text-slate-600">Location</label>
                    <div class="col-span-2 relative">
                        <select
                            wire:model.defer="filterLocation"
                            id="book-filter-location"
                            class="w-full h-9 px-3 rounded-lg border border-[#E2E8F0] bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#102B70] appearance-none cursor-pointer"
                        >
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Condition -->
                <div class="grid grid-cols-3 items-center gap-2">
                    <label for="book-filter-condition" class="text-xs font-semibold text-slate-600">Condition</label>
                    <div class="col-span-2 relative">
                        <select
                            wire:model.defer="filterCondition"
                            id="book-filter-condition"
                            class="w-full h-9 px-3 rounded-lg border border-[#E2E8F0] bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#102B70] appearance-none cursor-pointer"
                        >
                            <option value="">All Conditions</option>
                            @foreach($conditions as $cond)
                                <option value="{{ $cond->id }}">{{ $cond->status }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="grid grid-cols-3 items-center gap-2">
                    <label for="book-filter-status" class="text-xs font-semibold text-slate-600">Status</label>
                    <div class="col-span-2 relative">
                        <select
                            wire:model.defer="filterStatus"
                            id="book-filter-status"
                            class="w-full h-9 px-3 rounded-lg border border-[#E2E8F0] bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#102B70] appearance-none cursor-pointer"
                        >
                            <option value="">All Statuses</option>
                            <option value="available">Available</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="reserved">Reserved</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Author -->
                <div class="grid grid-cols-3 items-center gap-2">
                    <label for="book-filter-author" class="text-xs font-semibold text-slate-600">Author</label>
                    <input
                        wire:model.defer="filterAuthor"
                        id="book-filter-author"
                        type="text"
                        placeholder="All Authors"
                        class="col-span-2 h-9 px-3 rounded-lg border border-[#E2E8F0] bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#102B70]"
                    >
                </div>

                <!-- Publication Year -->
                <div class="grid grid-cols-3 items-center gap-2">
                    <label for="book-filter-year" class="text-xs font-semibold text-slate-600">Publication Year</label>
                    <input
                        wire:model.defer="filterYear"
                        id="book-filter-year"
                        type="number"
                        placeholder="Any"
                        class="col-span-2 h-9 px-3 rounded-lg border border-[#E2E8F0] bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#102B70]"
                    >
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    @click="filterOpen = false"
                    class="px-4 h-9 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="applyFilters"
                    @click="filterOpen = false"
                    class="h-9 rounded-lg bg-[#102B70] px-4 text-xs font-bold text-white shadow-sm transition-colors hover:bg-[#0B225E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                >
                    Apply Filters
                </button>
            </div>
        </x-slot:filterDropdown>

        <!-- Bulk Actions Slot -->
        <x-slot:bulkActions>
            <button
                type="button"
                wire:click="openBulkLocationModal"
                class="rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
            >
                Change Location
            </button>

            <button
                type="button"
                wire:click="openBulkConditionModal"
                class="rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
            >
                Update Condition
            </button>

            <button
                type="button"
                onclick="window.print()"
                class="rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
            >
                Print Labels
            </button>

            <button
                type="button"
                wire:click="bulkDelete"
                onclick="confirm('Are you sure you want to delete the selected copies?') || event.stopImmediatePropagation()"
                class="rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-red-700 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600"
            >
                Delete
            </button>

            <button
                type="button"
                wire:click="clearSelection"
                class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1"
            >
                <span>&times;</span> Clear
            </button>
        </x-slot:bulkActions>

        <!-- Rows -->
        @forelse($books as $book)
            @php
                $detail = $book->bookDetail;
                $data = $detail ? $detail->bookData : null;
                $authorName = 'Unknown Author';
                if ($data && $data->authors->isNotEmpty()) {
                    $authorName = $data->authors->map(function($a) {
                        return trim($a->first_name . ' ' . $a->last_name);
                    })->implode(', ');
                }

                $cond = strtolower($book->condition ? $book->condition->status : 'good');
                $condBadgeClass = match($cond) {
                    'new' => 'bg-[#DCFCE7] text-[#15803D]',
                    'good' => 'bg-[#DBEAFE] text-[#1D4ED8]',
                    'worn', 'fair' => 'bg-[#FEF3C7] text-[#B45309]',
                    'damaged' => 'bg-[#FFEDD5] text-[#C2410C]',
                    'lost' => 'bg-[#FEE2E2] text-[#B91C1C]',
                    default => 'bg-slate-100 text-slate-600'
                };

                $stat = strtolower($book->status);
                $statBadgeClass = match($stat) {
                    'available' => 'bg-[#DCFCE7] text-[#15803D]',
                    'borrowed' => 'bg-[#DBEAFE] text-[#1D4ED8]',
                    'damaged' => 'bg-[#FFEDD5] text-[#C2410C]',
                    'lost' => 'bg-[#FEE2E2] text-[#B91C1C]',
                    'reserved' => 'bg-amber-50 text-amber-700',
                    'maintenance' => 'bg-slate-100 text-slate-700',
                    default => 'bg-slate-100 text-slate-600'
                };

                $coverUrl = $detail && $detail->cover_image ? $detail->cover_url : null;
                $initials = collect(explode(' ', $data ? $data->book_title : 'BOOK'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
            @endphp
            <tr class="group h-[64px] transition-colors hover:bg-slate-50/70 {{ in_array((string)$book->id, $selectedCopies) ? 'bg-blue-50/60' : '' }}">
                <!-- Selection Checkbox -->
                <td class="w-12 px-4 py-3 align-middle text-center">
                    <input
                        type="checkbox"
                        wire:model.live="selectedCopies"
                        value="{{ (string)$book->id }}"
                        class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer"
                    >
                </td>

                <!-- Book Details -->
                <td x-show="cols['details'] !== false" class="px-4 py-3 align-middle max-w-[340px]">
                    <div class="flex items-center gap-3">
                        <div
                            x-data="{
                                coverSrc: @js($coverUrl) || fallbackCover
                            }"
                            class="relative flex h-11 w-8 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-[#E8EEFC] shadow-sm"
                        >
                            <img
                                :src="coverSrc || fallbackCover"
                                x-on:error="if (coverSrc !== fallbackCover) coverSrc = fallbackCover"
                                class="w-full h-full object-cover select-none"
                                alt="{{ $data ? $data->book_title : 'Book Cover' }}"
                                loading="lazy"
                            >
                        </div>

                        <div class="flex flex-col min-w-0">
                            <button
                                type="button"
                                x-on:click="$dispatch('open-book-details', { id: {{ $book->book_detail_id ?? $book->id }}, bookId: {{ $book->id }} })"
                                class="block max-w-full truncate text-left text-sm font-bold leading-snug text-[#102B70] underline-offset-4 transition-colors hover:text-blue-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                                title="View details for {{ $data ? $data->book_title : 'Unknown Title' }}"
                            >
                                {{ $data ? $data->book_title : 'Unknown Title' }}
                            </button>
                            <span class="text-xs text-slate-500 font-medium truncate mt-0.5">{{ $authorName }}</span>
                        </div>
                    </div>
                </td>

                <!-- Accession No. -->
                <td x-show="cols['accession'] !== false" class="px-4 py-3 align-middle">
                    <span class="text-sm font-medium text-slate-700">{{ $book->accession_number }}</span>
                </td>

                <!-- Unique Code -->
                <td x-show="cols['code'] !== false" class="px-4 py-3 align-middle">
                    <span class="text-sm text-slate-600">{{ $book->code ?: 'Not assigned' }}</span>
                </td>

                <!-- Location -->
                <td x-show="cols['location'] !== false" class="px-4 py-3 align-middle">
                    <span class="text-sm font-medium text-slate-700">{{ $book->location ?: 'Main Library' }}</span>
                </td>

                <!-- Condition Badge -->
                <td x-show="cols['condition'] !== false" class="px-4 py-3 align-middle">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-semibold {{ $condBadgeClass }}">
                        {{ $book->condition ? $book->condition->status : 'Good' }}
                    </span>
                </td>

                <!-- Status Badge -->
                <td x-show="cols['status'] !== false" class="px-4 py-3 align-middle">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-semibold {{ $statBadgeClass }}">
                        {{ ucfirst($book->status) }}
                    </span>
                </td>

                <!-- Actions -->
                <td class="px-4 py-3 align-middle text-right pr-6">
                    <div class="flex items-center justify-end gap-3">
                        <x-table-action-dot>
                            <button
                                type="button"
                                @click="open = false; $dispatch('open-book-details', { id: {{ $book->book_detail_id ?? $book->id }}, bookId: {{ $book->id }} })"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Details
                            </button>

                            <a
                                href="{{ route('admin.book-management.edit', $book->book_detail_id ?? $book->id) }}"
                                wire:navigate
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                Edit Book
                            </a>

                            <button
                                type="button"
                                wire:click="editCopy({{ $book->id }})"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Change Location
                            </button>

                            <button
                                type="button"
                                wire:click="editCopy({{ $book->id }})"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                Update Condition
                            </button>

                            <button
                                type="button"
                                wire:click="viewHistory({{ $book->id }})"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                View History
                            </button>

                            <div class="border-t border-slate-100 my-1"></div>

                            <button
                                type="button"
                                wire:click="deleteCopy({{ $book->id }})"
                                @disabled($book->status === 'borrowed')
                                @click="open = false"
                                onclick="confirm('Are you sure you want to delete copy {{ $book->accession_number }}?') || event.stopImmediatePropagation()"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <svg class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete Copy
                            </button>
                        </x-table-action-dot>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-16 text-center">
                    <div class="mx-auto flex max-w-sm flex-col items-center">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">No matching book copies</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Try a different search term or clear the active filters.</p>
                        @if($this->activeFilterCount > 0)
                            <button type="button" wire:click="clearFilters" class="mt-4 text-xs font-semibold text-[#102B70] underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">Clear filters</button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </x-data-table>
    </div>

    <!-- 3. SINGLE EDIT MODAL -->
    @if($showEditModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl animate-fade-in">
                <div class="px-6 py-5 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#102B70]">Edit Copy Settings</h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5 uppercase tracking-wider">Accession: {{ $editAccessionNumber }}</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-[#0F172A] text-2xl font-bold select-none">&times;</button>
                </div>

                <form wire:submit.prevent="saveCopy" class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Item Code / Barcode</label>
                        <input type="text" value="{{ $editCode }}" disabled class="h-[42px] w-full cursor-not-allowed rounded-lg border border-[#E2E8F0] bg-slate-50 px-3.5 text-xs font-bold text-slate-500 outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label for="editLocation" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Shelf Location</label>
                        <input
                            wire:model="editLocation"
                            type="text"
                            id="editLocation"
                            placeholder="e.g. Shelf A-2, Section B"
                            class="h-[42px] w-full rounded-lg border border-[#E2E8F0] bg-white px-3.5 text-xs font-semibold text-[#0F172A] outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                        >
                        @error('editLocation') <span class="text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="editConditionId" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Physical Condition</label>
                        <div class="relative">
                            <select
                                wire:model="editConditionId"
                                id="editConditionId"
                                class="h-[42px] w-full cursor-pointer appearance-none rounded-lg border border-[#E2E8F0] bg-white px-3.5 text-xs font-semibold text-[#0F172A] outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            >
                                <option value="">Select Condition</option>
                                @foreach($conditions as $condition)
                                    <option value="{{ $condition->id }}">{{ $condition->status }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('editConditionId') <span class="text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="editStatus" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Circulation Status</label>
                        <div class="relative">
                            <select
                                wire:model="editStatus"
                                id="editStatus"
                                class="h-[42px] w-full cursor-pointer appearance-none rounded-lg border border-[#E2E8F0] bg-white px-3.5 text-xs font-semibold text-[#0F172A] outline-none transition-colors focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            >
                                <option value="available">Available</option>
                                <option value="borrowed">Borrowed</option>
                                <option value="reserved">Reserved</option>
                                <option value="damaged">Damaged</option>
                                <option value="lost">Lost</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('editStatus') <span class="text-xs font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-[#F1F5F9] flex justify-end gap-2.5">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="px-4 h-10 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs uppercase tracking-wider font-bold rounded-xl transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="flex h-10 items-center gap-1.5 rounded-lg bg-[#102B70] px-[18px] text-xs font-bold uppercase tracking-wider text-white shadow-sm transition-colors hover:bg-[#0B225E] disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="saveCopy">Save Changes</span>
                            <span wire:loading.flex wire:target="saveCopy" class="items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 4. BULK CHANGE LOCATION MODAL -->
    @if($showBulkLocationModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-2xl animate-fade-in">
                <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#102B70]">Change Shelf Location</h3>
                    <button type="button" wire:click="$set('showBulkLocationModal', false)" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
                </div>
                <form wire:submit.prevent="saveBulkLocation" class="p-6 space-y-4">
                    <p class="text-xs text-slate-500 font-medium">Update location for <strong>{{ count($selectedCopies) }}</strong> selected copies.</p>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">New Shelf Location</label>
                        <input
                            wire:model="bulkLocation"
                            type="text"
                            placeholder="e.g. Shelf B-4, Section C"
                            class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold outline-none focus:border-[#102B70]"
                            required
                        >
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showBulkLocationModal', false)" class="px-3.5 h-9 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600">Cancel</button>
                        <button type="submit" class="h-9 rounded-lg bg-[#102B70] px-4 text-xs font-bold text-white shadow-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 5. BULK UPDATE CONDITION MODAL -->
    @if($showBulkConditionModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-2xl animate-fade-in">
                <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#102B70]">Update Copy Condition</h3>
                    <button type="button" wire:click="$set('showBulkConditionModal', false)" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
                </div>
                <form wire:submit.prevent="saveBulkCondition" class="p-6 space-y-4">
                    <p class="text-xs text-slate-500 font-medium">Update condition for <strong>{{ count($selectedCopies) }}</strong> selected copies.</p>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Physical Condition</label>
                        <select
                            wire:model="bulkConditionId"
                            class="w-full h-10 px-3 rounded-xl border border-[#E2E8F0] bg-white text-xs font-semibold outline-none focus:border-[#102B70]"
                            required
                        >
                            <option value="">Select Condition</option>
                            @foreach($conditions as $cond)
                                <option value="{{ $cond->id }}">{{ $cond->status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showBulkConditionModal', false)" class="px-3.5 h-9 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600">Cancel</button>
                        <button type="submit" class="h-9 rounded-lg bg-[#102B70] px-4 text-xs font-bold text-white shadow-sm">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 6. VIEW HISTORY MODAL -->
    @if($showHistoryModal && $historyBook)
        <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl animate-fade-in">
                <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#102B70]">Circulation History</h3>
                        <p class="text-xs text-slate-500 font-medium">Accession: {{ $historyBook->accession_number }}</p>
                    </div>
                    <button type="button" wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
                </div>
                <div class="p-6 text-xs max-h-96 overflow-y-auto">
                    @if($historyBook->borrowingTransactions && $historyBook->borrowingTransactions->isNotEmpty())
                        <div class="divide-y divide-slate-100 space-y-2">
                            @foreach($historyBook->borrowingTransactions as $tx)
                                <div class="pt-2 first:pt-0 flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $tx->user ? $tx->user->first_name . ' ' . $tx->user->last_name : 'Unknown User' }}</p>
                                        <p class="text-[11px] text-slate-500">Borrowed: {{ $tx->borrowed_date ? \Carbon\Carbon::parse($tx->borrowed_date)->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $tx->status === 'returned' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-6">No previous borrowing transactions recorded for this copy.</p>
                    @endif
                </div>
                <div class="px-6 py-3 border-t border-slate-100 flex justify-end">
                    <button type="button" wire:click="closeHistoryModal" class="px-4 h-9 rounded-lg bg-[#102B70] text-white text-xs font-bold">Close</button>
                </div>
            </div>
        </div>
    @endif

    <livewire:components.book-manager.edit-book-modal />
</div>
