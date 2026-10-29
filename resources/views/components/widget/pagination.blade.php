@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pageName = $paginator->getPageName();
    @endphp

    <nav
        x-data="{
            pageInput: {{ $current }},
            jumpToPage() {
                let p = parseInt(this.pageInput, 10);
                if (isNaN(p)) {
                    this.pageInput = {{ $current }};
                    return;
                }
                p = Math.max(1, Math.min({{ $last }}, p));
                this.pageInput = p;
                if (p !== {{ $current }}) {
                    $wire.gotoPage(p, '{{ $pageName }}');
                }
            }
        }"
        x-effect="pageInput = {{ $current }}"
        role="navigation"
        aria-label="Pagination Navigation"
        class="flex flex-wrap items-center gap-1.5 font-sans"
    >
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
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px cursor-pointer"
                title="Previous page"
                aria-label="Previous page"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </button>
        @endif

        {{-- First Page (1) Button --}}
        @if ($current == 1)
            <span class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                1
            </span>
        @else
            <button
                type="button"
                wire:click="gotoPage(1, '{{ $pageName }}')"
                class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px cursor-pointer"
            >
                1
            </button>
        @endif

        {{-- Center Manual Page Input --}}
        <div class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-2.5 text-xs font-bold text-[#0F172A] shadow-2xs transition focus-within:border-[#102B70] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#EFF6FF]">
            <span class="text-[#64748B] font-semibold select-none">Page</span>
            <input
                type="number"
                min="1"
                max="{{ $last }}"
                x-model="pageInput"
                @keydown.enter.prevent="jumpToPage()"
                @blur="jumpToPage()"
                aria-label="Current page, type a page number and press Enter"
                class="h-6 w-11 rounded bg-white border border-[#E2E8F0] px-1 text-center text-xs font-bold text-[#102B70] outline-none focus:border-[#102B70] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
            >
            <span class="text-[#64748B] font-semibold select-none">of {{ $last }}</span>
        </div>

        {{-- Last Page ($last) Button --}}
        @if ($last > 1)
            @if ($current == $last)
                <span class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg bg-[#102B70] text-xs font-bold text-white shadow-xs select-none">
                    {{ $last }}
                </span>
            @else
                <button
                    type="button"
                    wire:click="gotoPage({{ $last }}, '{{ $pageName }}')"
                    class="inline-flex h-8 min-w-[32px] px-2.5 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-xs font-bold text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px cursor-pointer"
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
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white text-slate-700 shadow-2xs transition hover:border-[#102B70]/30 hover:bg-blue-50/60 hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] active:translate-y-px cursor-pointer"
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
