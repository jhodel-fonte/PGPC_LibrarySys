<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-6 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        <!-- ======================================================== -->
        <!-- 1. PAGE HEADER & QUICK ACTIONS                          -->
        <!-- ======================================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="h-10 w-10 rounded-2xl bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Book Reservations</h1>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Manage pending requests, fulfill pickups, and track student book holds.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="h-11 px-5 sm:px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
                >
                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>New Reservation</span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. STATS SUMMARY CARDS                                   -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Reservations -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'all')"
                class="text-left rounded-2xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'all' ? 'border-[#102B70] ring-2 ring-[#102B70]/10 bg-blue-50/20' : 'border-[#E2E8F0]' }}"
            >
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Total</span>
                    <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#102B70]">{{ number_format($stats['total']) }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">All catalog requests</div>
            </button>

            <!-- Pending Review -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'pending')"
                class="text-left rounded-2xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'pending' ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/30' : 'border-[#E2E8F0]' }}"
            >
                <div class="flex items-center justify-between text-amber-600 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Pending Review</span>
                    <div class="h-7 w-7 rounded-lg bg-amber-100/70 flex items-center justify-center text-amber-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-2xl font-extrabold text-amber-700">{{ number_format($stats['pending']) }}</div>
                    @if($stats['pending'] > 0)
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                        </span>
                    @endif
                </div>
                <div class="text-[11px] text-amber-600/80 font-medium mt-1">Awaiting approval</div>
            </button>

            <!-- Approved / Ready for Pickup -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'approved')"
                class="text-left rounded-2xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'approved' ? 'border-blue-400 ring-2 ring-blue-400/20 bg-blue-50/30' : 'border-[#E2E8F0]' }}"
            >
                <div class="flex items-center justify-between text-blue-600 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Ready for Pickup</span>
                    <div class="h-7 w-7 rounded-lg bg-blue-100/70 flex items-center justify-center text-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-blue-700">{{ number_format($stats['approved']) }}</div>
                <div class="text-[11px] text-blue-600/80 font-medium mt-1">Reserved at desk</div>
            </button>

            <!-- Fulfilled -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'fulfilled')"
                class="text-left rounded-2xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'fulfilled' ? 'border-emerald-400 ring-2 ring-emerald-400/20 bg-emerald-50/30' : 'border-[#E2E8F0]' }}"
            >
                <div class="flex items-center justify-between text-emerald-600 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Fulfilled</span>
                    <div class="h-7 w-7 rounded-lg bg-emerald-100/70 flex items-center justify-center text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-700">{{ number_format($stats['fulfilled']) }}</div>
                <div class="text-[11px] text-emerald-600/80 font-medium mt-1">Checked out to borrower</div>
            </button>

            <!-- Cancelled / Expired -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'cancelled')"
                class="text-left rounded-2xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm col-span-2 lg:col-span-1 {{ in_array($statusFilter, ['cancelled', 'expired']) ? 'border-slate-400 ring-2 ring-slate-400/20 bg-slate-50' : 'border-[#E2E8F0]' }}"
            >
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider">Cancelled / Expired</span>
                    <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-700">{{ number_format($stats['cancelled']) }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">Released copies</div>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- 3. SEARCH & FILTER TOOLBAR                              -->
        <!-- ======================================================== -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                <!-- Search Input: 56px height, 16px radius, left icon per design system -->
                <div class="relative flex-1 max-w-xl">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by student name, ID number, book title, accession number..."
                        class="h-14 w-full rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] pl-11 pr-4 text-sm font-medium text-[#0F172A] placeholder-slate-400 transition-all focus:border-[#102B70] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#EFF6FF]"
                    >
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    @endif
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Status Filter Dropdown -->
                    <select
                        wire:model.live="statusFilter"
                        class="h-11 rounded-2xl border border-[#CBD5E1] bg-white px-3.5 text-xs sm:text-sm font-bold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                    >
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                    </select>

                    <!-- Time Filter Dropdown -->
                    <select
                        wire:model.live="timeFilter"
                        class="h-11 rounded-2xl border border-[#CBD5E1] bg-white px-3.5 text-xs sm:text-sm font-bold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
                    >
                        <option value="all">Any Date</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                    </select>

                    @if($search !== '' || $statusFilter !== 'all' || $timeFilter !== 'all')
                        <button
                            type="button"
                            wire:click="$set('search', ''); $set('statusFilter', 'all'); $set('timeFilter', 'all');"
                            class="h-11 px-3.5 rounded-2xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs sm:text-sm font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Reset Filters</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 4. RESERVATIONS DATA TABLE                              -->
        <!-- ======================================================== -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 sm:px-6">Reservation ID</th>
                            <th class="py-3.5 px-4 sm:px-6">Borrower / Student</th>
                            <th class="py-3.5 px-4 sm:px-6">Requested Book</th>
                            <th class="py-3.5 px-4 sm:px-6 cursor-pointer select-none group" wire:click="sortBy('reservation_date')">
                                <div class="flex items-center gap-1.5">
                                    <span>Date Reserved</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
                                </div>
                            </th>
                            <th class="py-3.5 px-4 sm:px-6 cursor-pointer select-none group" wire:click="sortBy('due_date')">
                                <div class="flex items-center gap-1.5">
                                    <span>Pickup Deadline</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
                                </div>
                            </th>
                            <th class="py-3.5 px-4 sm:px-6">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-sm font-medium text-slate-700">
                        @forelse($reservations as $reservation)
                            @php
                                $statusId = (int) $reservation->reservation_status_id;
                                $statusName = $reservation->reservationStatus?->status ?? 'Pending';
                                $student = $reservation->student;
                                $book = $reservation->book;
                                $bookDetail = $book?->bookDetail;
                                $bookData = $bookDetail?->bookData;
                                $authors = $bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';

                                $dueDate = $reservation->due_date ? Carbon::parse($reservation->due_date) : null;
                                $isOverdue = $dueDate && $dueDate->isPast() && in_array($statusId, [1, 2]);

                                $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $statusId === 1 ? 'bg-amber-50/15' : '' }}">
                                <!-- Reservation ID -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <span class="font-mono text-xs font-bold text-[#102B70]">#RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <!-- Student Details -->
                                <td class="py-4 px-4 sm:px-6">
                                    @if($student)
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] border border-[#DBEAFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ substr($student->first_name ?? 'S', 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a
                                                    href="{{ route($routePrefix . 'user-details', \App\Models\Account::encodeId($student->account_id ?? $student->id)) }}"
                                                    wire:navigate
                                                    class="font-bold text-[#102B70] hover:underline block truncate"
                                                >
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </a>
                                                <span class="text-xs text-slate-500 font-mono block">{{ $student->school_id_number ?: 'No ID' }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Guest / External request</span>
                                    @endif
                                </td>

                                <!-- Requested Book -->
                                <td class="py-4 px-4 sm:px-6 max-w-xs">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $bookData?->book_title ?? 'Book' }}"
                                            class="h-12 w-9 rounded object-cover shadow-xs border border-slate-200 shrink-0"
                                            onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                                        >
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate" title="{{ $bookData?->book_title ?? 'Untitled Book' }}">
                                                {{ $bookData?->book_title ?? 'Untitled Book' }}
                                            </div>
                                            <div class="text-xs text-slate-500 truncate">{{ $authors }}</div>
                                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                                Acc: {{ $book?->accession_number ?: 'N/A' }} | Call: {{ $bookDetail?->call_number ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Reservation Date -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-800">
                                        {{ $reservation->reservation_date ? Carbon::parse($reservation->reservation_date)->format('M d, Y') : '—' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $reservation->reservation_date ? Carbon::parse($reservation->reservation_date)->format('h:i A') : '' }}
                                    </div>
                                </td>

                                <!-- Pickup Deadline -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <div class="text-xs font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $dueDate ? $dueDate->format('M d, Y') : '—' }}
                                    </div>
                                    <div class="text-[11px] {{ $isOverdue ? 'text-rose-500 font-bold' : 'text-slate-400' }}">
                                        @if($isOverdue)
                                            Expired ({{ $dueDate->diffForHumans() }})
                                        @elseif($dueDate && in_array($statusId, [1, 2]))
                                            {{ $dueDate->diffForHumans() }}
                                        @elseif($statusId === 3)
                                            Fulfilled
                                        @elseif(in_array($statusId, [4, 5]))
                                            Released
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    @if($statusId === 1)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    @elseif($statusId === 2)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                            Ready for Pickup
                                        </span>
                                    @elseif($statusId === 3)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                            Fulfilled
                                        </span>
                                    @elseif($statusId === 4)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Cancelled
                                        </span>
                                    @elseif($statusId === 5)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                            {{ $statusName }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View Details Button -->
                                        <button
                                            type="button"
                                            wire:click="viewDetails({{ $reservation->id }})"
                                            title="View reservation details"
                                            class="h-8 w-8 rounded-lg border border-[#CBD5E1] bg-white hover:bg-slate-100 text-slate-600 flex items-center justify-center transition-all cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>

                                        @if($statusId === 1)
                                            <!-- Approve Action -->
                                            <button
                                                type="button"
                                                wire:click="approveReservation({{ $reservation->id }})"
                                                title="Approve this reservation"
                                                class="h-8 px-2.5 rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs"
                                            >
                                                <svg class="w-3.5 h-3.5 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                <span>Approve</span>
                                            </button>
                                        @endif

                                        @if($statusId === 2)
                                            <!-- Fulfill Action (Checkout) -->
                                            <button
                                                type="button"
                                                wire:click="fulfillReservation({{ $reservation->id }})"
                                                title="Fulfill and check out copy to student"
                                                class="h-8 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span>Fulfill</span>
                                            </button>

                                            <!-- Extend Pickup Deadline -->
                                            <button
                                                type="button"
                                                wire:click="extendPickup({{ $reservation->id }})"
                                                title="Extend pickup window by 3 days"
                                                class="h-8 px-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span>+3 Days</span>
                                            </button>
                                        @endif

                                        @if(in_array($statusId, [1, 2]))
                                            <!-- Cancel Action -->
                                            <button
                                                type="button"
                                                wire:click="openCancelModal({{ $reservation->id }})"
                                                title="Cancel reservation"
                                                class="h-8 px-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold flex items-center transition-all cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center">
                                    <div class="mx-auto max-w-sm space-y-3">
                                        <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                        </div>
                                        <h3 class="text-base font-bold text-[#102B70]">No reservations found</h3>
                                        <p class="text-xs text-slate-500">There are no reservations matching your current search or status filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($reservations->hasPages())
                <div class="border-t border-[#E2E8F0] px-4 sm:px-6 py-4 bg-[#F8FAFC]">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 5. DETAILS MODAL                                         -->
    <!-- ======================================================== -->
    @if($showDetailModal && $this->selectedReservation)
        @php
            $res = $this->selectedReservation;
            $resStudent = $res->student;
            $resBook = $res->book;
            $resDetail = $resBook?->bookDetail;
            $resData = $resDetail?->bookData;
            $resAuthors = $resData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
            $resCover = $resDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-2xl rounded-2xl border border-[#E2E8F0] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-[#F8FAFC]">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#102B70]">Reservation Details</h3>
                            <p class="text-xs text-slate-500 font-mono">Reference #RES-{{ str_pad($res->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Book Information Card -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex gap-4">
                        <img
                            src="{{ $resCover }}"
                            alt="{{ $resData?->book_title ?? 'Book' }}"
                            class="h-24 w-18 rounded-lg object-cover shadow-xs border border-slate-200 shrink-0"
                            onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                        >
                        <div class="min-w-0 space-y-1">
                            <h4 class="text-sm font-bold text-[#0F172A]">{{ $resData?->book_title ?? 'Untitled Book' }}</h4>
                            <p class="text-xs text-slate-600 font-medium">By {{ $resAuthors }}</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-500 pt-1">
                                <div><span class="font-semibold text-slate-700">Accession No:</span> {{ $resBook?->accession_number ?: 'N/A' }}</div>
                                <div><span class="font-semibold text-slate-700">Call No:</span> {{ $resDetail?->call_number ?: 'N/A' }}</div>
                                <div><span class="font-semibold text-slate-700">ISBN:</span> {{ $resDetail?->isbn ?: 'N/A' }}</div>
                                <div><span class="font-semibold text-slate-700">Shelf Location:</span> {{ $resBook?->location ?: 'Main Library' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Borrower Info & Reservation Timeline -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Borrower Info -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-white space-y-2">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500">Student Information</h5>
                            @if($resStudent)
                                <div class="text-sm font-bold text-[#102B70]">{{ $resStudent->first_name }} {{ $resStudent->last_name }}</div>
                                <div class="text-xs text-slate-600 space-y-0.5">
                                    <div><span class="font-semibold">Student ID:</span> {{ $resStudent->school_id_number ?: 'Not specified' }}</div>
                                    <div><span class="font-semibold">Email:</span> {{ $resStudent->account?->email ?: 'N/A' }}</div>
                                    <div><span class="font-semibold">Program:</span> {{ $resStudent->program ?: 'N/A' }}</div>
                                    <div><span class="font-semibold">Contact:</span> {{ $resStudent->contact_num ?: 'N/A' }}</div>
                                </div>
                            @else
                                <p class="text-xs text-slate-400">No student profile associated.</p>
                            @endif
                        </div>

                        <!-- Timeline & Dates -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-white space-y-2">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500">Timeline & Dates</h5>
                            <div class="text-xs text-slate-600 space-y-1">
                                <div><span class="font-semibold">Reserved On:</span> {{ $res->reservation_date ? Carbon::parse($res->reservation_date)->format('M d, Y h:i A') : 'N/A' }}</div>
                                <div><span class="font-semibold">Pickup Deadline:</span> <span class="font-bold text-[#102B70]">{{ $res->due_date ? Carbon::parse($res->due_date)->format('M d, Y') : 'N/A' }}</span></div>
                                <div><span class="font-semibold">Approved Date:</span> {{ $res->approved_date ? Carbon::parse($res->approved_date)->format('M d, Y h:i A') : 'Pending' }}</div>
                                <div><span class="font-semibold">Fulfilled Date:</span> {{ $res->fulfilled_date ? Carbon::parse($res->fulfilled_date)->format('M d, Y h:i A') : 'Not yet' }}</div>
                                <div><span class="font-semibold">Current Status:</span> <span class="font-bold text-[#102B70]">{{ $res->reservationStatus?->status ?? 'Pending' }}</span></div>
                            </div>
                        </div>
                    </div>

                    @if($res->comment)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                            <span class="font-bold text-slate-700">Staff & System Comments:</span>
                            <p class="text-slate-600 mt-0.5">{{ $res->comment }}</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-[#F8FAFC]">
                    <button
                        type="button"
                        wire:click="closeDetailModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all cursor-pointer"
                    >
                        Close
                    </button>

                    @if($res->reservation_status_id === 1)
                        <button
                            type="button"
                            wire:click="approveReservation({{ $res->id }}); closeDetailModal();"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            <span>Approve Request</span>
                        </button>
                    @elseif($res->reservation_status_id === 2)
                        <button
                            type="button"
                            wire:click="fulfillReservation({{ $res->id }}); closeDetailModal();"
                            class="h-10 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Fulfill Pickup</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 6. CANCEL MODAL                                          -->
    <!-- ======================================================== -->
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-md rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="h-10 w-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Cancel Reservation</h3>
                        <p class="text-xs text-slate-500">The reserved copy will be released back to available inventory.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#334155]">Reason for cancellation (optional)</label>
                    <textarea
                        wire:model="cancelReason"
                        rows="3"
                        placeholder="e.g. Student requested cancellation, copy unavailable, pickup deadline expired..."
                        class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeCancelModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all cursor-pointer"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        wire:click="confirmCancelReservation"
                        class="h-10 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                    >
                        Confirm Cancellation
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 7. NEW MANUAL RESERVATION MODAL                         -->
    <!-- ======================================================== -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overflow-y-auto" role="dialog" aria-modal="true">
            <div class="my-auto w-full max-w-xl rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-[#102B70]">Create New Reservation</h3>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Step 1: Select Student -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Select Student <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="createSearchStudent"
                            placeholder="Type student name or ID number..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3.5 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @error('createSelectedStudentId') <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p> @enderror

                        @if($this->searchedStudents->isNotEmpty() && !$createSelectedStudentId)
                            <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden divide-y divide-slate-100 max-h-40 overflow-y-auto">
                                @foreach($this->searchedStudents as $st)
                                    <button
                                        type="button"
                                        wire:click="selectStudent({{ $st->id }})"
                                        class="w-full text-left p-2.5 hover:bg-blue-50/50 flex items-center justify-between text-xs cursor-pointer transition-colors"
                                    >
                                        <span class="font-bold text-slate-800">{{ $st->first_name }} {{ $st->last_name }}</span>
                                        <span class="font-mono text-slate-500 text-[11px]">{{ $st->school_id_number }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($createSelectedStudentId)
                            @php $selectedSt = \App\Models\Student::find($createSelectedStudentId); @endphp
                            @if($selectedSt)
                                <div class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/50 flex items-center justify-between text-xs">
                                    <span class="font-bold text-[#102B70]">Selected: {{ $selectedSt->first_name }} {{ $selectedSt->last_name }} ({{ $selectedSt->school_id_number }})</span>
                                    <button type="button" wire:click="$set('createSelectedStudentId', null)" class="text-slate-400 hover:text-rose-600 font-bold">Remove</button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Step 2: Select Book Copy -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-[#334155]">Select Available Book <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="createSearchBook"
                            placeholder="Type book title or accession number..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3.5 text-xs text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @error('createSelectedBookId') <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p> @enderror

                        @if($this->searchedBooks->isNotEmpty() && !$createSelectedBookId)
                            <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden divide-y divide-slate-100 max-h-40 overflow-y-auto">
                                @foreach($this->searchedBooks as $bk)
                                    <button
                                        type="button"
                                        wire:click="selectBook({{ $bk->id }})"
                                        class="w-full text-left p-2.5 hover:bg-blue-50/50 flex items-center justify-between text-xs cursor-pointer transition-colors"
                                    >
                                        <div class="min-w-0 pr-2">
                                            <div class="font-bold text-slate-800 truncate">{{ $bk->bookDetail?->bookData?->book_title ?? 'Book' }}</div>
                                            <div class="text-[11px] font-mono text-slate-500">Acc: {{ $bk->accession_number }}</div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">Available</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($createSelectedBookId)
                            @php $selectedBk = \App\Models\Book::with('bookDetail.bookData')->find($createSelectedBookId); @endphp
                            @if($selectedBk)
                                <div class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/50 flex items-center justify-between text-xs">
                                    <span class="font-bold text-[#102B70]">Selected: {{ $selectedBk->bookDetail?->bookData?->book_title }} (Acc: {{ $selectedBk->accession_number }})</span>
                                    <button type="button" wire:click="$set('createSelectedBookId', null)" class="text-slate-400 hover:text-rose-600 font-bold">Remove</button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Pickup Days & Notes -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Hold Window (Days)</label>
                            <input
                                type="number"
                                min="1"
                                max="14"
                                wire:model="createPickupDays"
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-[#334155]">Internal Notes</label>
                            <input
                                type="text"
                                wire:model="createNotes"
                                placeholder="Optional remarks..."
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-xs text-slate-800 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="submitManualReservation"
                        class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Place Reservation</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
