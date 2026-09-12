@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center justify-center w-9 h-9 text-base font-bold text-slate-300 bg-transparent rounded-lg cursor-not-allowed select-none">
                ‹
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center justify-center w-9 h-9 text-base font-bold text-slate-700 bg-transparent rounded-lg hover:bg-slate-100 hover:text-[#102B70] transition-colors focus:outline-none">
                ‹
            </button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="relative inline-flex items-center justify-center w-9 h-9 text-sm font-bold text-slate-400 bg-transparent select-none">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="relative inline-flex items-center justify-center w-9 h-9 text-sm md:text-[15px] font-bold text-white bg-[#102B70] rounded-xl shadow-xs select-none">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center justify-center w-9 h-9 text-sm md:text-[15px] font-semibold text-slate-700 bg-transparent rounded-xl hover:bg-slate-100 hover:text-[#102B70] transition-colors focus:outline-none">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex items-center justify-center w-9 h-9 text-base font-bold text-slate-700 bg-transparent rounded-lg hover:bg-slate-100 hover:text-[#102B70] transition-colors focus:outline-none">
                ›
            </button>
        @else
            <span class="relative inline-flex items-center justify-center w-9 h-9 text-base font-bold text-slate-300 bg-transparent rounded-lg cursor-not-allowed select-none">
                ›
            </span>
        @endif
    </nav>
@endif
