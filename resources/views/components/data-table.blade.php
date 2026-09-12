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
    'headerTextSize' => 'text-xs'
])

@php
    $resolvedColspan = $emptyColspan ?? count($headers);
@endphp

<div class="relative z-10 rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col lg:flex-1 lg:min-h-0 font-sans {{ $textSize }}"
     x-data="{
         localSearch: @entangle($searchModel).live
     }"
>
    <!-- 1. Toolbar: Tabs, Search & Extra Actions -->
    @if(!empty($tabs) || $showSearch || isset($actions) || isset($toolbar))
        <div class="border-b border-[#E2E8F0] bg-white px-6 py-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between lg:shrink-0">
            
            <!-- Left Side: Filter Tabs or Custom Slot -->
            <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 md:pb-0">
                @if(isset($toolbarLeft))
                    {{ $toolbarLeft }}
                @elseif(!empty($tabs))
                    @foreach($tabs as $tab)
                        <button
                            type="button"
                            wire:click="{{ $tabMethod }}('{{ $tab }}')"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs uppercase tracking-wider font-bold transition-all shrink-0 {{ $activeTab === $tab ? 'bg-[#102B70] text-white shadow-sm' : 'text-slate-600 hover:text-[#102B70] hover:bg-slate-50' }}"
                        >
                            {{ $tab }}
                        </button>
                    @endforeach
                @endif
            </div>

            <!-- Right Side: Search & Extra Actions -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                @if($showSearch)
                    <div class="relative w-full md:w-[320px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <!-- Normal Search Icon -->
                            <svg wire:loading.remove wire:target="{{ $searchModel }}" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <!-- Search Loading Spinner -->
                            <svg wire:loading wire:target="{{ $searchModel }}" class="animate-spin h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <input
                            wire:model.live.debounce.300ms="{{ $searchModel }}"
                            type="text"
                            placeholder="{{ $searchPlaceholder }}"
                            class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-9 text-sm font-medium text-[#0F172A] placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] transition-all outline-none"
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

                @if(isset($actions))
                    <div class="flex items-center gap-2 shrink-0">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- 2. Scrollable Table Container -->
    <div class="overflow-x-auto overflow-y-hidden w-full relative flex-1 min-h-0 lg:overflow-y-auto">
        <table class="w-full text-left border-collapse" style="min-width: {{ $minWidth }};">
            <!-- Table Header -->
            <thead class="sticky top-0 z-10 bg-[#F8FAFC] shadow-[0_1px_0_0_#E2E8F0]">
                <tr>
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
                        @endphp

                        @if($isSortable)
                            <th wire:click="{{ $sortMethod }}('{{ $colIndex }}')"
                                style="{{ $width ? 'width: ' . $width . ';' : '' }}"
                                class="px-6 py-4 {{ $headerTextSize }} font-bold uppercase tracking-wider text-slate-600 cursor-pointer hover:bg-slate-100 hover:text-[#102B70] transition-colors group select-none {{ $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left') }}"
                            >
                                <div class="flex items-center gap-1.5 {{ $alignClass }}">
                                    <span>{{ $header['label'] ?? '' }}</span>
                                    @if($isSorted)
                                        <svg class="h-3.5 w-3.5 text-[#102B70] transition-transform {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    @else
                                        <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    @endif
                                </div>
                            </th>
                        @else
                            <th style="{{ $width ? 'width: ' . $width . ';' : '' }}"
                                class="px-6 py-4 {{ $headerTextSize }} font-bold uppercase tracking-wider text-slate-600 {{ $align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left') }}"
                            >
                                {{ $header['label'] ?? '' }}
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

    <!-- 3. Footer / Pagination -->
    @if(isset($footer))
        <div class="border-t border-[#E2E8F0] px-6 py-3.5 bg-[#F8FAFC] lg:shrink-0">
            {{ $footer }}
        </div>
    @elseif($paginator)
        <div class="border-t border-[#E2E8F0] px-6 py-3.5 bg-[#F8FAFC] lg:shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm text-slate-500 font-semibold">
            <div>
                @if(method_exists($paginator, 'total') && $paginator->total() > 0)
                    Showing <span class="font-bold text-[#0F172A]">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-[#0F172A]">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-[#0F172A]">{{ number_format($paginator->total()) }}</span> {{ $paginator->total() === 1 ? 'record' : 'records' }}
                @elseif(method_exists($paginator, 'total'))
                    Showing <span class="font-bold text-[#0F172A]">0</span> records
                @endif
            </div>

            @if(method_exists($paginator, 'hasPages') && $paginator->hasPages())
                <div>
                    {{ $paginator->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
