<div class="space-y-6 font-sans pb-24">

    <!-- Scoped style for always-visible prominent side scrollbar -->
    <style>
        .marc-table-scroll {
            overflow-y: scroll !important;
            scrollbar-width: thin;
            scrollbar-color: #94A3B8 #F1F5F9;
        }
        .marc-table-scroll::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .marc-table-scroll::-webkit-scrollbar-track {
            background: #F1F5F9;
            border-left: 1px solid #E2E8F0;
        }
        .marc-table-scroll::-webkit-scrollbar-thumb {
            background-color: #94A3B8;
            border-radius: 9999px;
            border: 2px solid #F1F5F9;
        }
        .marc-table-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #102B70;
        }
    </style>

    <!-- ============================================== -->
    <!-- SUCCESS BANNER (SIMULATION)                    -->
    <!-- ============================================== -->
    @if($importCompleted)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-5 shadow-xs transition-all animate-fade-in">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <div>
                        <h3 class="text-base font-bold text-emerald-950">Catalog Batch Successfully Created</h3>
                        <p class="text-sm font-medium text-emerald-800 mt-1 leading-relaxed">
                            <strong class="font-bold">{{ $importedCount }}</strong> bibliographic books have been parsed and committed into the <strong class="font-bold">{{ $defaultCollection }}</strong> section with accession numbers starting from <strong class="font-bold">{{ $accessionPrefix }}{{ $startAccessionNumber }}</strong>.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto justify-end">
                    <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 text-sm font-bold text-white shadow-xs hover:bg-emerald-800 transition">
                        <span>View in Catalog</span>
                    </a>
                    <button type="button" wire:click="resetImportState"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-5 text-sm font-bold text-emerald-800 hover:bg-emerald-50 transition">
                        <span>Review Records Again</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- PREVIEW TABLE CONTAINER CARD                   -->
    <!-- ============================================== -->
    <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">

        <!-- Filter Bar & Search Toolbar -->
        <div class="p-5 sm:p-6 border-b border-[#F1F5F9] space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Filter Tabs -->
                <div class="flex items-center gap-2 p-1.5 rounded-xl bg-slate-100 border border-slate-200/60 overflow-x-auto">
                    <button type="button" wire:click="setTab('all')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $activeTab === 'all' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-[#0F172A]' }}">
                        All Records ({{ $totalCount }})
                    </button>
                    <button type="button" wire:click="setTab('ready')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $activeTab === 'ready' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700' }}">
                        Ready ({{ $fileSummary['ready_count'] ?? 0 }})
                    </button>
                    <button type="button" wire:click="setTab('warnings')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $activeTab === 'warnings' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700' }}">
                        Warnings ({{ $fileSummary['warnings_count'] ?? 0 }})
                    </button>
                </div>

                <!-- Batch Selector Shortcuts -->
                @php
                    $allFilteredSelected = count($filteredRecords) > 0 && empty(array_diff(array_map(fn($r) => (string)$r['id'], $filteredRecords), $selectedRecords));
                @endphp
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="selectOnlyReady"
                        class="text-sm font-bold text-[#102B70] hover:text-[#0B225E] bg-[#EFF6FF] px-4 py-2 rounded-xl border border-[#BFDBFE] transition active:scale-[0.98]">
                        Select All Ready ({{ $fileSummary['ready_count'] ?? 0 }})
                    </button>
                    <button type="button" wire:click="toggleSelectAll"
                        class="text-sm font-semibold text-slate-700 hover:text-slate-900 px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 transition">
                        {{ $allFilteredSelected ? 'Deselect All' : 'Select All (' . count($filteredRecords) . ')' }}
                    </button>
                </div>
            </div>

            <!-- Search Bar with Scrollable Helper -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search extracted books by title, author, ISBN, or call number..."
                        class="w-full h-12 pl-12 pr-4 rounded-xl border border-[#CBD5E1] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    />
                </div>
            </div>
        </div>

        <label class="md:hidden flex items-center gap-2 border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700">
            <input type="checkbox" wire:click="toggleSelectAll" @checked($allFilteredSelected) class="h-4 w-4 rounded border-slate-300 text-[#102B70]">
            Select all filtered records
        </label>
        <!-- PREVIEW TABLE CONTAINER WITH ALWAYS-VISIBLE VERTICAL SCROLLBAR ON THE SIDE -->
        <div class="admin-mobile-table-scroll overflow-x-auto overflow-y-scroll max-h-[480px] sm:max-h-[520px] marc-table-scroll visible-table-scrollbar relative border-b border-[#E2E8F0] min-h-[300px]">
            <table class="admin-mobile-table admin-marc-table w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur-xs shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                    <tr class="border-b border-[#E2E8F0] text-[#334155] font-bold uppercase tracking-wider text-xs">
                        <th class="py-4 pl-5 pr-2 w-12 text-center bg-slate-50/95">
                            <input
                                type="checkbox"
                                wire:click="toggleSelectAll"
                                {{ $allFilteredSelected ? 'checked' : '' }}
                                class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] h-4 w-4 cursor-pointer"
                            />
                        </th>
                        <th class="py-4 px-4 min-w-[340px] bg-slate-50/95">Title & Bibliographic Details</th>
                        <th class="py-4 px-4 min-w-[200px] bg-slate-50/95">Author(s)</th>
                        <th class="py-4 px-4 min-w-[170px] bg-slate-50/95">ISBN / Identifiers</th>
                        <th class="py-4 px-4 min-w-[150px] bg-slate-50/95">Call Number</th>
                        <th class="py-4 px-4 min-w-[190px] bg-slate-50/95">Publication</th>
                        <th class="py-4 pr-5 pl-2 text-right min-w-[90px] bg-slate-50/95">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($paginatedRecords as $rec)
                        @php
                            $isSelected = in_array((string)$rec['id'], $selectedRecords);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $isSelected ? 'bg-blue-50/40' : '' }}">
                            <!-- Checkbox -->
                            <td data-label="Select" class="py-4 pl-5 pr-2 text-center align-top pt-5">
                                <input
                                    type="checkbox"
                                    value="{{ $rec['id'] }}"
                                    wire:model.live="selectedRecords"
                                    class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] h-4 w-4 cursor-pointer"
                                />
                            </td>

                            <!-- Title & Info -->
                            <td data-label="Title and details" class="py-4 px-4 align-top">
                                <div class="text-sm font-bold text-[#102B70] leading-snug">
                                    {{ $rec['title'] }}
                                </div>
                                @if(!empty($rec['subtitle']))
                                    <div class="text-xs text-slate-500 mt-0.5 leading-normal">
                                        {{ $rec['subtitle'] }}
                                    </div>
                                @endif
                                <div class="text-xs text-slate-500 font-medium mt-1.5 flex items-center gap-1.5">
                                    @if(!empty($rec['edition']))
                                        <span>{{ $rec['edition'] }}</span>
                                        <span>&bull;</span>
                                    @endif
                                    <span>{{ $rec['language'] }}</span>
                                    @if(!empty($rec['pages']))
                                        <span>&bull;</span>
                                        <span>{{ $rec['pages'] }}</span>
                                    @endif
                                </div>

                                <!-- Warning note if any -->
                                @if($rec['status'] === 'warning' && !empty($rec['warning_message']))
                                    <div class="mt-2.5 rounded-xl bg-amber-50 border border-amber-200 p-2.5 text-xs text-amber-900 leading-normal flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ $rec['warning_message'] }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Authors -->
                            <td data-label="Authors" class="py-4 px-4 align-top">
                                <div class="text-sm font-semibold text-slate-800 leading-snug">
                                    {{ $rec['primary_author'] }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5 leading-relaxed" title="{{ $rec['authors'] }}">
                                    {{ $rec['authors'] }}
                                </div>
                            </td>

                            <!-- ISBN -->
                            <td data-label="ISBN / identifiers" class="py-4 px-4 align-top whitespace-nowrap">
                                <div class="text-sm font-semibold {{ !empty($rec['isbn']) ? 'text-slate-800' : 'text-slate-400 italic' }} leading-snug">
                                    {{ $rec['isbn'] ?: 'No ISBN' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $rec['control_number'] }}
                                </div>
                            </td>

                            <!-- Call Number -->
                            <td data-label="Call number" class="py-4 px-4 align-top whitespace-nowrap">
                                @if(!empty($rec['call_number']) && $rec['call_number'] !== 'UNASSIGNED')
                                    <div class="text-sm font-semibold text-slate-800 leading-snug">
                                        {{ $rec['call_number'] }}
                                    </div>
                                    @if(!empty($rec['dewey']))
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            DDC {{ $rec['dewey'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm font-medium text-amber-700 italic">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            <!-- Publisher & Year -->
                            <td data-label="Publication" class="py-4 px-4 align-top">
                                <div class="text-sm font-semibold text-slate-800 leading-snug">
                                    {{ $rec['publisher'] }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    @if(!empty($rec['place']))
                                        {{ $rec['place'] }} &bull;
                                    @endif
                                    {{ $rec['year'] }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td data-label="Actions" class="py-4 pr-5 pl-2 align-top text-right whitespace-nowrap space-x-1.5 pt-4">
                                <!-- View Raw Tags Button (Icon Only with Instant Loading State) -->
                                <button
                                    type="button"
                                    wire:click="openMarcModal('{{ $rec['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="openMarcModal('{{ $rec['id'] }}')"
                                    class="p-2 rounded-lg text-[#102B70] hover:text-[#0B225E] hover:bg-blue-50 disabled:opacity-50 disabled:cursor-wait border border-slate-200 bg-white transition inline-flex items-center justify-center shadow-2xs relative"
                                    title="View Raw Tags"
                                >
                                    <span wire:loading.remove wire:target="openMarcModal('{{ $rec['id'] }}')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                        </svg>
                                    </span>
                                    <span wire:loading.flex wire:target="openMarcModal('{{ $rec['id'] }}')" class="items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Remove from list -->
                                <button
                                    type="button"
                                    wire:click="removeRecord('{{ $rec['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="removeRecord('{{ $rec['id'] }}')"
                                    class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 disabled:opacity-50 disabled:cursor-wait border border-transparent hover:border-rose-200 transition inline-flex items-center justify-center"
                                    title="Exclude from import"
                                >
                                    <span wire:loading.remove wire:target="removeRecord('{{ $rec['id'] }}')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </span>
                                    <span wire:loading.flex wire:target="removeRecord('{{ $rec['id'] }}')" class="items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">
                                @if($totalCount === 0 && !empty($importKey))
                                    <div class="flex flex-col items-center justify-center space-y-2 max-w-md mx-auto">
                                        <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-1">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="font-bold text-base text-slate-800">30-Minute Cache Expired</p>
                                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">The temporary 30-minute memory cache for this batch has expired. Please upload the MARC file again to continue cataloging.</p>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="font-bold text-base text-slate-700">No books found matching your filters</p>
                                        <p class="text-sm text-slate-500">Try adjusting your search keywords or tab view.</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TABLE FOOTER & PAGINATION -->
        <div class="p-4 sm:p-5 bg-slate-50 border-t border-[#E2E8F0] flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-600 font-medium">
            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto justify-between md:justify-start">
                {{-- Per Page Dropdown --}}
                <div class="flex items-center gap-2">
                    <label for="marcPerPageSelect" class="text-xs font-bold uppercase tracking-wider text-slate-500">Show</label>
                    <select
                        id="marcPerPageSelect"
                        wire:model.live="perPage"
                        class="h-8.5 px-2.5 py-1 rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-[#102B70] shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs text-slate-500">entries per page</span>
                </div>

                {{-- Showing count range --}}
                <div class="text-xs sm:text-sm text-slate-600">
                    @if($paginatedRecords->total() > 0)
                        Showing <strong class="font-bold text-slate-800">{{ $paginatedRecords->firstItem() }}</strong> to <strong class="font-bold text-slate-800">{{ $paginatedRecords->lastItem() }}</strong> of <strong class="font-bold text-[#102B70]">{{ $paginatedRecords->total() }}</strong> records
                    @else
                        No records to display
                    @endif
                    @if(count($selectedRecords) > 0)
                        <span class="ml-2 font-bold text-[#102B70] bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 text-xs">
                            {{ count($selectedRecords) }} selected
                        </span>
                    @endif
                </div>
            </div>

            {{-- Pagination links --}}
            <div class="flex items-center gap-2">
                {{ $paginatedRecords->links('components.widget.pagination') }}
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- BOTTOM ACTION BAR                              -->
    <!-- ============================================== -->
    <div class="rounded-2xl border border-[#CBD5E1] bg-white p-5 sm:p-6 shadow-[0_8px_30px_rgba(16,43,112,0.06)] flex flex-col sm:flex-row items-center justify-between gap-4 mb-20">
        <div>
            <div class="text-base sm:text-lg font-bold text-[#102B70]">
                Confirm to Import Books
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-end">
            <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                class="h-12 px-6 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-bold text-slate-700 shadow-2xs transition active:scale-[0.98] inline-flex items-center justify-center">
                <span>Cancel</span>
            </a>

            <button
                type="button"
                wire:click="importSelectedRecords"
                wire:loading.attr="disabled"
                wire:target="importSelectedRecords"
                @if(count($selectedRecords) === 0) disabled @endif
                class="h-12 px-7 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] disabled:opacity-50 disabled:cursor-not-allowed text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer min-w-[180px]"
            >
                {{-- Normal State: icon + text --}}
                <span wire:loading.remove wire:target="importSelectedRecords" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import Selected Records ({{ count($selectedRecords) }})</span>
                </span>

                {{-- Loading State --}}
                <span wire:loading.flex wire:target="importSelectedRecords" class="items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Importing Records...</span>
                </span>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MARC21 RAW TAG INSPECTION MODAL                -->
    <!-- ============================================== -->
    @if($showMarcModal && $activeRecordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
            x-data
            @keydown.escape.window="$wire.closeMarcModal()"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity animate-fade-in cursor-pointer"
                wire:click="closeMarcModal"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-5xl xl:max-w-6xl border border-[#E2E8F0]">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#102B70] px-6 py-5 flex items-start justify-between text-white">
                        <div class="min-w-0 pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold tracking-wider uppercase text-blue-200">
                                    MARC21 Record
                                </span>
                                <span class="text-xs text-white/70 font-mono">
                                    #{{ $activeRecordModal['control_number'] }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white mt-1 leading-snug" id="modal-title">
                                {{ $activeRecordModal['title'] }}
                            </h3>

                            <!-- Metadata Chips -->
                            <div class="flex flex-wrap items-center gap-2 mt-2.5">
                                @if(!empty($activeRecordModal['isbn']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-white/15 text-white">
                                        ISBN: {{ $activeRecordModal['isbn'] }}
                                    </span>
                                @endif
                                @if(!empty($activeRecordModal['call_number']) && $activeRecordModal['call_number'] !== 'UNASSIGNED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-white/15 text-white">
                                        Call: {{ $activeRecordModal['call_number'] }}
                                    </span>
                                @endif
                                @if(!empty($activeRecordModal['primary_author']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-white/15 text-white">
                                        Author: {{ $activeRecordModal['primary_author'] }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#FCC719] text-[#102B70]">
                                    {{ count($activeRecordModal['marc_fields'] ?? []) }} Tags
                                </span>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeMarcModal"
                            class="rounded-xl p-2 text-white/70 hover:text-white hover:bg-white/10 transition shrink-0 cursor-pointer"
                            title="Close modal (Esc)"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body / MARC Fields Table -->
                    <div class="p-6 max-h-[72vh] overflow-y-scroll marc-table-scroll visible-table-scrollbar space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs sm:text-sm text-slate-500 pb-2 border-b border-slate-100">
                            <span>Field Tag Structure &amp; Subfield Mapping</span>
                            <span class="text-slate-800 font-mono font-bold bg-slate-100 px-2 py-0.5 rounded">LDR: {{ $activeRecordModal['leader'] }}</span>
                        </div>

                        <div class="rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="sticky top-0 z-10 bg-slate-100/95 backdrop-blur-xs shadow-2xs">
                                    <tr class="border-b border-slate-200 text-slate-700 font-bold uppercase text-xs">
                                        <th class="py-3 px-4 w-20">Tag</th>
                                        <th class="py-3 px-3 w-16 text-center">Ind</th>
                                        <th class="py-3 px-4">Field Data &amp; Subfields</th>
                                        <th class="py-3 px-4 text-right w-64 sm:w-72 text-slate-600">Field Name</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-medium">
                                    @foreach($activeRecordModal['marc_fields'] as $f)
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="py-3 px-4 font-mono font-bold text-[#102B70] text-sm">
                                                {{ $f['tag'] }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono text-slate-400 font-bold text-sm bg-slate-50/40">
                                                {{ $f['ind1'] }}{{ $f['ind2'] }}
                                            </td>
                                            <td class="py-3 px-4 text-slate-900 break-words font-mono text-xs sm:text-sm leading-relaxed">
                                                {{ $f['data'] }}
                                            </td>
                                            <td class="py-3 px-4 text-right text-xs text-slate-500 font-medium">
                                                {{ $f['desc'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-[#E2E8F0] flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-xs sm:text-sm text-slate-600 font-medium">
                                Catalog Status:
                                <strong class="uppercase font-bold {{ $activeRecordModal['status'] === 'ready' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $activeRecordModal['status'] }}
                                </strong>
                            </span>
                            @if(!empty($activeRecordModal['warning_message']))
                                <span class="text-xs text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    {{ $activeRecordModal['warning_message'] }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="closeMarcModal"
                                class="h-10 px-6 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-100 text-sm font-bold text-slate-700 transition shadow-2xs cursor-pointer"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
