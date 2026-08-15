@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-400 bg-transparent rounded-md cursor-not-allowed">
                ‹
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-600 bg-transparent rounded-md hover:bg-slate-100 transition-colors focus:outline-none">
                ‹
            </button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-600 bg-transparent">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-semibold text-white bg-[#17357A] rounded-md">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-600 bg-transparent rounded-md hover:bg-slate-100 transition-colors focus:outline-none">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-600 bg-transparent rounded-md hover:bg-slate-100 transition-colors focus:outline-none">
                ›
            </button>
        @else
            <span class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-400 bg-transparent rounded-md cursor-not-allowed">
                ›
            </span>
        @endif
    </nav>
@endif
