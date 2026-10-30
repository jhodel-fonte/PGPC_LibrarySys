<div class="student-page min-w-0 w-full min-h-full bg-[#eef3f7] text-base text-slate-800">
    <div class="mx-auto w-full max-w-[1600px] p-3 sm:p-4 lg:p-6 space-y-5">
        
        <!-- Page Header -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 lg:p-6">
            <div class="student-page-heading flex flex-col justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">My Circulation & Borrowing</h1>
                    <p class="text-base text-slate-500 mt-1.5">
                        Track your currently borrowed books, upcoming due dates, complete borrowing archive, and library records.
                    </p>
                </div>
                
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <a href="{{ route('opac.index') }}" class="h-11 w-full px-5 bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-xs shrink-0 sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Catalog (OPAC)
                    </a>
                </div>
            </div>

            <!-- Quick Metric Summary Strip -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
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
        <div class="student-borrow-controls flex flex-col items-stretch justify-between gap-4">
            
            <!-- Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-white border border-[#E2E8F0] rounded-xl shadow-xs min-w-0 max-w-full">
                <button 
                    type="button" 
                    wire:click="setTab('active')" 
                    class="min-h-11 px-3.5 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-colors whitespace-normal flex-1 cursor-pointer {{ $activeTab === 'active' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Active Loans ({{ $activeCount }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('history')" 
                    class="min-h-11 px-3.5 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-colors whitespace-normal flex-1 cursor-pointer {{ $activeTab === 'history' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Borrowing History ({{ $historyCount }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('fines')" 
                    class="min-h-11 px-3.5 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-colors whitespace-normal flex-1 cursor-pointer {{ $activeTab === 'fines' ? 'bg-[#102B70] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                >
                    Fines & Penalties ({{ $fines->count() }})
                </button>
            </div>

            <!-- Search Field -->
            @if($activeTab !== 'fines')
                <div class="relative w-full min-w-0">
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
                <!-- Mobile Card Layout (< 768px) -->
                <div class="student-record-cards divide-y divide-[#E2E8F0]">
                    @forelse ($activeLoans as $loan)
                        @php
                            $bookDetail = $loan->book?->bookDetail;
                            $bookData = $bookDetail?->bookData;
                            $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                            $isOverdue = $loan->due_date && $loan->due_date->isPast();
                            $daysRemaining = $loan->due_date ? (int) ceil(now()->diffInDays($loan->due_date, false)) : null;
                            $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-16 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                    <img src="{{ $coverUrl }}" alt="Cover" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-2" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                        {{ $bookData->book_title ?? 'Untitled Book' }}
                                    </p>
                                    @if($loan->book?->bookDetail?->catalog_kind !== 'book')
                                        <span class="inline-block mt-0.5 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                            Physical item &bull; {{ ucfirst($loan->book?->bookDetail?->catalog_kind ?? 'item') }}
                                        </span>
                                    @endif
                                    <p class="text-xs text-slate-500 truncate mt-0.5">
                                        {{ $authors }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $loan->borrowType?->type ?? 'Regular Loan' }} &bull; {{ $loan->issuedCondition?->condition ?? 'Good' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                                <div>
                                    <span class="text-slate-400 font-medium block">Accession</span>
                                    <span class="font-mono font-semibold text-slate-700">{{ $loan->book?->accession_number ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Borrowed</span>
                                    <span class="font-medium text-slate-700">{{ $loan->issued_date ? $loan->issued_date->format('M d, Y') : '—' }}</span>
                                </div>
                                <div class="col-span-2 flex items-center justify-between pt-1 border-t border-slate-200/60">
                                    <div>
                                        <span class="text-slate-400 font-medium">Due:</span>
                                        <span class="font-bold ml-1 {{ $isOverdue ? 'text-red-600' : 'text-slate-900' }}">
                                            {{ $loan->due_date ? $loan->due_date->format('M d, Y') : '—' }}
                                        </span>
                                    </div>
                                    <div>
                                        @if ($isOverdue)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                                Overdue ({{ abs($daysRemaining) }}d)
                                            </span>
                                        @elseif ($daysRemaining === 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Due Today
                                            </span>
                                        @elseif ($daysRemaining <= 2)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Due in {{ $daysRemaining }}d
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Due in {{ $daysRemaining }}d
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-base font-bold text-slate-700">No active book loans found.</p>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Visit the library or browse the OPAC catalog to check out books.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop / Tablet Table View (768px+) -->
                <div tabindex="0" role="region" aria-label="Active loans table" class="student-record-table overflow-x-auto focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Book Details</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Accession</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Date Borrowed</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Due Date</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Loan Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($activeLoans as $loan)
                                @php
                                    $bookDetail = $loan->book?->bookDetail;
                                    $bookData = $bookDetail?->bookData;
                                    $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                                    $isOverdue = $loan->due_date && $loan->due_date->isPast();
                                    $daysRemaining = $loan->due_date ? (int) ceil(now()->diffInDays($loan->due_date, false)) : null;
                                    $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 sm:px-7 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-15 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                                <img src="{{ $coverUrl }}" alt="Cover" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-base font-bold text-slate-900 line-clamp-1" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                                    {{ $bookData->book_title ?? 'Untitled Book' }}
                                                </p>
                                                @if($loan->book?->bookDetail?->catalog_kind !== 'book')<span class="text-xs font-semibold text-blue-700">Physical item · {{ ucfirst($loan->book?->bookDetail?->catalog_kind ?? 'item') }}</span>@endif
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
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $activeLoans->links('components.widget.cursor-pagination') }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 2: Borrowing History Content -->
        @if($activeTab === 'history')
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                <!-- Mobile Card Layout (< 768px) -->
                <div class="student-record-cards divide-y divide-[#E2E8F0]">
                    @forelse ($historyLoans as $record)
                        @php
                            $bookDetail = $record->book?->bookDetail;
                            $bookData = $bookDetail?->bookData;
                            $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                            $wasLate = $record->due_date && $record->return_date && $record->return_date->gt($record->due_date);
                            $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-16 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                    <img src="{{ $coverUrl }}" alt="Cover" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-2" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                        {{ $bookData->book_title ?? 'Untitled Book' }}
                                    </p>
                                    @if($record->book?->bookDetail?->catalog_kind !== 'book')
                                        <span class="inline-block mt-0.5 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                            Physical item &bull; {{ ucfirst($record->book?->bookDetail?->catalog_kind ?? 'item') }}
                                        </span>
                                    @endif
                                    <p class="text-xs text-slate-500 truncate mt-0.5">
                                        {{ $authors }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                                <div>
                                    <span class="text-slate-400 font-medium block">Accession</span>
                                    <span class="font-mono font-semibold text-slate-700">{{ $record->book?->accession_number ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Borrowed</span>
                                    <span class="font-medium text-slate-700">{{ $record->issued_date ? $record->issued_date->format('M d, Y') : '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Returned On</span>
                                    <span class="font-bold text-slate-800">{{ $record->return_date ? $record->return_date->format('M d, Y') : '—' }}</span>
                                    @if($wasLate)
                                        <span class="text-xs text-amber-700 block font-semibold">Late Return</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Condition</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-200/80 text-slate-700 mt-0.5">
                                        {{ $record->returnCondition?->condition ?? 'Good' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-base font-bold text-slate-700">No borrowing history found.</p>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Returned books will appear here in your circulation archive.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop / Tablet Table View (768px+) -->
                <div tabindex="0" role="region" aria-label="Borrowing history table" class="student-record-table overflow-x-auto focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Book Details</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Accession</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Borrowed</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Returned On</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($historyLoans as $record)
                                @php
                                    $bookDetail = $record->book?->bookDetail;
                                    $bookData = $bookDetail?->bookData;
                                    $authors = $bookData?->authors ? $bookData->authors->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') : 'Unknown Author';
                                    $wasLate = $record->due_date && $record->return_date && $record->return_date->gt($record->due_date);
                                    $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 sm:px-7 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-15 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                                <img src="{{ $coverUrl }}" alt="Cover" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/book-cover.webp') }}'">
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-base font-bold text-slate-900 line-clamp-1" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                                    {{ $bookData->book_title ?? 'Untitled Book' }}
                                                </p>
                                                @if($record->book?->bookDetail?->catalog_kind !== 'book')<span class="text-xs font-semibold text-blue-700">Physical item · {{ ucfirst($record->book?->bookDetail?->catalog_kind ?? 'item') }}</span>@endif
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
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                        {{ $historyLoans->links('components.widget.cursor-pagination') }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab 3: Fines Content -->
        @if($activeTab === 'fines')
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                <!-- Mobile Card Layout (< 768px) -->
                <div class="student-record-cards divide-y divide-[#E2E8F0]">
                    @forelse ($fines as $fine)
                        @php
                            $bookTitle = $fine->transaction?->book?->bookDetail?->bookData?->book_title ?? 'General Library Penalty';
                            $isPaid = $fine->fine_status === 'paid';
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-2">{{ $bookTitle }}</p>
                                    @if($fine->note)
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $fine->note }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-base font-extrabold text-slate-900">₱{{ number_format($fine->amount, 2) }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                                <div>
                                    <span class="text-slate-400 font-medium block">Penalty Type</span>
                                    <span class="font-semibold text-slate-700">{{ $fine->fineType?->name ?? 'Overdue Fine' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Due Date</span>
                                    <span class="font-medium text-slate-700">{{ $fine->fine_due_date ? $fine->fine_due_date->format('M d, Y') : '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 font-medium block">Status</span>
                                    @if($isPaid)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Paid
                                        </span>
                                    @elseif($fine->fine_status === 'waived')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">
                                            Waived
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Unpaid
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-base font-bold text-slate-800">No library fines or penalties</p>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1">Your library account is in excellent standing with zero fees.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop / Tablet Table View (768px+) -->
                <div tabindex="0" role="region" aria-label="Fines and penalties table" class="student-record-table overflow-x-auto focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Related Item / Note</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Penalty Type</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider">Fine Due Date</th>
                                <th scope="col" class="py-3.5 px-6 sm:px-7 text-xs font-bold text-slate-600 uppercase tracking-wider text-right">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] bg-white">
                            @forelse ($fines as $fine)
                                @php
                                    $bookTitle = $fine->transaction?->book?->bookDetail?->bookData?->book_title ?? 'General Library Penalty';
                                    $isPaid = $fine->fine_status === 'paid';
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
                                                Paid{{ $fine->paid_at ? ' on ' . $fine->paid_at->format('M d, Y') : '' }}
                                            </span>
                                        @elseif($fine->fine_status === 'waived')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-slate-100 text-slate-700">Waived</span>
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
