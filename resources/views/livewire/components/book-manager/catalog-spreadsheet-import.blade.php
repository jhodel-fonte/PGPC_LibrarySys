<div @if($isImporting) wire:poll.2s @endif class="space-y-6 font-sans">

    <!-- ============================================== -->
    <!-- 1. HEADER & GLOBAL ACTIONS                     -->
    <!-- ============================================== -->
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Import Catalog Records</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#EFF6FF] px-2.5 py-0.5 text-xs font-bold text-[#102B70] border border-[#BFDBFE]">
                        <svg class="h-3 w-3 text-[#102B70]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Multi-Format
                    </span>
                </div>
                <p class="mt-1 text-sm font-medium text-slate-600">
                    Upload spreadsheets (<strong class="text-slate-800">.xlsx, .csv, .txt</strong>) or MARC exchange files (<strong class="text-slate-800">.mrc, .marc, .dat, .xml</strong>) to preview, validate, and batch-catalog into the library system.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button
                    type="button"
                    wire:click="downloadTemplate"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#CBD5E1] bg-white px-3.5 text-xs sm:text-sm font-semibold text-[#102B70] shadow-2xs transition-colors hover:border-[#102B70] hover:bg-[#EFF6FF]/50 active:translate-y-px cursor-pointer"
                    title="Download a formatted CSV sample file"
                >
                    <svg class="h-4 w-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    <span>Sample CSV Template</span>
                </button>

                @if ($catalogImport || $marcImportKey)
                    <button
                        type="button"
                        wire:click="resetImport"
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-[#CBD5E1] bg-white px-3.5 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs transition-colors hover:border-[#102B70] hover:text-[#102B70] hover:bg-slate-50 active:translate-y-px cursor-pointer"
                    >
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <span>Start Over</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 3-Step Workflow Indicator -->
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-slate-100 pt-4">
            <!-- Step 1 -->
            @php
                $step1Done = (bool) ($catalogImport || $marcImportKey);
                $step2Active = (bool) (($catalogImport && !$importCompleted) || $marcImportKey);
                $step3Done = (bool) $importCompleted;
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-xl {{ $step1Done ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-[#EFF6FF] border border-[#BFDBFE]' }}">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $step1Done ? 'bg-emerald-600 text-white' : 'bg-[#102B70] text-white' }} text-xs font-bold shadow-xs">
                    @if ($step1Done)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        1
                    @endif
                </span>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider {{ $step1Done ? 'text-emerald-800' : 'text-[#102B70]' }}">
                        {{ $step1Done ? 'Completed' : 'Current Step' }}
                    </div>
                    <div class="text-xs sm:text-sm font-bold text-[#0F172A] truncate">1. Upload &amp; Options</div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="flex items-center gap-3 p-3 rounded-xl {{ $step2Active ? 'bg-[#EFF6FF] border border-[#BFDBFE]' : ($step3Done ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-slate-50 border border-slate-200') }}">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $step2Active ? 'bg-[#102B70] text-white' : ($step3Done ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500') }} text-xs font-bold shadow-xs">
                    @if ($step3Done)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        2
                    @endif
                </span>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider {{ $step2Active ? 'text-[#102B70]' : ($step3Done ? 'text-emerald-800' : 'text-slate-500') }}">
                        {{ $step2Active ? 'Current Step' : ($step3Done ? 'Completed' : 'Next Step') }}
                    </div>
                    <div class="text-xs sm:text-sm font-bold text-[#0F172A] truncate">2. Preview &amp; Validate</div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="flex items-center gap-3 p-3 rounded-xl {{ $step3Done ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }}">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $step3Done ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500' }} text-xs font-bold shadow-xs">
                    @if ($step3Done)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        3
                    @endif
                </span>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider {{ $step3Done ? 'text-emerald-800' : 'text-slate-500' }}">
                        {{ $step3Done ? 'Completed' : 'Final Step' }}
                    </div>
                    <div class="text-xs sm:text-sm font-semibold text-[#0F172A] truncate">3. Commit to Catalog</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Message Banner -->
    @if ($message)
        <div class="rounded-xl border p-4 text-sm font-medium flex items-start justify-between gap-3 shadow-2xs {{ $messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($messageType === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-900' : ($messageType === 'error' ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-blue-200 bg-blue-50 text-blue-900')) }}"
            x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-start gap-2.5">
                @if ($messageType === 'success')
                    <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @elseif ($messageType === 'warning')
                    <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                @elseif ($messageType === 'error')
                    <svg class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @else
                    <svg class="h-5 w-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                @endif
                <div>{{ $message }}</div>
            </div>
            <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-0.5">
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
        <div class="space-y-6">

            <!-- Post-Import Success Banner -->
            @if ($importCompleted)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-5 shadow-xs transition-all">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-emerald-950">Catalog Batch Successfully Committed</h3>
                                <p class="text-sm font-medium text-emerald-800 mt-1 leading-relaxed">
                                    <strong class="font-bold">{{ $importedCount }}</strong> physical book copies were cataloged into <strong class="font-bold">{{ $defaultCollection }}</strong>.
                                    @if ($failedCount > 0)
                                        <span class="text-amber-800 font-semibold">({{ $failedCount }} invalid row(s) were excluded).</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto justify-end">
                            <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#102B70] px-5 text-sm font-bold text-white shadow-xs hover:bg-[#0B225E] transition">
                                <span>View Books in Catalog</span>
                            </a>
                            <button type="button" wire:click="resetImport"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-white px-5 text-sm font-bold text-emerald-800 hover:bg-emerald-50 transition">
                                <span>Import Another File</span>
                            </button>
                        </div>
                    </div>
                </div>
            <!-- Background Import Progress Card -->
            @if ($isImporting)
                <div class="rounded-2xl border-2 border-[#102B70] bg-gradient-to-br from-[#102B70] via-[#0B225E] to-[#071943] text-white p-5 sm:p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>
                    <div class="absolute top-0 right-1/4 w-32 h-32 rounded-full bg-[#FCC719]/10 pointer-events-none blur-2xl"></div>

                    <div class="relative z-10 space-y-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-[#FCC719] border border-white/20">
                                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base sm:text-lg font-bold text-white">Importing Records to Database</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FCC719] text-[#102B70]">
                                            Background Job
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-blue-100 mt-0.5">
                                        Cataloged <strong class="text-[#FCC719] font-bold">{{ $importedRows }}</strong> of <strong class="text-white font-bold">{{ $totalRows }}</strong> records into the library system.
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-2xl sm:text-3xl font-extrabold text-[#FCC719] font-mono tracking-tight">{{ $progressPercent }}%</div>
                                <div class="text-[11px] uppercase tracking-wider text-blue-200 font-semibold">Server Progress</div>
                            </div>
                        </div>

                        <!-- Animated Progress Bar -->
                        <div class="w-full bg-white/15 rounded-full h-3 overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-[#FCC719] via-amber-400 to-yellow-300 h-2 rounded-full transition-all duration-500 ease-out shadow-sm"
                                 style="width: {{ $progressPercent }}%"></div>
                        </div>

                        <!-- Safe To Leave / Refresh Notice -->
                        <div class="flex items-center gap-2.5 rounded-xl bg-white/10 px-3.5 py-2.5 text-xs text-blue-100 border border-white/10">
                            <svg class="w-4 h-4 text-[#FCC719] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            <span>
                                <strong>Safe to refresh or exit:</strong> The import process runs independently on the server. Even if you close your browser or navigate to other pages, adding data will continue and a notification will be delivered to your top bar.
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Total Rows -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Rows Found</span>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                            {{ strtoupper($catalogImport->source_type) }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#102B70]">{{ $totalRows }}</span>
                        <span class="text-xs text-slate-500 font-medium">record(s)</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 truncate" title="{{ $catalogImport->source_file_name }}">
                        Source: {{ $catalogImport->source_file_name }}
                    </p>
                </div>

                <!-- Valid Rows -->
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Ready to Import</span>
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700">{{ $validRows }}</span>
                        <span class="text-xs text-emerald-700 font-medium">valid rows</span>
                    </div>
                    <p class="mt-1 text-xs text-emerald-700 font-medium">
                        All required columns present &amp; verified.
                    </p>
                </div>

                <!-- Issue Rows -->
                <div class="rounded-2xl border {{ $issueRows > 0 ? 'border-rose-200 bg-rose-50/50' : 'border-slate-200 bg-white' }} p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider {{ $issueRows > 0 ? 'text-rose-800' : 'text-slate-500' }}">Issues / Conflicts</span>
                        @if ($issueRows > 0)
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">Needs Attention</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Clean</span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold {{ $issueRows > 0 ? 'text-rose-700' : 'text-slate-700' }}">{{ $issueRows }}</span>
                        <span class="text-xs {{ $issueRows > 0 ? 'text-rose-600' : 'text-slate-500' }} font-medium">conflict row(s)</span>
                    </div>
                    <p class="mt-1 text-xs {{ $issueRows > 0 ? 'text-rose-700' : 'text-slate-500' }} font-medium">
                        {{ $issueRows > 0 ? 'Duplicate accessions or missing required fields.' : 'No validation errors detected.' }}
                    </p>
                </div>
            </div>

            <!-- Preview Table Card -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden">
                <!-- Toolbar: Filter Tabs + Search + Per Page -->
                <div class="p-4 sm:p-5 border-b border-[#F1F5F9] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <!-- Filter Tabs -->
                        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 border border-slate-200/60 overflow-x-auto">
                            <button
                                type="button"
                                wire:click="setTab('all')"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'all' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-[#0F172A]' }}"
                            >
                                All Records ({{ $totalRows }})
                            </button>
                            @if ($validRows > 0 || !$importCompleted)
                                <button
                                    type="button"
                                    wire:click="setTab('ready')"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'ready' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700' }}"
                                >
                                    Ready to Import ({{ $validRows }})
                                </button>
                            @endif
                            @if ($importedRows > 0)
                                <button
                                    type="button"
                                    wire:click="setTab('imported')"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'imported' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700' }}"
                                >
                                    Cataloged ({{ $importedRows }})
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="setTab('issues')"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === 'issues' ? 'bg-rose-700 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700' }}"
                            >
                                Issues ({{ $issueRows }})
                            </button>
                        </div>

                        <!-- Search and Per Page -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="relative w-full sm:w-72">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Search title, author, accession..."
                                    class="h-9 w-full rounded-xl border border-[#CBD5E1] bg-white pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 shadow-2xs outline-none transition focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                                />
                                @if (!empty($search))
                                    <button
                                        type="button"
                                        wire:click="$set('search', '')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <label for="tablePerPage" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Show</label>
                                <select
                                    id="tablePerPage"
                                    wire:model.live="perPage"
                                    class="h-9 rounded-xl border border-[#CBD5E1] bg-white px-2.5 text-xs font-semibold text-[#102B70] shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="min-w-[960px] w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3.5 w-14">#</th>
                                <th class="py-3 px-3.5 w-24">Status</th>
                                <th class="py-3 px-3.5 min-w-[220px]">Title &amp; Author</th>
                                <th class="py-3 px-3.5 min-w-[140px]">Accession No</th>
                                <th class="py-3 px-3.5 min-w-[120px]">Format &amp; Shelf</th>
                                <th class="py-3 px-3.5 min-w-[130px]">ISBN / Call No</th>
                                <th class="py-3 px-3.5 min-w-[140px]">Publisher &amp; Year</th>
                                <th class="py-3 px-3.5 min-w-[160px]">Validation Notes</th>
                                <th class="py-3 px-3 w-16 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white font-medium">
                            @forelse ($paginatedRows as $row)
                                @php
                                    $data = is_array($row->raw_data) ? $row->raw_data : (json_decode($row->raw_data ?? '{}', true) ?: []);
                                    $errors = is_array($row->errors) ? $row->errors : (json_decode($row->errors ?? '[]', true) ?: []);
                                    $isReady = $row->status === 'pending';
                                    $isImported = $row->status === 'imported';
                                    $hasIssues = in_array($row->status, ['invalid', 'failed']);
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors {{ $hasIssues ? 'bg-rose-50/20' : '' }}">
                                    <!-- Row # -->
                                    <td class="py-3 px-3.5 font-mono text-xs font-semibold text-slate-500">
                                        {{ $row->row_number }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3 px-3.5">
                                        @if ($isImported)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                Imported
                                            </span>
                                        @elseif ($isReady)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                                Ready
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700 border border-rose-200">
                                                Issue
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Title & Author -->
                                    <td class="py-3 px-3.5">
                                        <div class="font-bold text-[#102B70] line-clamp-1" title="{{ $data['title'] ?? '' }}">
                                            {{ $data['title'] ?? 'Untitled Book' }}
                                        </div>
                                        <div class="text-xs text-slate-500 line-clamp-1 mt-0.5" title="{{ $data['author'] ?? '' }}">
                                            {{ !empty($data['author']) ? $data['author'] : 'Unknown Author' }}
                                        </div>
                                    </td>

                                    <!-- Accession No -->
                                    <td class="py-3 px-3.5 font-mono text-xs">
                                        @if (!empty($data['accession_no']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold {{ $hasIssues ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                                {{ $data['accession_no'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-slate-400 italic text-xs">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                Auto-assign
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Format & Shelf -->
                                    <td class="py-3 px-3.5 text-xs text-slate-700">
                                        <div class="font-semibold text-slate-800">
                                            {{ $data['material_type'] ?? 'Book' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate" title="{{ $data['sublocation'] ?? '' }}">
                                            {{ $data['sublocation'] ?: $defaultCollection }}
                                        </div>
                                    </td>

                                    <!-- ISBN & Call Number -->
                                    <td class="py-3 px-3.5 text-xs font-mono text-slate-600">
                                        <div>{{ $data['call_number'] ?: '—' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $data['isbn'] ?: 'No ISBN' }}</div>
                                    </td>

                                    <!-- Publisher & Year -->
                                    <td class="py-3 px-3.5 text-xs text-slate-600">
                                        <div class="truncate max-w-[140px]" title="{{ $data['publisher'] ?? '' }}">{{ $data['publisher'] ?: '—' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $data['publication_year'] ?: '—' }}</div>
                                    </td>

                                    <!-- Validation Notes -->
                                    <td class="py-3 px-3.5 text-xs">
                                        @if (!empty($errors))
                                            <ul class="space-y-0.5 text-rose-700 font-medium">
                                                @foreach ($errors as $err)
                                                    <li class="flex items-start gap-1">
                                                        <span class="text-rose-500 font-bold">&bull;</span>
                                                        <span>{{ $err }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif ($isImported)
                                            <span class="text-emerald-700 font-semibold">Cataloged successfully</span>
                                        @else
                                            <span class="text-emerald-700 font-semibold">Ready to catalog</span>
                                        @endif
                                    </td>

                                    <!-- Inspect Action -->
                                    <td class="py-3 px-3 text-right">
                                        <button
                                            type="button"
                                            wire:click="inspectRow({{ $row->id }})"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-[#102B70] hover:text-[#102B70] hover:bg-slate-50 transition shadow-2xs"
                                            title="Inspect row details"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-10 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                            </svg>
                                            <div class="font-bold text-slate-700">No records found matching filters</div>
                                            <div class="text-xs text-slate-400">Try adjusting your search query or switching tabs.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                @if ($paginatedRows)
                    <div class="p-4 sm:p-5 bg-slate-50 border-t border-[#E2E8F0] flex flex-col md:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-600 font-medium">
                        <div>
                            @if ($paginatedRows->total() > 0)
                                Showing <strong class="font-bold text-slate-800">{{ $paginatedRows->firstItem() }}</strong> to <strong class="font-bold text-slate-800">{{ $paginatedRows->lastItem() }}</strong> of <strong class="font-bold text-[#102B70]">{{ $paginatedRows->total() }}</strong> records
                            @else
                                No records to display
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            {{ $paginatedRows->links('components.widget.pagination') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Bottom Action Bar -->
            <div class="rounded-2xl border border-[#CBD5E1] bg-white p-5 sm:p-6 shadow-[0_8px_30px_rgba(16,43,112,0.06)] flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    @if ($isImporting)
                        <h3 class="text-base font-bold text-[#102B70]">Import running on server...</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Processing copy records in the background. You can freely refresh or leave this page.
                        </p>
                    @elseif ($importCompleted)
                        <h3 class="text-base font-bold text-emerald-800">All available records cataloged!</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Successfully imported {{ $importedCount }} book copy record(s).
                        </p>
                    @else
                        <h3 class="text-base font-bold text-[#102B70]">Ready to commit records into catalog?</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if ($validRows > 0)
                                {{ $validRows }} valid record(s) will be added to the library collection in the background.
                            @else
                                No valid records available to import. Resolve errors or upload a revised file.
                            @endif
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        wire:click="resetImport"
                        class="h-11 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-xs sm:text-sm font-bold text-slate-700 shadow-2xs transition active:scale-[0.98] cursor-pointer"
                    >
                        Start Over
                    </button>

                    @if ($isImporting)
                        <button
                            type="button"
                            disabled
                            class="h-11 px-6 rounded-xl bg-[#102B70] opacity-90 cursor-wait text-xs sm:text-sm font-bold text-white shadow-sm inline-flex items-center justify-center min-w-[200px] gap-2 border border-[#102B70]"
                        >
                            <svg class="animate-spin h-4 w-4 text-[#FCC719]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Importing in Background ({{ $progressPercent }}%)...</span>
                        </button>
                    @elseif ($importCompleted)
                        <a href="{{ route($routePrefix . 'book-management.index') }}" wire:navigate
                            class="h-11 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs sm:text-sm font-bold text-white shadow-sm transition inline-flex items-center justify-center min-w-[180px] gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>View Books in Catalog</span>
                        </a>
                    @else
                        <button
                            type="button"
                            wire:click="importSpreadsheet"
                            wire:loading.attr="disabled"
                            wire:target="importSpreadsheet"
                            @disabled($validRows === 0)
                            class="h-11 px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer min-w-[180px]"
                        >
                            <span wire:loading.remove wire:target="importSpreadsheet" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span>Import Valid Rows ({{ $validRows }})</span>
                            </span>

                            <span wire:loading.flex wire:target="importSpreadsheet" class="items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Starting Background Job...</span>
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

    <!-- ============================================== -->
    <!-- 4. INITIAL UPLOAD & CONFIGURATION STATE        -->
    <!-- ============================================== -->
    @else
        <div class="space-y-6">

            <!-- Validation Error Alert -->
            @if ($fileValidationError)
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-900 flex items-start justify-between gap-3 shadow-2xs">
                    <div class="flex items-start gap-2.5">
                        <svg class="h-5 w-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <div>{{ $fileValidationError }}</div>
                    </div>
                    <button type="button" wire:click="$set('fileValidationError', null)" class="text-rose-400 hover:text-rose-600 p-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <!-- Main Upload & Cataloging Setup Card -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">

                <!-- Interactive Drag-and-Drop Zone -->
                <div
                    x-data="{
                        isDragging: false,
                        uploading: false,
                        progress: 0,
                    }"
                    x-on:livewire-upload-start="uploading = true; progress = 0"
                    x-on:livewire-upload-finish="uploading = false; progress = 100"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="relative"
                >
                    <!-- Hidden File Input -->
                    <input
                        id="catalogSpreadsheetFileInput"
                        x-ref="fileInput"
                        type="file"
                        wire:model="spreadsheet"
                        accept=".xlsx,.xls,.csv,.txt,.mrc,.marc,.dat,.xml"
                        class="sr-only"
                    />

                    <!-- Drag & Drop Container -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="
                            isDragging = false;
                            if ($event.dataTransfer.files.length) {
                                uploading = true; progress = 0;
                                @this.upload('spreadsheet', $event.dataTransfer.files[0],
                                    () => { uploading = false; progress = 100; },
                                    () => { uploading = false; },
                                    (e) => { progress = e.detail.progress; }
                                );
                            }
                        "
                        @click="$refs.fileInput.click()"
                        :class="{
                            'border-[#102B70] bg-[#EFF6FF]/60 ring-2 ring-[#BFDBFE]': isDragging,
                            'border-slate-300 bg-[#F8FAFC] hover:border-[#102B70] hover:bg-[#EFF6FF]/20': !isDragging
                        }"
                        class="group cursor-pointer rounded-2xl border-2 border-dashed p-8 sm:p-10 text-center transition-all"
                    >
                        <!-- Cloud Upload Icon with PGPC Gold / Navy Accents -->
                        <div class="mx-auto flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-white shadow-xs border border-slate-200 group-hover:scale-105 transition-transform">
                            <svg class="h-7 w-7 sm:h-8 sm:w-8 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                        </div>

                        <div class="mt-4">
                            <span class="text-sm sm:text-base font-bold text-[#102B70]">
                                Drag &amp; drop your catalog file here,
                            </span>
                            <span class="text-sm sm:text-base font-medium text-slate-600">
                                or <span class="font-bold text-[#102B70] underline decoration-[#FCC719] decoration-2 underline-offset-2">browse computer</span>
                            </span>
                        </div>

                        <p class="mt-1.5 text-xs text-slate-500 font-medium">
                            Supported: Excel (<span class="font-mono text-slate-700">.xlsx, .xls</span>), CSV/Text (<span class="font-mono text-slate-700">.csv, .txt</span>), MARC21 (<span class="font-mono text-slate-700">.mrc, .marc, .dat, .xml</span>) &bull; Max 50MB
                        </p>

                        <!-- Format Badges -->
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs">
                                Excel (.xlsx)
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs">
                                CSV (.csv)
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs">
                                MARC21 Binary (.mrc)
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white border border-slate-200 text-slate-700 shadow-2xs">
                                MARCXML (.xml)
                            </span>
                        </div>

                        <!-- Uploading Progress Bar -->
                        <div x-show="uploading" class="mt-5 max-w-sm mx-auto" x-cloak>
                            <div class="flex items-center justify-between text-xs font-bold text-[#102B70] mb-1">
                                <span>Uploading file to server...</span>
                                <span x-text="progress + '%'"></span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full bg-[#102B70] transition-all duration-200 rounded-full" :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected File Card (if file chosen) -->
                @if ($hasSelectedFile && !empty($selectedFileName))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-2xs">
                                @if ($detectedFileType === 'marc')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m9.75 0h-7.5m0 0v7.5" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900 truncate" title="{{ $selectedFileName }}">
                                        {{ $selectedFileName }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-white text-emerald-800 border border-emerald-200">
                                        {{ $detectedFileType === 'marc' ? 'MARC Record' : 'Spreadsheet' }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-medium">
                                    {{ $selectedFileSize }} &bull; Ready to read and validate
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button
                                type="button"
                                wire:click="review"
                                wire:loading.attr="disabled"
                                wire:target="review"
                                class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white px-4 text-xs font-bold shadow-xs transition active:scale-[0.98] cursor-pointer"
                            >
                                <svg wire:loading.remove wire:target="review" class="w-3.5 h-3.5 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <svg wire:loading wire:target="review" class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="review">Review Records Now</span>
                                <span wire:loading wire:target="review">Validating Records...</span>
                            </button>

                            <button
                                type="button"
                                wire:click="clearFile"
                                class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                            >
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Change File</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Cataloging Preferences Card -->
                <div class="rounded-xl border border-[#E2E8F0] bg-slate-50/50 p-4 sm:p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/60 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.6 6.6 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Cataloging Preferences</h4>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">Applied to imported records</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Default Material Type -->
                        <div>
                            <label for="defaultMaterialSelect" class="block text-xs font-bold text-slate-700 mb-1">
                                Default Material Type
                            </label>
                            <select
                                id="defaultMaterialSelect"
                                wire:model="defaultMaterialType"
                                class="h-10 w-full rounded-xl border border-[#CBD5E1] bg-white px-3 text-xs font-semibold text-slate-800 shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            >
                                <option value="Book">Book</option>
                                <option value="Thesis">Thesis / Dissertation</option>
                                <option value="Periodical">Periodical / Journal</option>
                                <option value="Audio/Video">Audio / Video Media</option>
                                <option value="Electronic Resource">Electronic Resource</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Used if material column is omitted.</p>
                        </div>

                        <!-- Default Shelf Location -->
                        <div>
                            <label for="defaultCollectionSelect" class="block text-xs font-bold text-slate-700 mb-1">
                                Shelf / Section Location
                            </label>
                            <select
                                id="defaultCollectionSelect"
                                wire:model="defaultCollection"
                                class="h-10 w-full rounded-xl border border-[#CBD5E1] bg-white px-3 text-xs font-semibold text-slate-800 shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
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
                            <label for="accessionPrefixInput" class="block text-xs font-bold text-slate-700 mb-1">
                                Accession Prefix
                            </label>
                            <input
                                id="accessionPrefixInput"
                                type="text"
                                wire:model="accessionPrefix"
                                placeholder="PGPC-2026-"
                                class="h-10 w-full rounded-xl border border-[#CBD5E1] bg-white px-3 text-xs font-mono font-semibold text-slate-800 shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            />
                            <p class="text-[11px] text-slate-500 mt-1">Prefix for auto-numbered copies.</p>
                        </div>

                        <!-- Start Accession Number -->
                        <div>
                            <label for="startAccessionInput" class="block text-xs font-bold text-slate-700 mb-1">
                                Starting Number
                            </label>
                            <input
                                id="startAccessionInput"
                                type="number"
                                min="1"
                                wire:model="startAccessionNumber"
                                class="h-10 w-full rounded-xl border border-[#CBD5E1] bg-white px-3 text-xs font-mono font-semibold text-slate-800 shadow-2xs outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                            />
                            <p class="text-[11px] text-slate-500 mt-1">Next sequential accession index.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-slate-500 font-medium">
                        Excel/CSV: requires column <span class="font-bold text-slate-700">title</span>. Optional: author, accession_no, material_type, isbn, call_number, publisher, publication_year.
                    </div>

                    @if ($hasSelectedFile)
                        <button
                            type="button"
                            wire:click="review"
                            wire:loading.attr="disabled"
                            wire:target="review"
                            class="h-12 w-full sm:w-auto px-8 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-md hover:shadow-lg transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer min-w-[220px]"
                        >
                            <span wire:loading.remove wire:target="review" class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <span>Review &amp; Validate Records</span>
                            </span>

                            <span wire:loading.inline-flex wire:target="review" class="items-center gap-2" role="status">
                                <svg class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z"></path>
                                </svg>
                                <span>Reading &amp; Validating (5,000+)...</span>
                            </span>
                        </button>
                    @else
                        <button
                            type="button"
                            disabled
                            class="h-12 w-full sm:w-auto px-8 rounded-2xl bg-slate-200 text-slate-400 text-sm font-bold cursor-not-allowed inline-flex items-center justify-center min-w-[220px]"
                        >
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <span>Select a File to Review</span>
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- 5. ROW INSPECTION MODAL                        -->
    <!-- ============================================== -->
    @if ($inspectRowData)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="inspect-modal-title"
            role="dialog"
            aria-modal="true"
            x-data
            @keydown.escape.window="$wire.closeInspectModal()"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity cursor-pointer"
                wire:click="closeInspectModal"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-3xl border border-[#E2E8F0]">
                    <!-- Modal Header -->
                    <div class="bg-[#102B70] px-6 py-4 flex items-center justify-between text-white">
                        <div class="min-w-0 pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold tracking-wider uppercase text-blue-200">
                                    Row #{{ $inspectRowData['row_number'] }} Details
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $inspectRowData['status'] === 'pending' ? 'bg-emerald-500 text-white' : ($inspectRowData['status'] === 'imported' ? 'bg-emerald-600 text-white' : 'bg-rose-500 text-white') }}">
                                    {{ ucfirst($inspectRowData['status']) }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white mt-1 truncate" id="inspect-modal-title">
                                {{ $inspectRowData['raw_data']['title'] ?? 'Untitled' }}
                            </h3>
                        </div>
                        <button
                            type="button"
                            wire:click="closeInspectModal"
                            class="rounded-xl p-2 text-white/70 hover:text-white hover:bg-white/10 transition shrink-0 cursor-pointer"
                            title="Close modal (Esc)"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-5">
                        <!-- Validation issues notice if any -->
                        @if (!empty($inspectRowData['errors']))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-800 mb-1.5 flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                    Validation Issues Detected
                                </h4>
                                <ul class="list-disc list-inside text-xs text-rose-700 space-y-1 font-medium">
                                    @foreach ($inspectRowData['errors'] as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Parsed Fields Grid -->
                        <div class="rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                            <table class="w-full text-left text-xs border-collapse">
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @if (!empty($inspectRowData['raw_data']) && is_array($inspectRowData['raw_data']))
                                        @foreach ($inspectRowData['raw_data'] as $key => $val)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="py-2.5 px-4 font-bold text-slate-600 uppercase tracking-wider w-40 bg-slate-50/60 font-mono text-[11px]">
                                                    {{ str_replace('_', ' ', $key) }}
                                                </td>
                                                <td class="py-2.5 px-4 text-slate-800 font-medium break-words">
                                                    {{ is_array($val) ? json_encode($val) : ($val !== '' ? $val : '—') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-[#E2E8F0] flex items-center justify-end">
                        <button
                            type="button"
                            wire:click="closeInspectModal"
                            class="h-10 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition shadow-2xs cursor-pointer"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
