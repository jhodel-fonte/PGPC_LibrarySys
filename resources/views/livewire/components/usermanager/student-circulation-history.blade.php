@php
    $circulationHistory = $circulationHistory ?? (isset($this->circulationHistory) ? $this->circulationHistory : collect());
@endphp

<div class="rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-5">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Complete Circulation History</h3>
                <p class="text-xs text-slate-500 font-medium">All recorded borrow, renewal, and return transactions for this patron.</p>
            </div>
        </div>

    </div>

    <!-- Table of History -->
    <div class="overflow-x-auto -mx-5 sm:-mx-6 lg:-mx-7 px-5 sm:px-6 lg:px-7">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="border-b border-[#F1F5F9] text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                    <th class="py-3 px-3 text-center w-10">#</th>
                    <th class="py-3 px-4">Book Title & Accession</th>
                    <th class="py-3 px-4">Borrowed Date</th>
                    <th class="py-3 px-4">Due Date</th>
                    <th class="py-3 px-4">Returned Date</th>
                    <th class="py-3 px-4">Processed By</th>
                    <th class="py-3 px-4">Fines</th>
                    <th class="py-3 px-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($circulationHistory as $index => $hist)
                    @php
                        $hBook = $hist->book;
                        $hBookDetail = $hBook?->bookDetail;
                        $hBookData = $hBookDetail?->bookData;
                        $hTitle = $hBookData?->book_title ?? 'Untitled Book';
                        $hAccession = $hBook?->accession_number ?? ('ACC-' . date('Y') . '-' . str_pad($hist->id, 4, '0', STR_PAD_LEFT));
                        $hBorrowDate = $hist->issued_date ? $hist->issued_date->format('M d, Y') : '—';
                        $hDueDate = $hist->due_date ? $hist->due_date->format('M d, Y') : '—';
                        $hReturnDate = $hist->return_date ? $hist->return_date->format('M d, Y') : null;
                        $hIsReturned = !empty($hist->return_date);
                        $hIsOverdue = !$hIsReturned && $hist->due_date && $hist->due_date->isPast();
                        $hFine = $hist->fines?->sum('amount') ?? 0;
                        $librarianStaff = $hist->issuedBy
                            ? trim($hist->issuedBy->first_name . ' ' . $hist->issuedBy->last_name)
                            : 'Librarian Staff';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-3 text-center text-xs font-semibold text-slate-400">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3.5 px-4 min-w-[200px]">
                            <div class="font-bold text-[#0F172A] truncate max-w-[240px]">
                                {{ $hTitle }}
                            </div>
                            <div class="font-mono text-xs text-slate-500 font-semibold">
                                {{ $hAccession }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                            {{ $hBorrowDate }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                            {{ $hDueDate }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                            {{ $hReturnDate ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-medium text-slate-600 whitespace-nowrap">
                            {{ $librarianStaff }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($hFine > 0)
                                <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 text-xs">
                                    ₱{{ number_format($hFine, 2) }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">None</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            @if($hIsReturned)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                    Returned
                                </span>
                            @elseif($hIsOverdue)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold text-red-700 bg-red-50 border border-red-200">
                                    Overdue
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200">
                                    Active
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <p class="text-xs font-semibold text-slate-500">No circulation history recorded yet for this student.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
