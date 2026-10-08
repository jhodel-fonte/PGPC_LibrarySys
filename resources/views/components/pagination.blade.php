@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pageName = $paginator->getPageName();
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5 font-sans">
        {{-- Previous Page Button --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50/50 text-slate-300 cursor-not-allowed select-none" aria-disabled="true" title="Previous page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </span>
        @else
            <button
                type="button"
                wire:click="previousPage('{{ $pageName }}')"
                wire:loading.attr="disabled"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px"
                title="Previous page"
                aria-label="Previous page"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </button>
        @endif

        {{-- Page Numbers & Current Page --}}
        @if ($last <= 3)
            {{-- Small number of pages: list 1, 2, 3 --}}
            @for ($p = 1; $p <= $last; $p++)
                @if ($p == $current)
                    <span class="inline-flex h-8 px-3 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                        Page {{ $p }}
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="gotoPage({{ $p }}, '{{ $pageName }}')"
                        class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px"
                    >
                        {{ $p }}
                    </button>
                @endif
            @endfor
        @else
            @if ($current == 1)
                <span class="inline-flex h-8 px-3 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                    Page 1
                </span>
            @else
                <button
                    type="button"
                    wire:click="gotoPage(1, '{{ $pageName }}')"
                    class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px"
                >
                    1
                </button>
            @endif

            {{-- Left Ellipsis --}}
            @if ($current > 2)
                <span class="px-0.5 text-xs font-bold text-slate-400 select-none">...</span>
            @endif

            {{-- Middle: Active Current Page (if not first and not last) --}}
            @if ($current > 1 && $current < $last)
                <span class="inline-flex h-8 px-3 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                    Page {{ $current }}
                </span>
            @endif

            {{-- Right Ellipsis --}}
            @if ($current < $last - 1)
                <span class="px-0.5 text-xs font-bold text-slate-400 select-none">...</span>
            @endif

            {{-- Other End (Page $last) --}}
            @if ($current == $last)
                <span class="inline-flex h-8 px-3 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                    Page {{ $last }}
                </span>
            @else
                <button
                    type="button"
                    wire:click="gotoPage({{ $last }}, '{{ $pageName }}')"
                    class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px"
                >
                    {{ $last }}
                </button>
            @endif
        @endif

        {{-- Next Page Button --}}
        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage('{{ $pageName }}')"
                wire:loading.attr="disabled"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px"
                title="Next page"
                aria-label="Next page"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </button>
        @else
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50/50 text-slate-300 cursor-not-allowed select-none" aria-disabled="true" title="Next page">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </span>
        @endif
    </nav>
@endif
