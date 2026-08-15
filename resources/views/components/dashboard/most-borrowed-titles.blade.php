@props(['titles' => collect()])

<div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 sm:px-7 sm:py-6 border-b border-[#E2E8F0] bg-white">
        <h2 class="text-xl font-bold text-slate-900">Most Borrowed Titles</h2>
        <p class="text-slate-500 text-xs mt-1">Top circulation this period</p>
    </div>

    <!-- List -->
    <div class="flex flex-col flex-1 divide-y divide-[#E2E8F0]">
        @forelse ($titles as $title)
            @php
                // Count total and available copies
                $totalCopies = 0;
                $availableCopies = 0;
                
                if ($title->bookDetails) {
                    foreach ($title->bookDetails as $detail) {
                        if ($detail->books) {
                            $totalCopies += $detail->books->count();
                            $availableCopies += $detail->books->where('status', 'Available')->count();
                        }
                    }
                }
            @endphp
            <div class="px-6 py-4 sm:px-7 sm:py-5 hover:bg-slate-50 transition-colors flex justify-between items-start gap-4">
                <div>
                    <h4 class="font-semibold text-sm text-slate-900 leading-tight mb-1">{{ Str::limit($title->book_title, 50) }}</h4>
                    <p class="text-xs text-slate-500">{{ $availableCopies }} available <span class="mx-1.5 text-slate-300">|</span> {{ $totalCopies }} total</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200 shrink-0">{{ $title->borrowing_transactions_count }} Borrows</span>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-slate-500 text-sm flex-1 flex items-center justify-center">
                No borrowing data available yet.
            </div>
        @endforelse
    </div>
</div>
