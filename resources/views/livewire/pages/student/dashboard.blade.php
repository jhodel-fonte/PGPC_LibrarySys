<div class="min-h-full bg-[#eef3f7]">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 space-y-5">
        
        <!-- Top Row: Student Hero Banner & Status Notice -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.15fr)_minmax(330px,1fr)] gap-5 items-stretch">
            
            <!-- Student Greeting Banner -->
            <div class="bg-[#17357A] rounded-[16px] p-5 sm:p-6 lg:p-7 relative overflow-hidden shadow-sm flex flex-col justify-center">
                <!-- Background Decoration -->
                <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(105deg, transparent 0%, transparent 60%, rgba(255,255,255,.03) 60%);"></div>

                <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <!-- Left Column: Student Greeting & Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="text-blue-200 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em]">
                                {{ strtoupper(now()->format('l, F j, Y')) }}
                            </span>
                        </div>
                        
                        <h1 class="text-2xl sm:text-[28px] lg:text-[30px] font-bold text-white leading-tight mb-2">
                            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, 
                            <span class="text-[#FFC61A]">{{ $student ? ($student->first_name . ' ' . $student->last_name) : (auth()->user()?->name ?? 'Student') }}</span>
                        </h1>
                        
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm sm:text-[15px] text-blue-100/90 mt-2">
                            @if($student?->school_id_number)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-blue-200 text-xs sm:text-sm font-medium">Student ID:</span>
                                    <span class="font-bold text-white font-mono text-sm sm:text-base tracking-wider">{{ $student->school_id_number }}</span>
                                </div>
                            @endif
                            @if($student?->program)
                                <span class="text-blue-300/60 hidden sm:inline">•</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-blue-200 text-xs sm:text-sm font-medium">Program:</span>
                                    <span class="font-semibold text-white text-sm sm:text-base">{{ $student->program }}</span>
                                </div>
                            @endif
                            @if($student?->year_level)
                                <span class="text-blue-300/60 hidden sm:inline">•</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-blue-200 text-xs sm:text-sm font-medium">Year:</span>
                                    <span class="font-semibold text-white text-sm sm:text-base">{{ $student->year_level }}</span>
                                </div>
                            @endif
                        </div>

                        <p class="text-blue-100/90 text-xs sm:text-[13.5px] leading-relaxed max-w-[620px] mt-2.5">
                            Welcome to your student library portal. Track your checked-out books, view upcoming return deadlines, and discover resources for your research and courses.
                        </p>

                        <!-- Quick Action Button -->
                        <div class="mt-4 pt-1">
                            <button
                                type="button"
                                @click="$dispatch('open-qr-modal', {
                                    value: '{{ $studentIdValue ?? '' }}',
                                    name: '{{ $student ? ($student->first_name . ' ' . $student->last_name) : (auth()->user()?->name ?? 'Student') }}',
                                    title: 'PGPC Student',
                                    subtitle: 'Digital Identification Pass',
                                    secondaryInfo: '{{ $student?->program ?? 'Student' }}@if($student?->year_level) • {{ $student->year_level }}@endif',
                                    label: 'Student ID Number:',
                                    instruction: 'Present this QR code to the library circulation desk or scanner to borrow, return, or verify your library account.'
                                })"
                                class="w-full sm:w-auto h-10 px-5 bg-[#FFC61A] hover:bg-[#e6b217] text-[#102B70] text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer shadow-sm active:scale-[0.99] whitespace-nowrap"
                            >
                                <svg class="w-4.5 h-4.5 shrink-0 text-[#102B70]" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span>Student QR Code</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Requires Attention / Account Summary Card -->
            <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 lg:p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900">Library Summary</h2>
                    </div>

                    <div class="space-y-3">
                        @if($overdueCount > 0)
                            <!-- Overdue Alert -->
                            <a href="{{ route('student.borrow') }}" wire:navigate class="group block">
                                <div class="flex min-h-[58px] items-center gap-3 rounded-xl border border-red-200 bg-red-50/70 p-3.5 transition-colors hover:bg-red-50">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-red-800">Action Required: Overdue Items</p>
                                        <p class="text-[11px] text-red-600 truncate">Return books to avoid additional fines</p>
                                    </div>
                                    <span class="text-base font-bold tabular-nums text-red-700 bg-white px-2 py-0.5 rounded-md border border-red-200">
                                        {{ $overdueCount }}
                                    </span>
                                </div>
                            </a>
                        @endif

                        @if($unpaidFines > 0)
                            <!-- Unpaid Fines Alert -->
                            <div class="flex min-h-[58px] items-center gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-amber-900">Unpaid Library Fines</p>
                                    <p class="text-[11px] text-amber-700 truncate">Settle at circulation desk</p>
                                </div>
                                <span class="text-xs font-bold tabular-nums text-amber-800 bg-white px-2 py-0.5 rounded-md border border-amber-200">
                                    ₱{{ number_format($unpaidFines, 2) }}
                                </span>
                            </div>
                        @endif

                        @if($overdueCount === 0 && $unpaidFines <= 0)
                            <!-- Clear Status -->
                            <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-emerald-900">Account in Good Standing</p>
                                    <p class="text-[11px] text-emerald-700">No overdue items or pending fees</p>
                                </div>
                            </div>
                        @endif

                        <!-- Patron Borrowing Allowance Details -->
                        <div class="pt-2 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="text-slate-500">Max Loan Limit:</span>
                                <span class="font-bold text-slate-900">{{ config('settings.library_config.max_loan_limit', 3) }} Books</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="text-slate-500">Loan Period:</span>
                                <span class="font-bold text-slate-900">{{ config('settings.library_config.loan_period', 5) }} Calendar Days</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="text-slate-500">Library Desk Hours:</span>
                                <span class="font-medium text-slate-700">{{ config('settings.library_config.desk_hours', '8:00 AM – 5:00 PM') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100">
                    <a href="{{ route('student.borrow') }}" wire:navigate class="w-full py-2 px-3 text-center text-xs font-bold text-[#102B70] hover:text-[#0B225E] hover:bg-slate-50 rounded-lg transition-colors block">
                        Open Borrowing Records &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Middle Row: 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <x-dashboard.stat-card 
                title="Active Loans" 
                :value="$activeCount" 
                subtext="Books currently in your care" 
            />
            
            <div class="bg-white rounded-2xl border {{ $overdueCount > 0 ? 'border-red-300 ring-2 ring-red-100' : 'border-slate-200' }} shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] px-6 py-5 flex flex-col justify-center h-[120px]">
                <h3 class="text-[11px] font-bold tracking-[0.05em] uppercase mb-1 {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-500' }}">
                    Overdue Items
                </h3>
                <div class="text-[27px] font-bold leading-tight mb-1 {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-900' }}">
                    {{ $overdueCount }}
                </div>
                <p class="text-xs font-bold {{ $overdueCount > 0 ? 'text-red-500' : 'text-slate-500' }}">
                    {{ $overdueCount > 0 ? 'Action required immediately' : 'No overdue items' }}
                </p>
            </div>

            <x-dashboard.stat-card 
                title="Total Borrowed" 
                :value="$totalBorrowed" 
                subtext="All-time books borrowed" 
            />

            <div class="bg-white rounded-2xl border {{ $unpaidFines > 0 ? 'border-amber-300' : 'border-slate-200' }} shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] px-6 py-5 flex flex-col justify-center h-[120px]">
                <h3 class="text-[11px] font-bold tracking-[0.05em] uppercase mb-1 {{ $unpaidFines > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                    Outstanding Fines
                </h3>
                <div class="text-[27px] font-bold leading-tight mb-1 {{ $unpaidFines > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                    ₱{{ number_format($unpaidFines, 2) }}
                </div>
                <p class="text-xs font-bold {{ $unpaidFines > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                    {{ $unpaidFines > 0 ? 'Please settle at circulation' : 'Zero balance' }}
                </p>
            </div>
        </div>

        <!-- Bottom Row: Currently Borrowed Books & Information Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.15fr)_minmax(330px,1fr)] gap-5 items-start">
            
            <!-- Left Main Column: Currently Borrowed Books -->
            <div class="space-y-5">
                
                <!-- Active Loans Table Card -->
                <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                    <div class="px-6 py-5 sm:px-7 sm:py-6 flex justify-between items-center border-b border-[#E2E8F0]">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-bold text-slate-900">My Active Loans</h2>
                                <span class="bg-[#EFF6FF] text-[#102B70] text-xs font-bold px-2 py-0.5 rounded-full">
                                    {{ $activeCount }} / 3
                                </span>
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Books currently in your possession and their due dates</p>
                        </div>
                        <a href="{{ route('student.borrow') }}" wire:navigate class="px-3 py-2 bg-blue-50 text-[#102B70] hover:bg-blue-100 transition-colors rounded-lg text-xs font-bold">
                            View All Records &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Book Details</th>
                                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Accession</th>
                                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Date Issued</th>
                                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Due Date</th>
                                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Status</th>
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
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-13 rounded-md bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                                                    @if(!empty($loan->book?->bookDetail?->cover_image))
                                                        <img src="{{ $loan->book->bookDetail->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-slate-900 line-clamp-1" title="{{ $bookData->book_title ?? 'Untitled Book' }}">
                                                        {{ $bookData->book_title ?? 'Untitled Book' }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 truncate mt-0.5">
                                                        {{ $authors }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 sm:px-7 py-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                            {{ $loan->book?->accession_number ?? '—' }}
                                        </td>
                                        <td class="px-6 sm:px-7 py-4 text-xs text-slate-600 whitespace-nowrap">
                                            {{ $loan->issued_date ? $loan->issued_date->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-6 sm:px-7 py-4 text-xs font-bold whitespace-nowrap {{ $isOverdue ? 'text-red-600' : 'text-slate-900' }}">
                                            {{ $loan->due_date ? $loan->due_date->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="px-6 sm:px-7 py-4 text-right whitespace-nowrap">
                                            @if ($isOverdue)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                                    Overdue by {{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'day' : 'days' }}
                                                </span>
                                            @elseif ($daysRemaining === 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Due Today
                                                </span>
                                            @elseif ($daysRemaining <= 2)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Due in {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'day' : 'days' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Due in {{ $daysRemaining }} days
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="max-w-xs mx-auto flex flex-col items-center">
                                                <p class="text-sm font-bold text-slate-800">No active book loans</p>
                                                <p class="text-xs text-slate-500 mt-1 mb-4">You currently don't have any books checked out from the library.</p>
                                                <a href="{{ route('opac.index') }}" class="px-4 py-2 bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold rounded-xl transition-colors shadow-xs">
                                                    Browse Library Catalog
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Returned Books Table Card -->
                @if($recentTransactions->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] overflow-hidden">
                        <div class="px-6 py-4 sm:px-7 flex justify-between items-center border-b border-[#E2E8F0] bg-slate-50/50">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Recently Returned Books</h3>
                                <p class="text-[11px] text-slate-500">Your past circulation transactions</p>
                            </div>
                            <a href="{{ route('student.borrow') }}" wire:navigate class="text-xs font-bold text-[#102B70] hover:underline">
                                View Full History &rarr;
                            </a>
                        </div>
                        <div class="divide-y divide-[#E2E8F0]">
                            @foreach($recentTransactions as $tx)
                                @php
                                    $bookData = $tx->book?->bookDetail?->bookData;
                                @endphp
                                <div class="px-6 py-3 sm:px-7 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">

                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate" title="{{ $bookData->book_title ?? 'Book' }}">
                                                {{ $bookData->book_title ?? 'Untitled Book' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500">
                                                Borrowed {{ $tx->issued_date ? $tx->issued_date->format('M d, Y') : 'N/A' }} • Returned {{ $tx->return_date ? $tx->return_date->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded shrink-0">
                                        {{ $tx->returnCondition?->condition ?? 'Returned' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column: Guidelines & Library Resources -->
            <div class="space-y-5">
                
                <!-- Library Borrowing Guidelines Card -->
                <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h3 class="text-base font-bold text-slate-900">Borrowing Guidelines</h3>
                        <a href="{{ route('terms') }}" wire:navigate class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] hover:underline inline-flex items-center gap-1">
                            <span>Full Terms</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>

                    <ul class="space-y-3 text-sm text-slate-600 mt-4">
                        <li class="flex items-start gap-2">
                            <span class="text-[#FFC61A] font-bold text-base leading-none">•</span>
                            <span><strong>Loan Period:</strong> General circulation books may be borrowed for up to <strong>{{ config('settings.library_config.loan_period', 5) }} days</strong>.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#FFC61A] font-bold text-base leading-none">•</span>
                            <span><strong>Renewal:</strong> Books may be renewed once if there are no pending reservations from other patrons.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#FFC61A] font-bold text-base leading-none">•</span>
                            <span><strong>Overdue Fines:</strong> Standard late returns incur a fee of <strong>₱{{ number_format(config('settings.library_config.penalty_fee', 50), 2) }} per day</strong> based on college library regulations.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#FFC61A] font-bold text-base leading-none">•</span>
                            <span><strong>Book Care:</strong> Please keep books clean and report any pre-existing damage when checking out.</span>
                        </li>
                    </ul>
                </div>

                <!-- Library Help & Operating Info -->
                <div class="bg-gradient-to-br from-[#102B70] to-[#0B225E] rounded-2xl p-5 sm:p-6 text-white shadow-sm">
                    <h3 class="text-sm font-bold text-white mb-2">Need Library Assistance?</h3>
                    <p class="text-xs text-blue-200 leading-relaxed mb-4">
                        Visit the Circulation Desk during school hours or contact our librarians for book reservations, inquiries, and clearances.
                    </p>
                    
                    <div class="space-y-2 pt-2 border-t border-white/10 text-xs text-blue-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#FFC61A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ config('settings.library_config.operating_days', 'Mon - Fri') }}: {{ config('settings.library_config.desk_hours', '8:00 AM – 5:00 PM') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#FFC61A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>{{ config('settings.library_config.location') ?? config('settings.library_location') }}</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

