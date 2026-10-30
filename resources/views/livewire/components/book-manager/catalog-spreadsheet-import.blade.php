<div
    @if($isImporting) wire:poll.2s @endif
    x-data="{
        inspectModalOpen: @json(!empty($inspectRowData)),
        reviewRequestError: '',
        removeImportHook: null,
        init() {
            this.removeImportHook = Livewire.hook('commit', ({ component, commit, fail, succeed }) => {
                if (component.id !== this.$wire.$id || !commit.calls.some(call => call.method === 'review')) return;
                this.reviewRequestError = '';
                fail(() => { this.reviewRequestError = 'The server could not review the uploaded file. It may be too large or the connection was interrupted. Try a smaller file or contact an administrator.'; });
                succeed(() => { this.reviewRequestError = ''; });
            });
        },
        destroy() { if (this.removeImportHook) this.removeImportHook(); },
    }"
    class="flex flex-col gap-4 font-sans text-slate-800"
>

    <!-- ============================================== -->
    <!-- 1. PAGE HEADER & QUICK ACTIONS                 -->
    <!-- ============================================== -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-[-0.02em] text-[#102B70] sm:text-2xl">Import Catalog Records</h1>
            <p class="mt-0.5 text-xs sm:text-sm font-medium text-slate-600">
                Batch-import books and bibliographic records from spreadsheets (.xlsx, .csv) or MARC exchange files.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                wire:click="downloadTemplate"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#E2E8F0] bg-white px-4 text-xs sm:text-sm font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50 hover:text-[#102B70] active:translate-y-px cursor-pointer"
                title="Download formatted sample CSV file"
            >
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                <span>Sample CSV Template</span>
            </button>

            @if ($catalogImport || $marcImportKey || $hasSelectedFile)
                <button
                    type="button"
                    wire:click="resetImport"
                    @disabled($isImporting)
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-[#E2E8F0] bg-white px-4 text-xs sm:text-sm font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50 hover:text-rose-600 active:translate-y-px disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span>Start Over</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert / Message Banner -->
    <div x-show="reviewRequestError" x-cloak role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-900" x-text="reviewRequestError"></div>
    @if ($message)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="rounded-lg border p-4 text-xs sm:text-sm font-medium flex items-start justify-between gap-3 {{ $messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($messageType === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-900' : ($messageType === 'error' ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-blue-200 bg-blue-50 text-blue-900')) }}"
        >
            <div>{{ $message }}</div>
            <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- 2. ROUTE TO MARC REVIEW COMPONENT              -->
    <!-- ============================================== -->
    @if ($marcImportKey)
        <div>
            <livewire:components.book-manager.marc-upload
                :import-key="$marcImportKey"
                :file-summary="$marcFileSummary"
                :default-collection="$defaultCollection"
                :accession-prefix="$accessionPrefix"
                :start-accession-number="$startAccessionNumber"
                :key="'marc-upload-'.$marcImportKey"
            />
        </div>

    <!-- ============================================== -->
    <!-- 3. SPREADSHEET PREVIEW & REVIEW STATE          -->
    <!-- ============================================== -->
    @elseif ($catalogImport)
        <div class="space-y-4">

            <!-- Post-Import Success Banner -->
            @if ($importCompleted)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-emerald-950">Catalog import finished</h3>
                            <p class="text-xs sm:text-sm font-medium text-emerald-800 mt-0.5">
                                <strong class="font-bold">{{ $importedCount }}</strong> record(s) cataloged into <strong class="font-bold">{{ $catalogImport->summary['options']['default_collection'] ?? $defaultCollection }}</strong>.
                                @if ($failedCount > 0)
                                    <span class="text-amber-800 font-semibold">({{ $failedCount }} record(s) failed or were skipped; see Issues for details).</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                                class="inline-flex h-9 items-center justify-center rounded-lg bg-[#102B70] px-4 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#0B225E] transition">
                                View Books in Catalog
                            </a>
                            <button type="button" wire:click="resetImport"
                                class="inline-flex h-9 items-center justify-center rounded-lg border border-emerald-300 bg-white px-4 text-xs sm:text-sm font-semibold text-emerald-800 hover:bg-emerald-50 transition cursor-pointer">
                                Import Another File
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Background Import Progress Card -->
            @if ($isImporting)
                <div class="rounded-xl border border-[#102B70] bg-[#102B70] text-white p-5 space-y-3">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-white">Importing Records to Catalog...</h3>
                            <p class="text-xs text-blue-100 mt-0.5">
                                Processed <strong class="text-white">{{ $importedRows + $issueRows }}</strong> of <strong class="text-white">{{ $totalRows }}</strong> rows: {{ $importedRows }} imported, {{ $issueRows }} with issues.
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-2xl font-bold text-[#FCC719] font-mono">{{ $progressPercent }}%</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-white/20 rounded-full h-2.5 overflow-hidden" role="progressbar" aria-label="Import progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressPercent }}">
                        <div class="bg-[#FCC719] h-2.5 rounded-full transition-all duration-300 ease-out"
                             style="width: {{ $progressPercent }}%"></div>
                    </div>

                    <p class="text-xs text-blue-200">
                        If progress stays unchanged, check that the queue worker is running. You can return to this page to resume monitoring.
                    </p>
                </div>
            @endif

            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Total Rows -->
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Rows Found</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-[#102B70]">{{ $totalRows }}</span>
                        <span class="text-xs text-slate-500 font-medium">record(s)</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 truncate" title="{{ $catalogImport->source_file_name }}">
                        Source: {{ $catalogImport->source_file_name }}
                    </p>
                </div>

                <!-- Valid Rows -->
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Ready to Import</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-emerald-700">{{ $validRows }}</span>
                        <span class="text-xs text-slate-500 font-medium">valid rows</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        All required columns present &amp; verified.
                    </p>
                </div>

                <!-- Issue Rows -->
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wider {{ $issueRows > 0 ? 'text-rose-700' : 'text-slate-500' }}">Issues / Conflicts</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-bold {{ $issueRows > 0 ? 'text-rose-700' : 'text-slate-700' }}">{{ $issueRows }}</span>
                        <span class="text-xs text-slate-500 font-medium">issue row(s)</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $issueRows > 0 ? 'Duplicate accessions or missing required fields.' : 'No validation errors detected.' }}
                    </p>
                </div>
            </div>

            <!-- Preview Table Card -->
            <div class="rounded-xl border border-[#E2E8F0] bg-white overflow-hidden shadow-xs">
                <!-- Toolbar: Filter Tabs + Search + Per Page -->
                <div class="p-3 sm:p-4 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto">
                        <button
                            type="button"
                            wire:click="setTab('all')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'all' ? 'bg-[#102B70] text-white' : 'border border-[#E2E8F0] bg-white text-slate-700 hover:bg-slate-50' }}"
                        >
                            All Records ({{ $totalRows }})
                        </button>
                        @if ($validRows > 0 || !$importCompleted)
                            <button
                                type="button"
                                wire:click="setTab('ready')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'ready' ? 'bg-[#102B70] text-white' : 'border border-[#E2E8F0] bg-white text-slate-700 hover:bg-slate-50' }}"
                            >
                                Ready to Import ({{ $validRows }})
                            </button>
                        @endif
                        @if ($importedRows > 0)
                            <button
                                type="button"
                                wire:click="setTab('imported')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'imported' ? 'bg-emerald-700 text-white' : 'border border-[#E2E8F0] bg-white text-slate-700 hover:bg-slate-50' }}"
                            >
                                Cataloged ({{ $importedRows }})
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="setTab('issues')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'issues' ? 'bg-rose-700 text-white' : 'border border-[#E2E8F0] bg-white text-slate-700 hover:bg-slate-50' }}"
                        >
                            Issues ({{ $issueRows }})
                        </button>
                    </div>

                    <!-- Search and Per Page -->
                    <div class="flex items-center gap-2">
                        <div class="relative w-full sm:w-64">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search title, author, accession..."
                                class="h-9 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            />
                            @if (!empty($search))
                                <button
                                    type="button"
                                    wire:click="$set('search', '')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                >
                                    ✕
                                </button>
                            @endif
                        </div>

                        <select
                            wire:model.live="perPage"
                            class="h-9 rounded-lg border border-[#CBD5E1] bg-white px-2.5 text-xs font-medium text-slate-700 outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                        >
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="min-w-[960px] w-full text-left text-xs sm:text-sm">
                        <thead class="bg-[#F8FAFC] text-[11px] font-semibold uppercase tracking-wider text-slate-600 border-b border-[#E2E8F0]">
                            <tr>
                                <th class="py-2.5 px-3.5 w-12 text-slate-500">#</th>
                                <th class="py-2.5 px-3.5 w-24">Status</th>
                                <th class="py-2.5 px-3.5 min-w-[220px]">Title &amp; Author</th>
                                <th class="py-2.5 px-3.5 min-w-[130px]">Accession No</th>
                                <th class="py-2.5 px-3.5 min-w-[120px]">Format &amp; Shelf</th>
                                <th class="py-2.5 px-3.5 min-w-[130px]">ISBN / Call No</th>
                                <th class="py-2.5 px-3.5 min-w-[140px]">Publisher &amp; Year</th>
                                <th class="py-2.5 px-3.5 min-w-[160px]">Validation Notes</th>
                                <th class="py-2.5 px-3.5 w-16 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white font-normal">
                            @forelse ($paginatedRows as $row)
                                @php
                                    $data = is_array($row->raw_data) ? $row->raw_data : (json_decode($row->raw_data ?? '{}', true) ?: []);
                                    $errors = is_array($row->errors) ? $row->errors : (json_decode($row->errors ?? '[]', true) ?: []);
                                    $isReady = $row->status === 'pending';
                                    $isImported = $row->status === 'imported';
                                    $hasIssues = in_array($row->status, ['invalid', 'failed', 'skipped']);
                                @endphp
                                <tr class="hover:bg-[#F8FAFC] transition-colors {{ $hasIssues ? 'bg-rose-50/20' : '' }}">
                                    <!-- Row # -->
                                    <td class="py-2.5 px-3.5 font-mono text-xs text-slate-500">
                                        {{ $row->row_number }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2.5 px-3.5 text-xs font-semibold whitespace-nowrap">
                                        @if ($isImported)
                                            <span class="text-emerald-700">Imported</span>
                                        @elseif ($isReady)
                                            <span class="text-emerald-600">Ready</span>
                                        @else
                                            <span class="text-rose-600">Issue</span>
                                        @endif
                                    </td>

                                    <!-- Title & Author -->
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-semibold text-[#0F172A] line-clamp-1" title="{{ $data['title'] ?? '' }}">
                                            {{ $data['title'] ?? 'Untitled Book' }}
                                        </div>
                                        <div class="text-xs text-slate-500 line-clamp-1 mt-0.5" title="{{ $data['author'] ?? '' }}">
                                            {{ !empty($data['author']) ? $data['author'] : 'Unknown Author' }}
                                        </div>
                                    </td>

                                    <!-- Accession No -->
                                    <td class="py-2.5 px-3.5 font-mono text-xs">
                                        @if (!empty($data['accession_no']))
                                            <span class="text-slate-800 font-semibold">{{ $data['accession_no'] }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Auto-assign</span>
                                        @endif
                                    </td>

                                    <!-- Format & Shelf -->
                                    <td class="py-2.5 px-3.5 text-xs text-slate-700">
                                        <div class="font-medium text-slate-800">{{ $data['material_type'] ?? 'Book' }}</div>
                                        <div class="text-[11px] text-slate-500 truncate" title="{{ $data['sublocation'] ?? '' }}">
                                            {{ ($data['sublocation'] ?? '') ?: $defaultCollection }}
                                        </div>
                                    </td>

                                    <!-- ISBN & Call Number -->
                                    <td class="py-2.5 px-3.5 text-xs font-mono text-slate-600">
                                        <div>{{ ($data['call_number'] ?? '') ?: '—' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ ($data['isbn'] ?? '') ?: 'No ISBN' }}</div>
                                    </td>

                                    <!-- Publisher & Year -->
                                    <td class="py-2.5 px-3.5 text-xs text-slate-600">
                                        <div class="truncate max-w-[140px]" title="{{ $data['publisher'] ?? '' }}">{{ ($data['publisher'] ?? '') ?: '—' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ ($data['publication_year'] ?? '') ?: '—' }}</div>
                                    </td>

                                    <!-- Validation Notes -->
                                    <td class="py-2.5 px-3.5 text-xs">
                                        @if (!empty($errors))
                                            <ul class="space-y-0.5 text-rose-700 font-medium">
                                                @foreach ($errors as $err)
                                                    <li>&bull; {{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        @elseif ($isImported)
                                            <span class="text-emerald-700 font-medium">Cataloged successfully</span>
                                        @else
                                            <span class="text-emerald-700 font-medium">Ready to catalog</span>
                                        @endif
                                    </td>

                                    <!-- Inspect Action -->
                                    <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                                        <button
                                            type="button"
                                            @click="inspectModalOpen = true"
                                            wire:click="inspectRow({{ $row->id }})"
                                            class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] hover:underline cursor-pointer"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-500">
                                        <div class="font-semibold text-slate-700">No records found matching filters</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Try adjusting your search query or switching tabs.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                @if ($paginatedRows)
                    <div class="p-3 sm:p-4 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600 font-medium">
                        <div>
                            @if ($paginatedRows->count() > 0)
                                Showing <strong class="font-bold text-slate-800">{{ $paginatedRows->count() }}</strong> record(s) on this page
                            @else
                                No records to display
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $paginatedRows->links('components.widget.cursor-pagination') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Bottom Action Bar -->
            <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    @if ($isImporting)
                        <h3 class="text-sm font-bold text-[#102B70]">Import running in background...</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Processing copy records. You may freely refresh or leave this page.
                        </p>
                    @elseif ($importCompleted)
                        <h3 class="text-sm font-bold text-emerald-800">All available records cataloged!</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Successfully imported {{ $importedCount }} book copy record(s).
                        </p>
                    @else
                        <h3 class="text-sm font-bold text-[#102B70]">Ready to commit records into catalog?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if ($validRows > 0)
                                {{ $validRows }} valid record(s) will be added to the library collection in the background.
                            @else
                                No valid records available to import. Resolve errors or upload a revised file.
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        wire:click="resetImport"
                        @disabled($isImporting)
                        class="h-10 px-4 rounded-lg border border-[#E2E8F0] bg-white hover:bg-slate-50 text-xs sm:text-sm font-semibold text-slate-700 shadow-xs transition cursor-pointer"
                    >
                        Start Over
                    </button>

                    @if ($isImporting)
                        <button type="button" wire:click="cancelImport" wire:loading.attr="disabled" wire:target="cancelImport" wire:confirm="Cancel this import? Records already imported will be kept."
                            class="h-10 px-4 rounded-lg border border-rose-300 bg-white text-rose-700 text-xs sm:text-sm font-semibold hover:bg-rose-50 disabled:opacity-50">
                            Cancel import
                        </button>
                        <button
                            type="button"
                            disabled
                            class="h-10 px-5 rounded-lg bg-[#102B70] opacity-80 cursor-wait text-xs sm:text-sm font-semibold text-white shadow-xs inline-flex items-center justify-center gap-2"
                        >
                            <span>Importing ({{ $progressPercent }}%)...</span>
                        </button>
                    @elseif ($catalogImport->status === 'cancelled')
                        <span class="text-sm font-semibold text-slate-600">Import cancelled</span>
                    @elseif ($importCompleted)
                        <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                            class="h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-xs sm:text-sm font-semibold text-white shadow-xs transition inline-flex items-center justify-center">
                            View Books in Catalog
                        </a>
                    @else
                        <button
                            type="button"
                            wire:click="importSpreadsheet"
                            wire:loading.attr="disabled"
                            wire:target="importSpreadsheet"
                            @disabled($validRows === 0)
                            class="h-10 px-5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-semibold text-white shadow-xs transition cursor-pointer inline-flex items-center justify-center"
                        >
                            <span wire:loading.remove wire:target="importSpreadsheet">Import Valid Rows ({{ $validRows }})</span>
                            <span wire:loading wire:target="importSpreadsheet">Starting Background Job...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

    <!-- ============================================== -->
    <!-- 4. INITIAL UPLOAD & CONFIGURATION STATE        -->
    <!-- ============================================== -->
    @else
        <div class="space-y-4">

            <!-- Validation Error Alert -->
            @if ($fileValidationError)
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-xs sm:text-sm font-medium text-rose-900 flex items-start justify-between gap-3">
                    <div>{{ $fileValidationError }}</div>
                    <button type="button" wire:click="$set('fileValidationError', null)" class="text-rose-400 hover:text-rose-600 p-0.5 cursor-pointer">
                        ✕
                    </button>
                </div>
            @endif

            <!-- Main Upload & Cataloging Setup Card -->
            <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 sm:p-6 space-y-6 shadow-xs">

                <!-- Interactive Drag-and-Drop Zone -->
                <div
                    x-data="{
                        isDragging: false,
                        uploading: false,
                        progress: 0,
                        uploadError: '',
                        uploadTimer: null,
                        checkFile(file) {
                            if (file.size > {{ $uploadLimitBytes }}) {
                                this.uploadError = 'This file exceeds the server upload limit of {{ $uploadLimitLabel }}. Choose a smaller file or ask an administrator to raise the server limit.';
                                return false;
                            }
                            return true;
                        },
                        beginUpload() {
                            this.uploading = true; this.progress = 0; this.uploadError = '';
                            this.armUploadTimer();
                        },
                        armUploadTimer() {
                            clearTimeout(this.uploadTimer);
                            this.uploadTimer = setTimeout(() => {
                                if (this.uploading) {
                                    this.uploading = false;
                                    this.uploadError = 'The server did not finish the upload. Refresh this page and try again; if the problem continues, check the server logs and upload limits.';
                                }
                            }, 120000);
                        },
                        updateProgress(value) {
                            this.progress = value;
                            this.armUploadTimer();
                        },
                        finishUpload() {
                            clearTimeout(this.uploadTimer);
                            this.uploading = false; this.progress = 100;
                            if (this.$wire.hasSelectedFile) this.$wire.review();
                        },
                    }"
                    x-on:livewire-upload-start="beginUpload()"
                    x-on:livewire-upload-finish="finishUpload()"
                    x-on:livewire-upload-error="clearTimeout(uploadTimer); uploading = false; uploadError = 'Upload failed. Check your connection and server upload limits, then try again.'"
                    x-on:livewire-upload-cancel="clearTimeout(uploadTimer); uploading = false; uploadError = 'Upload cancelled. Choose a file to try again.'"
                    x-on:livewire-upload-progress="updateProgress($event.detail.progress)"
                    class="relative"
                >
                    <!-- Hidden File Input -->
                    <input
                        id="catalogSpreadsheetFileInput"
                        x-ref="fileInput"
                        type="file"
                        wire:model="spreadsheet"
                        @change.capture="if ($event.target.files.length && !checkFile($event.target.files[0])) { $event.stopImmediatePropagation(); $event.target.value = ''; }"
                        accept=".xlsx,.xls,.csv,.txt,.mrc,.marc,.dat,.xml"
                        class="sr-only"
                    />

                    <!-- Drag & Drop Container -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="
                            isDragging = false;
                            if ($event.dataTransfer.files.length && checkFile($event.dataTransfer.files[0])) {
                                beginUpload();
                                @this.upload('spreadsheet', $event.dataTransfer.files[0],
                                    () => finishUpload(),
                                    () => { clearTimeout(uploadTimer); uploading = false; uploadError = 'Upload failed. Check your connection and server upload limits, then try again.'; },
                                    (e) => updateProgress(e.detail.progress)
                                );
                            }
                        "
                        @click="$refs.fileInput.click()"
                        :class="{
                            'border-[#102B70] bg-[#EFF6FF] ring-2 ring-[#BFDBFE]': isDragging,
                            'border-slate-300 bg-[#F8FAFC] hover:border-[#102B70] hover:bg-slate-50': !isDragging
                        }"
                        class="cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                    >
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-white border border-[#E2E8F0] text-[#102B70] shadow-xs">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                        </div>

                        <div class="mt-3">
                            <span class="text-sm font-semibold text-[#0F172A]">
                                Drop your catalog file here, or
                            </span>
                            <span class="text-sm font-semibold text-[#102B70] underline">
                                browse computer
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            Accepts Excel (.xlsx, .xls), CSV (.csv, .txt), or MARC (.mrc, .xml) &bull; Server upload limit {{ $uploadLimitLabel }}. Set cataloging preferences before choosing a file.
                        </p>

                        <!-- Uploading Progress Bar -->
                        <div x-show="uploading" class="mt-4 max-w-sm mx-auto" x-cloak>
                            <div class="flex items-center justify-between text-xs font-semibold text-[#102B70] mb-1">
                                <span x-text="progress >= 100 ? 'Finishing upload on the server…' : 'Uploading file…'"></span>
                                <span x-text="progress + '%'"></span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full bg-[#102B70] transition-all duration-200 rounded-full" :style="'width: ' + progress + '%'"></div>
                            </div>
                            <button type="button" @click.stop="$wire.$cancelUpload('spreadsheet'); clearTimeout(uploadTimer); uploading = false; uploadError = 'Upload cancelled. Choose a file to try again.'" class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel upload</button>
                        </div>
                    </div>
                    <p x-show="uploadError" x-text="uploadError" role="alert" x-cloak class="mt-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800"></p>
                    <div wire:loading wire:target="review" role="status" class="mt-3 text-sm font-semibold text-[#102B70]">Upload complete. Reading and validating records…</div>
                    @error('spreadsheet') <p role="alert" class="mt-3 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>

                <!-- Selected File Card (if file chosen) -->
                @if ($hasSelectedFile && !empty($selectedFileName))
                    <div class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 truncate" title="{{ $selectedFileName }}">
                                {{ $selectedFileName }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $selectedFileSize }} &bull; {{ $detectedFileType === 'marc' ? 'MARC Record' : 'Spreadsheet' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                wire:click="review"
                                wire:loading.attr="disabled"
                                wire:target="review"
                                class="inline-flex h-9 items-center justify-center rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white px-4 text-xs font-semibold shadow-xs transition cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="review">Review Records</span>
                                <span wire:loading wire:target="review">Validating Records...</span>
                            </button>

                            <button
                                type="button"
                                wire:click="clearFile"
                                class="inline-flex h-9 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                            >
                                Change File
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Cataloging Preferences Card -->
                <div class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-4 sm:p-5">
                    <div class="pb-3 border-b border-[#E2E8F0] mb-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Cataloging Preferences</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Default values applied when fields are not specified in the file.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Default Material Type -->
                        <div>
                            <label for="defaultMaterialSelect" class="block text-xs font-semibold text-slate-700 mb-1">
                                Default Material Type
                            </label>
                            <select
                                id="defaultMaterialSelect"
                                wire:model="defaultMaterialType"
                                class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs font-medium text-slate-800 shadow-xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                            >
                                <option value="Book">Book</option>
                                <option value="Thesis">Thesis / Dissertation</option>
                                <option value="Periodical">Periodical / Journal</option>
                                <option value="Audio/Video">Audio / Video Media</option>
                                <option value="Electronic Resource">Electronic Resource</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">If material column is omitted.</p>
                        </div>

                        <!-- Default Shelf Location -->
                        <div>
                            <label for="defaultCollectionSelect" class="block text-xs font-semibold text-slate-700 mb-1">
                                Shelf / Section Location
                            </label>
                            <select
                                id="defaultCollectionSelect"
                                wire:model="defaultCollection"
                                class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs font-medium text-slate-800 shadow-xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                            >
                                <option value="General Circulation">General Circulation</option>
                                <option value="Filipiniana Section">Filipiniana Section</option>
                                <option value="Reference Section">Reference Section</option>
                                <option value="Periodicals Section">Periodicals Section</option>
                                <option value="Reserve Collection">Reserve Collection</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Default physical location.</p>
                        </div>

                        <!-- Accession Prefix -->
                        <div>
                            <label for="accessionPrefixInput" class="block text-xs font-semibold text-slate-700 mb-1">
                                Accession Prefix
                            </label>
                            <input
                                id="accessionPrefixInput"
                                type="text"
                                wire:model="accessionPrefix"
                                placeholder="PGPC-2026-"
                                class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs font-mono font-medium text-slate-800 shadow-xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            />
                            <p class="text-[11px] text-slate-500 mt-1">Prefix for auto-numbered copies.</p>
                        </div>

                        <!-- Start Accession Number -->
                        <div>
                            <label for="startAccessionInput" class="block text-xs font-semibold text-slate-700 mb-1">
                                Starting Number
                            </label>
                            <input
                                id="startAccessionInput"
                                type="number"
                                min="1"
                                wire:model="startAccessionNumber"
                                class="h-10 w-full rounded-lg border border-[#CBD5E1] bg-white px-3 text-xs font-mono font-medium text-slate-800 shadow-xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            />
                            <p class="text-[11px] text-slate-500 mt-1">Next sequential accession index.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-slate-500 font-medium">
                        Required spreadsheet column: <strong class="text-slate-700">title</strong>. Optional: author, accession_no, material_type, isbn, call_number, publisher, publication_year.
                    </div>

                    @if ($hasSelectedFile)
                        <button
                            type="button"
                            wire:click="review"
                            wire:loading.attr="disabled"
                            wire:target="review"
                            class="h-10 w-full sm:w-auto px-6 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-xs sm:text-sm font-semibold text-white shadow-xs transition cursor-pointer inline-flex items-center justify-center min-w-[200px]"
                        >
                            <span wire:loading.remove wire:target="review">Review &amp; Validate Records</span>
                            <span wire:loading wire:target="review">Reading &amp; Validating Records...</span>
                        </button>
                    @else
                        <button
                            type="button"
                            disabled
                            class="h-10 w-full sm:w-auto px-6 rounded-lg bg-slate-100 border border-[#E2E8F0] text-slate-400 text-xs sm:text-sm font-semibold cursor-not-allowed inline-flex items-center justify-center min-w-[200px]"
                        >
                            <span>Select a File to Review</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- 5. ROW INSPECTION MODAL                        -->
    <!-- ============================================== -->
    <div
        x-show="inspectModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="inspect-modal-title"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="inspectModalOpen = false; $wire.closeInspectModal()"
    >
        <!-- Backdrop -->
        <div
            x-show="inspectModalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity cursor-pointer"
            @click="inspectModalOpen = false; $wire.closeInspectModal()"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div
                x-show="inspectModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 w-full max-w-2xl border border-[#E2E8F0]"
            >
                <!-- Modal Header -->
                <div class="bg-[#102B70] px-5 py-3.5 flex items-center justify-between text-white">
                    <div class="min-w-0 pr-4 flex-1">
                        <!-- Skeleton Header (while server loads) -->
                        <div wire:loading.block wire:target="inspectRow" class="space-y-1.5 animate-pulse">
                            <div class="h-3 w-28 bg-white/30 rounded"></div>
                            <div class="h-4 w-52 bg-white/40 rounded"></div>
                        </div>

                        <!-- Real Header (when server data ready) -->
                        <div wire:loading.remove wire:target="inspectRow">
                            @if ($inspectRowData)
                                <span class="text-xs font-semibold text-blue-200">
                                    Row #{{ $inspectRowData['row_number'] }} &bull; {{ ucfirst($inspectRowData['status']) }}
                                </span>
                                <h3 class="text-sm font-bold text-white truncate mt-0.5" id="inspect-modal-title">
                                    {{ $inspectRowData['raw_data']['title'] ?? 'Untitled' }}
                                </h3>
                            @else
                                <div class="space-y-1.5 animate-pulse">
                                    <div class="h-3 w-28 bg-white/30 rounded"></div>
                                    <div class="h-4 w-52 bg-white/40 rounded"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="inspectModalOpen = false; $wire.closeInspectModal()"
                        class="rounded-lg p-1 text-white/80 hover:text-white hover:bg-white/10 transition shrink-0 cursor-pointer text-sm"
                        title="Close modal (Esc)"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <!-- Skeleton Loader (shown using JS/Livewire while waiting for server data) -->
                    <div wire:loading.block wire:target="inspectRow" class="space-y-3 animate-pulse">
                        <div class="h-8 bg-slate-100 rounded-lg border border-slate-200/60 w-3/4"></div>
                        <div class="rounded-lg border border-[#E2E8F0] overflow-hidden">
                            <table class="w-full text-left text-sm border-collapse">
                                <tbody class="divide-y divide-[#E2E8F0] bg-white">
                                    @for ($i = 0; $i < 7; $i++)
                                        <tr>
                                            <td class="py-2.5 px-4 w-40 bg-[#F8FAFC] border-r border-[#E2E8F0]">
                                                <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                                            </td>
                                            <td class="py-2.5 px-4">
                                                <div class="h-3.5 bg-slate-200 rounded {{ $i % 3 === 0 ? 'w-3/4' : ($i % 3 === 1 ? 'w-1/2' : 'w-2/3') }}"></div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Real Data View -->
                    <div wire:loading.remove wire:target="inspectRow">
                        @if ($inspectRowData)
                            <!-- Validation issues notice if any -->
                            @if (!empty($inspectRowData['errors']))
                                <div class="rounded-lg border border-rose-200 bg-rose-50 p-3.5 mb-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-800 mb-1">
                                        Validation Issues
                                    </h4>
                                    <ul class="list-disc list-inside text-sm text-rose-700 space-y-1 font-medium">
                                        @foreach ($inspectRowData['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Parsed Fields Grid -->
                            <div class="rounded-lg border border-[#E2E8F0] overflow-hidden">
                                <table class="w-full text-left text-sm border-collapse">
                                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                                        @if (!empty($inspectRowData['raw_data']) && is_array($inspectRowData['raw_data']))
                                            @foreach ($inspectRowData['raw_data'] as $key => $val)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="py-2.5 px-4 font-semibold text-slate-600 uppercase tracking-wide w-40 bg-[#F8FAFC] font-mono text-xs border-r border-[#E2E8F0]">
                                                        {{ str_replace('_', ' ', $key) }}
                                                    </td>
                                                    <td class="py-2.5 px-4 text-slate-800 font-medium text-sm break-words leading-relaxed">
                                                        {{ is_array($val) ? json_encode($val) : ($val !== '' ? $val : '—') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Initial Fallback Skeleton -->
                            <div class="space-y-3 animate-pulse">
                                <div class="h-8 bg-slate-100 rounded-lg border border-slate-200/60 w-3/4"></div>
                                <div class="rounded-lg border border-[#E2E8F0] overflow-hidden">
                                    <table class="w-full text-left text-sm border-collapse">
                                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                                            @for ($i = 0; $i < 7; $i++)
                                                <tr>
                                                    <td class="py-2.5 px-4 w-40 bg-[#F8FAFC] border-r border-[#E2E8F0]">
                                                        <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                                                    </td>
                                                    <td class="py-2.5 px-4">
                                                        <div class="h-3.5 bg-slate-200 rounded {{ $i % 3 === 0 ? 'w-3/4' : ($i % 3 === 1 ? 'w-1/2' : 'w-2/3') }}"></div>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-[#F8FAFC] px-5 py-3 border-t border-[#E2E8F0] flex items-center justify-end">
                    <button
                        type="button"
                        @click="inspectModalOpen = false; $wire.closeInspectModal()"
                        class="h-10 px-4 rounded-lg border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-semibold text-slate-700 transition cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
