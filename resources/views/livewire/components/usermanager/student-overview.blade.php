@php
    $student = $student ?? null;
    $activeLoans = $activeLoans ?? (isset($this->activeLoans) ? $this->activeLoans : collect());
    $activeLoansCount = $activeLoansCount ?? (isset($this->activeLoansCount) ? $this->activeLoansCount : ($activeLoans ? $activeLoans->count() : 0));
    $overdueLoansCount = $overdueLoansCount ?? (isset($this->overdueLoansCount) ? $this->overdueLoansCount : 0);
    $unpaidFinesTotal = $unpaidFinesTotal ?? (isset($this->unpaidFinesTotal) ? $this->unpaidFinesTotal : 0.0);
@endphp

<div class="space-y-6">

    <!-- Middle Row Grid (Circulation Summary + Library Notes & Remarks) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left: Circulation Summary (lg:col-span-7) -->
        <div class="lg:col-span-7 rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-4">
            <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-[#102B70]">Circulation Summary</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <!-- Metric 1: Active Borrowed Books -->
                <div class="rounded-xl border border-blue-100 bg-[#EFF6FF]/50 p-3 flex flex-col justify-center space-y-1">
                    <span class="text-xs font-semibold text-slate-600">Active Borrowed Books</span>
                    <div class="text-xl sm:text-2xl font-black text-[#102B70]">
                        {{ $activeLoansCount }}
                    </div>
                </div>

                <!-- Metric 2: Overdue Items -->
                <div class="rounded-xl border border-amber-100 bg-[#FFFBEB]/60 p-3 flex flex-col justify-center space-y-1">
                    <span class="text-xs font-semibold text-slate-600">Overdue Items</span>
                    <div class="text-xl sm:text-2xl font-black {{ $overdueLoansCount > 0 ? 'text-red-600' : 'text-slate-800' }}">
                        {{ $overdueLoansCount }}
                    </div>
                </div>

                <!-- Metric 3: Unpaid Penalties & Fines -->
                <div class="rounded-xl border border-purple-100 bg-[#FAF5FF]/60 p-3 flex flex-col justify-center space-y-1">
                    <span class="text-xs font-semibold text-slate-600">Unpaid Penalties & Fines</span>
                    <div class="text-xl sm:text-2xl font-black {{ $unpaidFinesTotal > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        ₱{{ number_format($unpaidFinesTotal, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Library Notes & Remarks (lg:col-span-5) -->
        <div class="lg:col-span-5 rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-[0_4px_20px_rgba(16,43,112,0.03)] flex flex-col justify-between space-y-3">
            <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-[#102B70]">Library Notes & Remarks</h3>
            </div>

            <div class="p-3 rounded-xl border border-slate-100 bg-[#F8FAFC] text-xs leading-relaxed text-slate-700 flex-1 flex flex-col justify-center">
                @if($student?->note)
                    <p class="whitespace-pre-line">{{ $student->note }}</p>
                @else
                    <p class="italic text-slate-400">No notes recorded.</p>
                @endif
            </div>

            <div class="flex items-center justify-between text-[11px] font-medium text-slate-400 pt-0.5">
                <span>Last updated: {{ $student?->updated_at ? $student->updated_at->format('M d, Y') : now()->format('M d, Y') }}</span>
            </div>
        </div>

    </div>

    <!-- Card: Current Loan Table -->
    <div class="rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Current Borrowed Books</h3>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto -mx-5 sm:-mx-6 lg:-mx-7 px-5 sm:px-6 lg:px-7">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-[#F1F5F9] text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        <th class="py-3 px-3 text-center w-10">#</th>
                        <th class="py-3 px-3">Book Cover</th>
                        <th class="py-3 px-4">Title & Author</th>
                        <th class="py-3 px-4">Accession No.</th>
                        <th class="py-3 px-4">Borrowed Date</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activeLoans as $index => $loan)
                        @php
                            $book = $loan->book;
                            $bookDetail = $book?->bookDetail;
                            $bookData = $bookDetail?->bookData;
                            $bookTitle = $bookData?->book_title ?? 'The Alchemist';
                            $authors = $bookData?->authors;
                            $authorStr = ($authors && $authors->count() > 0)
                                ? $authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->filter()->join(', ')
                                : 'Paulo Coelho';
                            $accessionNo = $book?->accession_number ?? ('ACC-' . date('Y') . '-' . str_pad($loan->id, 4, '0', STR_PAD_LEFT));
                            $borrowedDateStr = $loan->issued_date ? $loan->issued_date->format('M d, Y') : now()->subDays(7)->format('M d, Y');
                            $dueDateStr = $loan->due_date ? $loan->due_date->format('M d, Y') : now()->addDays(7)->format('M d, Y');
                            $isOverdue = $loan->due_date && $loan->due_date->isPast();
                            $coverUrl = $bookDetail?->cover_url ?? asset('images/book-cover.webp');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-3 text-center text-xs font-semibold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="h-12 w-9 rounded-md overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-2xs">
                                    <img
                                        src="{{ $coverUrl }}"
                                        alt="{{ $bookTitle }}"
                                        class="h-full w-full object-cover"
                                        onerror="this.onerror=null;this.src='{{ asset('images/book-cover.webp') }}';"
                                    >
                                </div>
                            </td>
                            <td class="py-3.5 px-4 min-w-[200px]">
                                <div class="font-bold text-[#0F172A] hover:text-[#102B70] transition-colors truncate max-w-[240px]">
                                    {{ $bookTitle }}
                                </div>
                                <div class="text-xs text-slate-500 font-medium truncate max-w-[240px]">
                                    {{ $authorStr }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700 whitespace-nowrap">
                                {{ $accessionNo }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                {{ $borrowedDateStr }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-semibold {{ $isOverdue ? 'text-red-600' : 'text-slate-700' }} whitespace-nowrap">
                                {{ $dueDateStr }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold text-red-700 bg-red-50 border border-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Overdue
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                        Borrowed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-xs font-bold text-[#102B70] shadow-2xs transition-all cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <span>View Details</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-slate-500">No books currently borrowed by this student.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
