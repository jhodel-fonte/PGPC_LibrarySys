@props([
    'headers' => [],
    'sort' => ['column' => 'id', 'direction' => 'desc'],
    'sortMethod' => 'sortBy',
    'search' => null,
    'searchModel' => 'search',
    'searchPlaceholder' => 'Search records...',
    'showSearch' => true,
    'tabs' => [],
    'activeTab' => null,
    'tabMethod' => 'setTab',
    'paginator' => null,
    'minWidth' => '1000px',
    'emptyMessage' => 'No records found matching the current search or filters.',
    'emptyColspan' => null,
    'textSize' => 'text-sm',
    'headerTextSize' => 'text-[11px]',
    'selectable' => false,
    'selectAllModel' => 'selectAll',
    'selectedCount' => 0,
    'showFilter' => false,
    'showColumns' => true,
    'activeFilterCount' => 0,
    'perPageModel' => null,
    'perPageOptions' => [10, 25, 50, 100]
])

@php
    $resolvedColspan = ($emptyColspan ?? count($headers)) + ($selectable ? 1 : 0);
    $initialCols = collect($headers)->pluck('index')->filter()->mapWithKeys(fn($k) => [$k => true])->toArray();
@endphp

<div class="relative z-10 flex flex-col overflow-visible rounded-xl border border-[#DCE3EC] bg-white font-sans antialiased shadow-[0_8px_24px_rgba(15,43,112,0.045)] min-h-0 flex-1 {{ $textSize }}"
     x-data="{
         localSearch: @entangle($searchModel).live,
         filterOpen: false,
         columnsOpen: false,
         cols: @js($initialCols),
         isTableLoading: false,
         loadingTimer: null,
         hasError: false,
         errorMessage: '',
         init() {
             if (typeof Livewire !== 'undefined') {
                 Livewire.hook('commit', ({ respond, fail }) => {
                     clearTimeout(this.loadingTimer);
                     this.loadingTimer = setTimeout(() => {
                         this.isTableLoading = true;
                     }, 1200);

                     respond(() => {
                         clearTimeout(this.loadingTimer);
                         this.isTableLoading = false;
                         this.hasError = false;
                     });

                     fail(() => {
                         clearTimeout(this.loadingTimer);
                         this.isTableLoading = false;
                         this.hasError = true;
                         this.errorMessage = 'Unable to load records. Please check your network connection and try again.';
                     });
                 });
             }
         },
         retry() {
             this.hasError = false;
             this.isTableLoading = true;
             if (typeof $wire !== 'undefined') {
                 $wire.$refresh().then(() => {
                     this.isTableLoading = false;
                 }).catch(() => {
                     this.isTableLoading = false;
                     this.hasError = true;
                 });
             }
         }
     }"
     x-on:livewire:error.window="
         clearTimeout(loadingTimer);
         isTableLoading = false;
         hasError = true;
         errorMessage = 'A network error occurred while reaching the server. Please try again.';
     "
     x-on:offline.window="
         hasError = true;
         errorMessage = 'You are currently offline. Please check your internet connection.';
     "
     x-on:online.window="hasError = false"
>
    <!-- 1. Toolbar Row: Search Bar, Filter Button, Columns Button & Actions -->
    @if($showSearch || $showFilter || $showColumns || isset($actions))
        <div class="flex flex-col items-stretch gap-2.5 border-b border-[#E2E8F0] p-3 sm:p-4 md:flex-row md:items-center">
            
            <!-- Search Bar -->
            @if($showSearch)
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <!-- Normal Search Icon -->
                        <svg wire:loading.remove wire:target="{{ $searchModel }}" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <!-- Search Loading Spinner -->
                        <svg wire:loading wire:target="{{ $searchModel }}" class="animate-spin h-3.5 w-3.5 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="{{ $searchModel }}"
                        type="text"
                        placeholder="{{ $searchPlaceholder }}"
                        class="h-[38px] w-full rounded-lg border border-[#CBD5E1] bg-white pl-9 pr-8 text-xs font-medium text-[#0F172A] outline-none transition-colors placeholder:text-slate-500 focus:border-[#102B70] focus:ring-2 focus:ring-[#DBEAFE] md:text-[13px]"
                    >
                    <!-- Clear Search Button -->
                    <button
                        type="button"
                        x-show="localSearch && localSearch.length > 0"
                        @click="localSearch = ''; $wire.set('{{ $searchModel }}', '')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition-colors"
                        aria-label="Clear search"
                        style="display: none;"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <!-- Right Controls: Filter, Columns, Extra Actions -->
            <div class="flex items-center gap-2 shrink-0">

                <!-- Filter Dropdown -->
                @if($showFilter || isset($filterDropdown))
                    <div class="relative">
                        <button
                            type="button"
                            @click="filterOpen = !filterOpen; columnsOpen = false"
                            :aria-expanded="filterOpen"
                            class="flex h-[38px] items-center gap-1.5 rounded-lg border px-3.5 text-xs font-semibold shadow-sm transition-colors active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] md:text-[13px] {{ $activeFilterCount > 0 ? 'border-[#102B70] bg-[#102B70] text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="13.5" height="13.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            @if($activeFilterCount > 0)
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-white text-[#102B70] text-[10px] font-bold">
                                    {{ $activeFilterCount }}
                                </span>
                            @endif
                        </button>

                        @if(isset($filterDropdown))
                            <div
                                x-show="filterOpen"
                                @click.outside="filterOpen = false"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-40 mt-2 w-[min(24rem,calc(100vw-2rem))] rounded-xl border border-[#DCE3EC] bg-white p-4 shadow-xl focus:outline-none"
                            >
                                {{ $filterDropdown }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Columns Dropdown -->
                @if($showColumns && count($headers) > 0)
                    <div class="relative">
                        <button
                            type="button"
                            @click="columnsOpen = !columnsOpen; filterOpen = false"
                            :aria-expanded="columnsOpen"
                            class="flex h-[38px] items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] md:text-[13px]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="13.5" height="13.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M12 3v18"/></svg>
                            <span>Columns</span>
                        </button>

                        <div
                            x-show="columnsOpen"
                            @click.outside="columnsOpen = false"
                            x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-40 mt-2 w-52 rounded-xl border border-[#DCE3EC] bg-white p-3 shadow-xl focus:outline-none"
                        >
                            <div class="space-y-1 mt-1">
                                @foreach($headers as $h)
                                    @php $idx = $h['index'] ?? null; @endphp
                                    @if($idx && $idx !== 'actions')
                                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 select-none">
                                            <input type="checkbox" x-model="cols['{{ $idx }}']" class="rounded text-[#102B70] focus:ring-[#102B70]">
                                            <span>{{ $h['label'] ?? ucfirst($idx) }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Extra Action Buttons Slot -->
                @if(isset($actions))
                    <div class="flex items-center gap-2 shrink-0">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- 2. Row 2: Filter Tabs OR Multiple Selection Bulk Action Bar -->
    @if(!empty($tabs) || isset($toolbarLeft) || $selectedCount > 0 || isset($bulkActions))
        <div class="border-b border-[#E2E8F0] bg-white px-3 py-2.5 sm:px-4">
            @if(isset($bulkActions))
                <!-- Multiple Selection (Bulk Actions) Banner -->
                <div
                    x-show="typeof selected !== 'undefined' ? (Array.isArray(selected) && selected.length > 0) : {{ $selectedCount > 0 ? 'true' : 'false' }}"
                    x-cloak
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-[#EAF2FF] px-3.5 py-2 animate-fade-in"
                >
                    <div class="flex items-center gap-2 text-xs font-bold text-[#102B70]">
                        <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>
                            <span x-text="typeof selected !== 'undefined' ? (Array.isArray(selected) ? selected.length : 0) : {{ $selectedCount }}">{{ $selectedCount }}</span>
                            <span x-text="(typeof selected !== 'undefined' ? (Array.isArray(selected) ? selected.length : 0) : {{ $selectedCount }}) === 1 ? 'item' : 'items'">{{ $selectedCount === 1 ? 'item' : 'items' }}</span> selected
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{ $bulkActions }}
                    </div>
                </div>
            @endif

            <div
                @if(isset($bulkActions))
                    x-show="typeof selected !== 'undefined' ? (!Array.isArray(selected) || selected.length === 0) : {{ $selectedCount === 0 ? 'true' : 'false' }}"
                @endif
            >
                @if(isset($toolbarLeft))
                    {{ $toolbarLeft }}
                @elseif(!empty($tabs))
                <!-- Filter Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    @foreach($tabs as $tabKey => $tabValue)
                        @php
                            $tabName = is_numeric($tabKey) ? $tabValue : $tabKey;
                            $tabLabel = is_numeric($tabKey) ? $tabValue : $tabValue;
                            $isActive = $activeTab === $tabName;

                            // Check if label contains count in parentheses e.g. "Available (20)"
                            $hasCount = preg_match('/^(.*?)\s*\((\d+)\)$/', $tabLabel, $matches);
                            $titleText = $hasCount ? $matches[1] : $tabLabel;
                            $countText = $hasCount ? $matches[2] : null;
                        @endphp
                        <button
                            type="button"
                            wire:click="{{ $tabMethod }}('{{ $tabName }}')"
                            class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-colors active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] md:text-[13px] {{ $isActive ? 'bg-[#102B70] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-[#102B70]' }}"
                        >
                            <span>{{ $titleText }}</span>
                            @if($countText !== null)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[11px] font-bold {{ $isActive ? 'bg-[#071943] text-white' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $countText }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
            </div>
        </div>
    @endif

    <!-- 3. Scrollable Table Container -->
    <div
        :class="hasError ? 'overflow-hidden' : 'overflow-auto'"
        x-effect="if (hasError) { $el.scrollTop = 0; $el.scrollLeft = 0; }"
        class="w-full relative flex-1 min-h-0"
    >
        
        <!-- Long Loading State Overlay (Shown only when request takes > 1.2s) -->
        <div
            x-show="isTableLoading && !hasError"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-white/80 backdrop-blur-[2px] p-6 text-center select-none"
        >
            <div class="relative flex items-center justify-center mb-3">
                <div class="h-10 w-10 animate-spin rounded-full border-3 border-slate-200 border-t-[#102B70]"></div>
                <div class="absolute h-5 w-5 animate-ping rounded-full bg-[#102B70]/10"></div>
            </div>
            <p class="text-sm font-bold text-[#102B70]">Loading records...</p>
            <p class="text-xs text-slate-500 mt-0.5">Fetching latest data from server</p>
        </div>

        <!-- Graceful Error State Overlay (Network / Server Failure) -->
        <div
            x-show="hasError"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-white p-6 text-center select-none"
        >
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600 mb-3 shadow-xs">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900">Unable to load table data</h4>
            <p class="mt-1 max-w-sm text-xs text-slate-500" x-text="errorMessage || 'An unexpected connection error occurred.'"></p>
            
            <button
                type="button"
                x-on:click="retry()"
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-[#102B70] px-4 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-[#0B225E] active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Retry Connection</span>
            </button>
        </div>

        <table
            x-show="!hasError"
            :class="isTableLoading ? 'opacity-40 pointer-events-none transition-opacity duration-300' : 'transition-opacity duration-200'"
            class="table-responsive-cards w-full text-left border-collapse"
            style="--min-w: {{ $minWidth }}; min-width: {{ $minWidth }};"
        >
            <!-- Table Header -->
            <thead class="sticky top-0 z-20 bg-slate-50 shadow-[inset_0_-1px_0_#E2E8F0]">
                <tr>
                    @if($selectable)
                        <th class="sticky top-0 z-20 bg-slate-50 shadow-[inset_0_-1px_0_#E2E8F0] w-12 px-4 py-3.5 align-middle text-center">
                            @if(isset($headerCheckbox))
                                {{ $headerCheckbox }}
                            @else
                                <input
                                    type="checkbox"
                                    wire:model.live="{{ $selectAllModel }}"
                                    class="rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer"
                                >
                            @endif
                        </th>
                    @endif

                    @foreach($headers as $header)
                        @php
                            $isSortable = $header['sortable'] ?? false;
                            $colIndex = $header['index'] ?? '';
                            $align = $header['align'] ?? 'left';
                            $width = $header['width'] ?? null;
                            $alignClass = match($align) {
                                'right' => 'text-right justify-end',
                                'center' => 'text-center justify-center',
                                default => 'text-left justify-start'
                            };
                            $isSorted = ($sort['column'] ?? '') === $colIndex;
                            $sortDir = $sort['direction'] ?? 'asc';
                            $thXShow = $colIndex && $colIndex !== 'actions' ? "x-show=\"cols['{$colIndex}'] !== false\"" : "";
                        @endphp

                        @if($isSortable)
                            <th wire:click="{{ $sortMethod }}('{{ $colIndex }}')"
                                {!! $thXShow !!}
                                style="{{ $width ? 'width: ' . $width . ';' : '' }}"
                                class="sticky top-0 z-20 bg-slate-50 shadow-[inset_0_-1px_0_#E2E8F0] px-4 py-3.5 {{ $headerTextSize }} font-bold uppercase tracking-wider text-slate-500 cursor-pointer hover:bg-slate-100 hover:text-[#102B70] transition-colors group select-none leading-normal {{ $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left') }}"
                            >
                                <div class="flex items-center gap-1 {{ $alignClass }}">
                                    <span class="leading-normal">{{ $header['label'] ?? '' }}</span>
                                    @if($isSorted)
                                        <span class="text-[#102B70] font-bold">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity">↕</span>
                                    @endif
                                </div>
                            </th>
                        @else
                            <th {!! $thXShow !!}
                                style="{{ $width ? 'width: ' . $width . ';' : '' }}"
                                class="sticky top-0 z-20 bg-slate-50 shadow-[inset_0_-1px_0_#E2E8F0] px-4 py-3.5 {{ $headerTextSize }} font-bold uppercase tracking-wider text-slate-500 leading-normal {{ $align === 'right' ? 'text-right pr-6' : ($align === 'center' ? 'text-center' : 'text-left') }}"
                            >
                                <span class="leading-normal">{{ $header['label'] ?? '' }}</span>
                            </th>
                        @endif
                    @endforeach
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <!-- 4. Footer / Pagination -->
    @if(isset($footer))
        <div :class="{ 'pointer-events-none opacity-50': hasError }" class="px-5 py-3.5 border-t border-[#E2E8F0] bg-white transition-opacity shrink-0">
            {{ $footer }}
        </div>
    @elseif($paginator)
        <div :class="{ 'pointer-events-none opacity-50': hasError }" class="px-5 py-3.5 border-t border-[#E2E8F0] bg-white flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600 font-medium transition-opacity shrink-0">
            <!-- Left: Results count -->
            <div>
                @if(method_exists($paginator, 'total') && $paginator->total() > 0)
                    Showing <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-slate-800">{{ number_format($paginator->total()) }}</span> {{ $paginator->total() === 1 ? 'result' : 'results' }}
                @elseif(method_exists($paginator, 'total'))
                    Showing <span class="font-bold text-slate-800">0</span> results
                @endif
            </div>

            <!-- Right: Pagination links and optional per-page selector -->
            <div class="flex items-center gap-3 sm:gap-4">
                @if(method_exists($paginator, 'hasPages') && $paginator->hasPages())
                    <div class="flex items-center">
                        {{ $paginator->links('components.pagination') }}
                    </div>
                @endif

                @if($perPageModel)
                    <div class="relative">
                        <select
                            wire:model.live="{{ $perPageModel }}"
                            class="h-8 pl-3 pr-7 rounded-lg border border-[#CBD5E1] bg-white text-xs font-semibold text-slate-700 outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] transition-colors appearance-none cursor-pointer"
                        >
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }} / page</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<style>
@media (max-width: 768px) {
    .table-responsive-cards {
        min-width: 100% !important;
        display: block !important;
        width: 100% !important;
    }
    .table-responsive-cards > thead {
        display: none !important;
    }
    .table-responsive-cards > tbody {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.75rem !important;
        padding: 0.75rem !important;
        background-color: #F8FAFC !important;
        width: 100% !important;
    }
    .table-responsive-cards > tbody > tr {
        display: block !important;
        width: 100% !important;
        background-color: transparent !important;
        border: none !important;
        height: auto !important;
    }
    .table-responsive-cards > tbody > tr > td.desktop-cell {
        display: none !important;
    }
    .table-responsive-cards > tbody > tr > td.mobile-cell {
        display: block !important;
        width: 100% !important;
        padding: 0 !important;
        border: none !important;
    }
}
@media (min-width: 769px) {
    .table-responsive-cards {
        min-width: var(--min-w, 1000px) !important;
    }
    .table-responsive-cards > tbody > tr > td.mobile-cell {
        display: none !important;
    }
}
</style>
