<div wire:poll.60s class="p-4 md:p-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-[#102B70]">Logs</h1>
        <p class="mt-1 text-sm text-slate-600">Review recorded library activity. Times are shown in Philippine time.</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <label class="text-sm font-medium text-slate-700">Librarian or actor<input wire:model.live.debounce.400ms="actor" type="search" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Search actor"></label>
        <label class="text-sm font-medium text-slate-700">Action<input wire:model.live.debounce.400ms="action" type="search" class="mt-1 w-full rounded-lg border-slate-300" placeholder="e.g. book.created"></label>
        <label class="text-sm font-medium text-slate-700">Book, record type or ID<input wire:model.live.debounce.400ms="subject" type="search" class="mt-1 w-full rounded-lg border-slate-300" placeholder="e.g. title, Book or 123"></label>
        <label class="text-sm font-medium text-slate-700">From<input wire:model.live="from" type="date" class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label class="text-sm font-medium text-slate-700">To<input wire:model.live="to" type="date" class="mt-1 w-full rounded-lg border-slate-300"></label>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-700"><tr><th scope="col" class="p-4">Date and time</th><th scope="col" class="p-4">Actor</th><th scope="col" class="p-4">Action</th><th scope="col" class="p-4">Record</th><th scope="col" class="p-4">Details</th></tr></thead>
            <tbody class="divide-y divide-slate-200">
            @forelse($logs as $log)
                <tr class="align-top"><td class="p-4 whitespace-nowrap">{{ $log->time }}</td><td class="p-4">{{ $log->actor }}</td><td class="p-4">{{ $log->action }}</td><td class="p-4">{{ $log->record }}</td><td class="p-4"><button type="button" class="text-blue-800 underline underline-offset-2" x-data @click="$dispatch('open-details-drawer', {{ \Illuminate\Support\Js::from($log->details) }})">View changes</button></td></tr>
            @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-600">No audit entries match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
