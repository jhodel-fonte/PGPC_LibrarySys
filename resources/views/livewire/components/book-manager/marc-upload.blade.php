<div class="space-y-6 font-sans pb-24">

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
                    <button type="button" wire:click="resetSimulation"
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
                        All Records ({{ count($records) }})
                    </button>
                    <button type="button" wire:click="setTab('ready')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $activeTab === 'ready' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700' }}">
                        Ready ({{ $fileSummary['ready_count'] }})
                    </button>
                    <button type="button" wire:click="setTab('warnings')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap {{ $activeTab === 'warnings' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700' }}">
                        Warnings ({{ $fileSummary['warnings_count'] }})
                    </button>
                </div>

                <!-- Batch Selector Shortcuts -->
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="selectOnlyReady"
                        class="text-sm font-bold text-[#102B70] hover:text-[#0B225E] bg-[#EFF6FF] px-4 py-2 rounded-xl border border-[#BFDBFE] transition active:scale-[0.98]">
                        Select All Ready ({{ $fileSummary['ready_count'] }})
                    </button>
                    <button type="button" wire:click="toggleSelectAll"
                        class="text-sm font-semibold text-slate-700 hover:text-slate-900 px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 transition">
                        {{ count($selectedRecords) === count($filteredRecords) && count($filteredRecords) > 0 ? 'Deselect All' : 'Select All' }}
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full">
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

        <!-- PREVIEW TABLE -->
        <div class="overflow-x-auto min-h-[340px]">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-[#E2E8F0] text-[#334155] font-bold uppercase tracking-wider text-xs">
                        <th class="py-4 pl-5 pr-2 w-12 text-center">
                            <input
                                type="checkbox"
                                wire:click="toggleSelectAll"
                                {{ count($selectedRecords) === count($filteredRecords) && count($filteredRecords) > 0 ? 'checked' : '' }}
                                class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] h-4 w-4 cursor-pointer"
                            />
                        </th>
                        <th class="py-4 px-4 min-w-[340px]">Title & Bibliographic Details</th>
                        <th class="py-4 px-4 min-w-[200px]">Author(s)</th>
                        <th class="py-4 px-4 min-w-[170px]">ISBN / Identifiers</th>
                        <th class="py-4 px-4 min-w-[150px]">Call Number</th>
                        <th class="py-4 px-4 min-w-[190px]">Publication</th>
                        <th class="py-4 pr-5 pl-2 text-right min-w-[90px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($filteredRecords as $rec)
                        @php
                            $isSelected = in_array((string)$rec['id'], $selectedRecords);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $isSelected ? 'bg-blue-50/40' : '' }}">
                            <!-- Checkbox -->
                            <td class="py-4 pl-5 pr-2 text-center align-top pt-5">
                                <input
                                    type="checkbox"
                                    value="{{ $rec['id'] }}"
                                    wire:model.live="selectedRecords"
                                    class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] h-4 w-4 cursor-pointer"
                                />
                            </td>

                            <!-- Title & Info -->
                            <td class="py-4 px-4 align-top">
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
                            <td class="py-4 px-4 align-top">
                                <div class="text-sm font-semibold text-slate-800 leading-snug">
                                    {{ $rec['primary_author'] }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5 leading-relaxed" title="{{ $rec['authors'] }}">
                                    {{ $rec['authors'] }}
                                </div>
                            </td>

                            <!-- ISBN -->
                            <td class="py-4 px-4 align-top whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-800 leading-snug">
                                    {{ $rec['isbn'] }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $rec['control_number'] }}
                                </div>
                            </td>

                            <!-- Call Number -->
                            <td class="py-4 px-4 align-top whitespace-nowrap">
                                @if($rec['call_number'] !== 'UNASSIGNED')
                                    <div class="text-sm font-semibold text-slate-800 leading-snug">
                                        {{ $rec['call_number'] }}
                                    </div>
                                    @if(!empty($rec['dewey']))
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            DDC {{ $rec['dewey'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm font-medium text-rose-600">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            <!-- Publisher & Year -->
                            <td class="py-4 px-4 align-top">
                                <div class="text-sm font-semibold text-slate-800 leading-snug">
                                    {{ $rec['publisher'] }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $rec['place'] }} &bull; {{ $rec['year'] }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 pr-5 pl-2 align-top text-right whitespace-nowrap space-x-1.5 pt-4">
                                <!-- View Raw Tags Button (Icon Only, No Text) -->
                                <button
                                    type="button"
                                    wire:click="openMarcModal('{{ $rec['id'] }}')"
                                    class="p-2 rounded-lg text-[#102B70] hover:text-[#0B225E] hover:bg-blue-50 border border-slate-200 bg-white transition inline-flex items-center justify-center shadow-2xs"
                                    title="View Raw Tags"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </button>

                                <!-- Remove from list -->
                                <button
                                    type="button"
                                    wire:click="removeRecord('{{ $rec['id'] }}')"
                                    class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition inline-flex items-center justify-center"
                                    title="Exclude from import"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-bold text-base text-slate-700">No books found matching your filters</p>
                                    <p class="text-sm text-slate-500">Try adjusting your search keywords or tab view.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TABLE FOOTER -->
        <div class="p-4 sm:p-5 bg-slate-50 border-t border-[#E2E8F0] flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-600 font-medium">
            <span>Showing {{ count($filteredRecords) }} of {{ count($records) }} extracted bibliographic entries</span>
            <span class="font-bold text-[#102B70]">
                {{ count($selectedRecords) }} Selected for Cataloging
            </span>
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
                wire:click="simulateImport"
                @if(count($selectedRecords) === 0) disabled @endif
                class="h-12 px-7 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] disabled:opacity-50 disabled:cursor-not-allowed text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import Selected Records ({{ count($selectedRecords) }})</span>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MARC21 RAW TAG INSPECTION MODAL                -->
    <!-- ============================================== -->
    @if($showMarcModal && $activeRecordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity animate-fade-in"
                wire:click="closeMarcModal"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-3xl border border-[#E2E8F0]">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#102B70] px-6 py-4 flex items-center justify-between text-white">
                        <div class="min-w-0 pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold tracking-wider uppercase text-blue-200">
                                    MARC21 Record
                                </span>
                                <span class="text-xs text-white/70">
                                    {{ $activeRecordModal['control_number'] }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white truncate mt-1" id="modal-title">
                                {{ $activeRecordModal['title'] }}
                            </h3>
                        </div>
                        <button
                            type="button"
                            wire:click="closeMarcModal"
                            class="rounded-xl p-1.5 text-white/70 hover:text-white hover:bg-white/10 transition shrink-0"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body / MARC Fields Table -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                        <div class="flex items-center justify-between text-sm text-slate-500 pb-2 border-b border-slate-100">
                            <span>Field Tag Structure & Subfield Mapping</span>
                            <span class="text-slate-700 font-bold">LDR: {{ $activeRecordModal['leader'] }}</span>
                        </div>

                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold uppercase text-xs">
                                        <th class="py-3 px-3 w-16">Tag</th>
                                        <th class="py-3 px-2 w-12 text-center">Ind</th>
                                        <th class="py-3 px-3">Field Data & Subfields</th>
                                        <th class="py-3 px-3 text-right text-slate-500">Field Name</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($activeRecordModal['marc_fields'] as $f)
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 px-3 font-bold text-[#102B70]">
                                                {{ $f['tag'] }}
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-400 font-bold">
                                                {{ $f['ind1'] }}{{ $f['ind2'] }}
                                            </td>
                                            <td class="py-3 px-3 text-slate-900 break-words text-xs sm:text-sm">
                                                {{ $f['data'] }}
                                            </td>
                                            <td class="py-3 px-3 text-right text-xs text-slate-500 whitespace-nowrap">
                                                {{ $f['desc'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-[#E2E8F0] flex items-center justify-between">
                        <span class="text-sm text-slate-600 font-medium">
                            Status: <strong class="text-slate-900 uppercase font-bold">{{ $activeRecordModal['status'] }}</strong>
                        </span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="closeMarcModal"
                                class="h-10 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-100 text-sm font-bold text-slate-700 transition"
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
