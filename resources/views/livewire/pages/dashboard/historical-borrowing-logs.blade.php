<div class="admin-page-table bg-[#F8FAFC] flex-1 overflow-auto font-sans">
    <div class="mx-auto w-full max-w-[1600px] p-3 sm:p-4 lg:p-6 space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#102B70]">Historical Borrowing Log</h1>
                <p class="mt-1 text-sm text-slate-600">Imported handwritten records. Borrower and return status are unknown; these are not active loans.</p>
            </div>
            <a href="{{ route('admin.circulation-desk.borrowed-books') }}" wire:navigate class="text-sm font-semibold text-[#102B70] underline underline-offset-2">Back to borrowed books</a>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <label for="history-search" class="block text-sm font-semibold text-slate-700">Search accession number</label>
            <input id="history-search" type="search" wire:model.live.debounce.300ms="search" class="mt-1 w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#102B70] focus:ring-[#102B70]" placeholder="Accession number">
            <p class="mt-3 text-sm text-slate-600">{{ $logs->total() }} entries</p>
            <div class="mt-3 overflow-x-auto" tabindex="0" role="region" aria-label="Historical borrowing log">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-600"><tr><th scope="col" class="p-3">Borrowed date</th><th scope="col" class="p-3">Accession</th><th scope="col" class="p-3">Borrower</th><th scope="col" class="p-3">Source</th><th scope="col" class="p-3">Review note</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr><td class="p-3">{{ $log->borrowed_date ?: 'Unknown' }}</td><td class="p-3 font-mono">{{ $log->accession_number ?: 'Unknown' }}</td><td class="p-3">Unknown</td><td class="p-3">{{ $log->source_image ?: $log->source_file }}@if($log->page) · page {{ $log->page }}@endif</td><td class="p-3">{{ $log->review_note ?: '—' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-center text-slate-600">No historical log entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
