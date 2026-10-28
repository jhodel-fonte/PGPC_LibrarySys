<div class="min-h-full bg-[#eef3f7] text-base text-slate-800">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 space-y-5">
        
        <!-- Page Header -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 lg:p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">My Circulation & Borrowing</h1>
                    <p class="text-base text-slate-500 mt-1.5">
                        Track your currently borrowed books, upcoming due dates, complete borrowing archive, and library records.
                    </p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('opac.index') }}" class="h-11 px-5 bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold rounded-xl transition-colors flex items-center gap-2 shadow-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Catalog (OPAC)
                    </a>
                </div>
            </div>

            <!-- Quick Metric Summary Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Currently Borrowed</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5">{{ $activeCount }} <span class="text-sm text-slate-500 font-normal">/ {{ config('settings.library_config.max_loan_limit', 3) }} books</span></p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-500' }}">Overdue</p>
                    <p class="text-xl sm:text-2xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-900' }} mt-0.5">{{ $overdueCount }} <span class="text-sm text-slate-500 font-normal">items</span></p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Returned</p>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5">{{ $historyCount }} <span class="text-sm text-slate-500 font-normal">books</span></p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider {{ $unpaidFinesTotal > 0 ? 'text-amber-600' : 'text-slate-500' }}">Pending Fines</p>
                    <p class="text-xl sm:text-2xl font-bold {{ $unpaidFinesTotal > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-0.5">₱{{ number_format($unpaidFinesTotal, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Controls: Tabs & Search -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            
            <!-- Navigation Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-white border border-[#E2E8F0] rounded-xl shadow-xs shrink-0 overflow-x-auto">
                <button 
                    type="button" 
                    wire:click="setTab('active')" 
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-colors whitespace-nowrap {{ $activeTab === 'active' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Active Loans ({{ $activeCount }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('history')" 
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-colors whitespace-nowrap {{ $activeTab === 'history' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Borrowing History ({{ $historyCount }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('fines')" 
                    class="px-4 py-2 rounded-lg text-sm font-bold transition-colors whitespace-nowrap {{ $activeTab === 'fines' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Fines & Penalties ({{ $fines->count() }})
                </button>
            </div>

            <!-- Search Field -->
            @if($activeTab !== 'fines')
                <div class="relative w-full sm:w-80">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search title, accession..." 
                        class="w-full h-11 pl-10 pr-4 text-sm font-medium text-slate-800 bg-white border border-[#E2E8F0] rounded-xl focus:outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] placeholder:text-slate-400"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tab 1: Active Loans Content -->
        @if($activeTab === 'active')
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Book Details</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Accession</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Date Borrowed</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Due Date</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Loan Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($activeLoans as $loan)
                                @php
                                    $bookData = $loan->book?->bookDetail?->bookData;
                                    $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                                    $isOverdue = $loan->due_date && $loan->due_date->isPast();
                                    $daysRemaining = $loan->due_date ? (int) ceil(now()->diffInDays($loan->due_date, false)) : null;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 sm:px-7 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-15 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                                @if(!empty($loan->book?->bookDetail?->cover_image))
                                                    <img src="{{ $loan->book->bookDetail->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-base font-bold text-slate-900 line-clamp-1" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                                    {{ $bookData->book_title ?? 'Untitled Book' }}
                                                </p>
                                                <p class="text-sm text-slate-500 truncate mt-0.5">
                                                    {{ $authors }}
                                                </p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Type: {{ $loan->borrowType?->type ?? 'Regular Loan' }} • Condition: {{ $loan->issuedCondition?->condition ?? 'Good' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm font-bold text-slate-700 whitespace-nowrap">
                                        {{ $loan->book?->accession_number ?? '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm text-slate-600 whitespace-nowrap">
                                        {{ $loan->issued_date ? $loan->issued_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm font-bold whitespace-nowrap {{ $isOverdue ? 'text-red-600' : 'text-slate-900' }}">
                                        {{ $loan->due_date ? $loan->due_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-right whitespace-nowrap">
                                        @if ($isOverdue)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-red-100 text-red-700 border border-red-200">
                                                Overdue by {{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'day' : 'days' }}
                                            </span>
                                        @elseif ($daysRemaining === 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Due Today
                                            </span>
                                        @elseif ($daysRemaining <= 2)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Due in {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'day' : 'days' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Due in {{ $daysRemaining }} days
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-base font-bold text-slate-700">No active book loans found.</p>
                                        <p class="text-sm text-slate-500 mt-1">Visit the library or browse the OPAC catalog to check out books.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($activeLoans->hasPages())
                    <div class="px-6 py-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $activeLoans->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 2: Borrowing History Content -->
        @if($activeTab === 'history')
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Book Details</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Accession</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Borrowed</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Returned On</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($historyLoans as $record)
                                @php
                                    $bookData = $record->book?->bookDetail?->bookData;
                                    $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                                    $wasLate = $record->due_date && $record->return_date && $record->return_date->gt($record->due_date);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 sm:px-7 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-15 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                                @if(!empty($record->book?->bookDetail?->cover_image))
                                                    <img src="{{ $record->book->bookDetail->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-base font-bold text-slate-900 line-clamp-1" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                                    {{ $bookData->book_title ?? 'Untitled Book' }}
                                                </p>
                                                <p class="text-sm text-slate-500 truncate mt-0.5">
                                                    {{ $authors }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm font-semibold text-slate-700 whitespace-nowrap">
                                        {{ $record->book?->accession_number ?? '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm text-slate-600 whitespace-nowrap">
                                        {{ $record->issued_date ? $record->issued_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm text-slate-800 whitespace-nowrap">
                                        <span class="font-bold">{{ $record->return_date ? $record->return_date->format('M d, Y') : '—' }}</span>
                                        @if($wasLate)
                                            <span class="text-xs text-amber-700 block font-semibold">Late Return</span>
                                        @endif
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-right whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-semibold bg-slate-100 text-slate-700">
                                            {{ $record->returnCondition?->condition ?? 'Good' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-base font-bold text-slate-700">No borrowing history found.</p>
                                        <p class="text-sm text-slate-500 mt-1">Returned books will appear here in your circulation archive.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($historyLoans->hasPages())
                    <div class="px-6 py-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $historyLoans->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 3: Fines Content -->
        @if($activeTab === 'fines')
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Related Item / Note</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Penalty Type</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Amount</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Fine Due Date</th>
                                <th class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($fines as $fine)
                                @php
                                    $bookTitle = $fine->transaction?->book?->bookDetail?->bookData?->book_title ?? 'General Library Penalty';
                                    $isPaid = !is_null($fine->paid_at);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 sm:px-7 py-4">
                                        <p class="text-base font-bold text-slate-900 line-clamp-1">{{ $bookTitle }}</p>
                                        @if($fine->note)
                                            <p class="text-sm text-slate-500 mt-0.5">{{ $fine->note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm font-semibold text-slate-700 whitespace-nowrap">
                                        {{ $fine->fineType?->name ?? 'Overdue Fine' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-base font-bold text-slate-900 whitespace-nowrap">
                                        ₱{{ number_format($fine->amount, 2) }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-sm text-slate-600 whitespace-nowrap">
                                        {{ $fine->fine_due_date ? $fine->fine_due_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 sm:px-7 py-4 text-right whitespace-nowrap">
                                        @if($isPaid)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Paid on {{ $fine->paid_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Unpaid Balance
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-base font-bold text-slate-800">No library fines or penalties</p>
                                        <p class="text-sm text-slate-500 mt-1">Your library account is in excellent standing with zero fees.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>
