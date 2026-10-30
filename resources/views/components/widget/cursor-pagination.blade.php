@php
    $previous = $paginator->previousCursor()?->encode();
    $next = $paginator->nextCursor()?->encode();
    $cursorName = $paginator->getCursorName();
@endphp

<nav class="flex flex-wrap items-center gap-2" aria-label="Pagination">
    <button type="button" wire:click="setPage('{{ $previous }}', '{{ $cursorName }}')" @disabled(! $previous)
        wire:loading.attr="disabled" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
        Previous
    </button>
    <button type="button" wire:click="setPage('{{ $next }}', '{{ $cursorName }}')" @disabled(! $next)
        wire:loading.attr="disabled" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
        Next
    </button>
</nav>
