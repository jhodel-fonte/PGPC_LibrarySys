@if($canProcessLost && ! $isTrashed)
    @foreach($lostCopies as $lostCopy)
        @foreach($lostCopy->borrowingTransactions as $lostLoan)
            <a href="{{ route($routePrefix . 'circulation-desk.return', ['loan' => $lostLoan->id]) }}"
               wire:navigate @click="open = false"
               class="flex w-full items-center gap-2 rounded-xl px-3 py-1.5 text-sm font-semibold text-[#102B70] hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">
                {{ $lostLoan->lost_resolved_at ? 'Record recovery' : 'Resolve lost book' }} · {{ $lostCopy->accession_number }}
            </a>
        @endforeach
    @endforeach
@endif
