<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-4 py-5 sm:px-6 lg:px-8 lg:py-6">

        <!-- ======================================================== -->
        <!-- 1. PAGE HEADER & QUICK ACTIONS                          -->
        <!-- ======================================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight text-[#102B70]">Book Reservations</h1>
                        <p class="mt-1 text-base text-slate-600">Manage pending requests, fulfill pickups, and track student book holds.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="h-11 px-5 sm:px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-semibold  transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
                >
                    <span>New Reservation</span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. STATS SUMMARY CARDS                                   -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Reservations -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'all')"
                class="text-left rounded-xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'all' ? 'border-[#102B70] bg-[#F8FAFC]' : 'border-[#E2E8F0]' }}"
            >
                <div class="mb-2 text-slate-600">
                    <span class="text-base font-semibold uppercase tracking-wider">Total</span>
                </div>
                <div class="text-2xl font-extrabold text-[#102B70]">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-slate-500 mt-1">All catalog requests</div>
            </button>

            <!-- Pending Review -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'pending')"
                class="text-left rounded-xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'pending' ? 'border-[#102B70] bg-[#F8FAFC]' : 'border-[#E2E8F0]' }}"
            >
                <div class="mb-2 text-slate-600">
                    <span class="text-base font-semibold uppercase tracking-wider">Pending Review</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-2xl font-extrabold text-[#102B70]">{{ number_format($stats['pending']) }}</div>

                </div>
                <div class="text-sm text-slate-500 font-medium mt-1">Awaiting approval</div>
            </button>

            <!-- Approved / Ready for Pickup -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'approved')"
                class="text-left rounded-xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'approved' ? 'border-[#102B70] bg-[#F8FAFC]' : 'border-[#E2E8F0]' }}"
            >
                <div class="mb-2 text-slate-600">
                    <span class="text-base font-semibold uppercase tracking-wider">Ready for Pickup</span>
                </div>
                <div class="text-2xl font-extrabold text-[#102B70]">{{ number_format($stats['approved']) }}</div>
                <div class="text-sm text-slate-500 font-medium mt-1">Reserved at desk</div>
            </button>

            <!-- Fulfilled -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'fulfilled')"
                class="text-left rounded-xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm {{ $statusFilter === 'fulfilled' ? 'border-[#102B70] bg-[#F8FAFC]' : 'border-[#E2E8F0]' }}"
            >
                <div class="mb-2 text-slate-600">
                    <span class="text-base font-semibold uppercase tracking-wider">Fulfilled</span>
                </div>
                <div class="text-2xl font-extrabold text-[#102B70]">{{ number_format($stats['fulfilled']) }}</div>
                <div class="text-sm text-slate-500 font-medium mt-1">Checked out to borrower</div>
            </button>

            <!-- Cancelled / Expired -->
            <button
                type="button"
                wire:click="$set('statusFilter', 'cancelled')"
                class="text-left rounded-xl border p-4 bg-white transition-all cursor-pointer hover:shadow-sm  {{ in_array($statusFilter, ['cancelled', 'expired']) ? 'border-[#102B70] bg-slate-50' : 'border-[#E2E8F0]' }}"
            >
                <div class="mb-2 text-slate-600">
                    <span class="text-base font-semibold uppercase tracking-wider">Cancelled / Expired</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-700">{{ number_format($stats['cancelled']) }}</div>
                <div class="text-sm text-slate-500 mt-1">Released copies</div>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- 3. SEARCH & FILTER TOOLBAR                              -->
        <!-- ======================================================== -->
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-4  space-y-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <!-- Search Input: 56px height, 16px radius, left icon per design system -->
                <div class="relative min-w-0 flex-1 md:max-w-xl">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by student name, ID number, book title, accession number..."
                        class="h-11 w-full rounded-xl border border-[#CBD5E1] bg-white px-3 pr-16 text-base text-[#0F172A] placeholder-slate-400 transition-colors focus:border-[#102B70] focus:outline-none focus:ring-4 focus:ring-[#EFF6FF]"
                    >
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 px-3 text-base font-medium text-slate-600 hover:text-[#102B70]"
                        >
                        Clear</button>
                    @endif
                </div>

                <!-- Filters -->
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    <!-- Status Filter Dropdown -->
                    <select
                        wire:model.live="statusFilter"
                        class="h-11 rounded-xl border border-[#CBD5E1] bg-white px-3.5 text-base font-semibold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
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
                        class="h-11 rounded-xl border border-[#CBD5E1] bg-white px-3.5 text-base font-semibold text-[#334155] transition-all hover:border-[#102B70] focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-[#EFF6FF] cursor-pointer"
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
                            class="h-11 px-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-base font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
                        >
                            <span>Reset Filters</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 4. RESERVATIONS DATA TABLE                              -->
        <!-- ======================================================== -->
        <div class="rounded-xl border border-[#E2E8F0] bg-white  overflow-hidden">
            <div class="overflow-x-auto overscroll-x-contain">
                <table class="min-w-[1040px] w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC] text-sm font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4 sm:px-6">Reservation ID</th>
                            <th class="py-3.5 px-4 sm:px-6">Borrower / Student</th>
                            <th class="py-3.5 px-4 sm:px-6">Requested Book</th>
                            <th class="py-3.5 px-4 sm:px-6 cursor-pointer select-none group" wire:click="sortBy('reservation_date')">
                                <div class="flex items-center">
                                    <span>Date Reserved</span>
                                </div>
                            </th>
                            <th class="py-3.5 px-4 sm:px-6 cursor-pointer select-none group" wire:click="sortBy('due_date')">
                                <div class="flex items-center">
                                    <span>Pickup Deadline</span>
                                </div>
                            </th>
                            <th class="py-3.5 px-4 sm:px-6">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-base text-slate-700">
                        @forelse($reservations as $reservation)
                            @php
                                $statusId = (int) $reservation->reservation_status_id;
                                $statusName = $reservation->reservationStatus?->status ?? 'Pending';
                                $student = $reservation->student;
                                $book = $reservation->book;
                                $bookDetail = $book?->bookDetail;
                                $bookData = $bookDetail?->bookData;
                                $authors = $bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';

                                $dueDate = $reservation->due_date ? \Carbon\Carbon::parse($reservation->due_date) : null;
                                $isOverdue = $dueDate && $dueDate->isPast() && in_array($statusId, [1, 2]);

                                $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $statusId === 1 ? 'bg-transparent' : '' }}">
                                <!-- Reservation ID -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <span class="font-mono text-base font-semibold text-[#102B70]">#RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>

                                <!-- Student Details -->
                                <td class="py-4 px-4 sm:px-6">
                                    @if($student)
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] border border-[#DBEAFE] text-[#102B70] font-bold text-sm flex items-center justify-center shrink-0">
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
                                                <span class="text-base text-slate-500 font-mono block">{{ $student->school_id_number ?: 'No ID' }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-base text-slate-500 italic">Guest / External request</span>
                                    @endif
                                </td>

                                <!-- Requested Book -->
                                <td class="py-4 px-4 sm:px-6 max-w-xs">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $bookData?->book_title ?? 'Book' }}"
                                            class="h-12 w-9 rounded object-cover  border border-slate-200 shrink-0"
                                            onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                                        >
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 truncate" title="{{ $bookData?->book_title ?? 'Untitled Book' }}">
                                                {{ $bookData?->book_title ?? 'Untitled Book' }}
                                            </div>
                                            <div class="text-base text-slate-500 truncate">{{ $authors }}</div>
                                            <div class="text-sm font-mono text-slate-500 mt-0.5">
                                                Acc: {{ $book?->accession_number ?: 'N/A' }} | Call: {{ $bookDetail?->call_number ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Reservation Date -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <div class="text-base font-medium text-slate-800">
                                        {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') : '—' }}
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('h:i A') : '' }}
                                    </div>
                                </td>

                                <!-- Pickup Deadline -->
                                <td class="py-4 px-4 sm:px-6 whitespace-nowrap">
                                    <div class="text-base font-semibold {{ $isOverdue ? 'text-slate-700' : 'text-slate-800' }}">
                                        {{ $dueDate ? $dueDate->format('M d, Y') : '—' }}
                                    </div>
                                    <div class="text-sm {{ $isOverdue ? 'text-rose-500 font-bold' : 'text-slate-500' }}">
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
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-base font-semibold bg-slate-100 text-slate-700 border border-slate-200">

                                            Pending Review
                                        </span>
                                    @elseif($statusId === 2)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-base font-semibold bg-slate-100 text-slate-700 border border-slate-200">

                                            Ready for Pickup
                                        </span>
                                    @elseif($statusId === 3)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-base font-semibold bg-slate-100 text-slate-700 border border-slate-200">

                                            Fulfilled
                                        </span>
                                    @elseif($statusId === 4)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-base font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Cancelled
                                        </span>
                                    @elseif($statusId === 5)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-base font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold bg-slate-100 text-slate-700">
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
                                            aria-label="View reservation details"
                                            class="h-8 rounded-lg border border-[#CBD5E1] bg-white px-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                        >View</button>

                                        @if($statusId === 1)
                                            <!-- Approve Action -->
                                            <button
                                                type="button"
                                                wire:click="approveReservation({{ $reservation->id }})"
                                                title="Approve this reservation"
                                                class="h-8 rounded-lg bg-[#102B70] px-2.5 text-sm font-medium text-white hover:bg-[#0B225E]"
                                            >
                                                Approve
                                            </button>
                                        @endif

                                        @if($statusId === 2)
                                            <!-- Fulfill Action (Checkout) -->
                                            <button
                                                type="button"
                                                wire:click="fulfillReservation({{ $reservation->id }})"
                                                title="Fulfill and check out copy to student"
                                                class="h-8 rounded-lg bg-[#102B70] px-2.5 text-sm font-medium text-white hover:bg-[#0B225E]"
                                            >
                                                Fulfill
                                            </button>

                                            <!-- Extend Pickup Deadline -->
                                            <button
                                                type="button"
                                                wire:click="extendPickup({{ $reservation->id }})"
                                                title="Extend pickup window by 3 days"
                                                class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                            >
                                                Extend 3 days
                                            </button>
                                        @endif

                                        @if(in_array($statusId, [1, 2]))
                                            <!-- Cancel Action -->
                                            <button
                                                type="button"
                                                wire:click="openCancelModal({{ $reservation->id }})"
                                                title="Cancel reservation"
                                                class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                            >Cancel</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center">
                                    <div class="mx-auto max-w-sm space-y-3">
                                        <h3 class="text-base font-bold text-[#102B70]">No reservations found</h3>
                                        <p class="text-base text-slate-500">There are no reservations matching your current search or status filter.</p>
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
            <div class="my-auto w-full max-w-2xl rounded-xl border border-[#E2E8F0] bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-[#F8FAFC]">
                    <div>
                        <div>
                            <h3 class="text-base font-bold text-[#102B70]">Reservation Details</h3>
                            <p class="text-base text-slate-500 font-mono">Reference #RES-{{ str_pad($res->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-200 hover:text-slate-700 cursor-pointer">
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Book Information Card -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex gap-4">
                        <img
                            src="{{ $resCover }}"
                            alt="{{ $resData?->book_title ?? 'Book' }}"
                            class="h-24 w-18 rounded-lg object-cover  border border-slate-200 shrink-0"
                            onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                        >
                        <div class="min-w-0 space-y-1">
                            <h4 class="text-sm font-bold text-[#0F172A]">{{ $resData?->book_title ?? 'Untitled Book' }}</h4>
                            <p class="text-base text-slate-600 font-medium">By {{ $resAuthors }}</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-base text-slate-500 pt-1">
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
                            <h5 class="text-base font-semibold uppercase tracking-wider text-slate-500">Student Information</h5>
                            @if($resStudent)
                                <div class="text-sm font-bold text-[#102B70]">{{ $resStudent->first_name }} {{ $resStudent->last_name }}</div>
                                <div class="text-base text-slate-600 space-y-0.5">
                                    <div><span class="font-semibold">Student ID:</span> {{ $resStudent->school_id_number ?: 'Not specified' }}</div>
                                    <div><span class="font-semibold">Email:</span> {{ $resStudent->account?->email ?: 'N/A' }}</div>
                                    <div><span class="font-semibold">Program:</span> {{ $resStudent->program ?: 'N/A' }}</div>
                                    <div><span class="font-semibold">Contact:</span> {{ $resStudent->contact_num ?: 'N/A' }}</div>
                                </div>
                            @else
                                <p class="text-base text-slate-500">No student profile associated.</p>
                            @endif
                        </div>

                        <!-- Timeline & Dates -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-white space-y-2">
                            <h5 class="text-base font-semibold uppercase tracking-wider text-slate-500">Timeline & Dates</h5>
                            <div class="text-base text-slate-600 space-y-1">
                                <div><span class="font-semibold">Reserved On:</span> {{ $res->reservation_date ? \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y h:i A') : 'N/A' }}</div>
                                <div><span class="font-semibold">Pickup Deadline:</span> <span class="font-bold text-[#102B70]">{{ $res->due_date ? \Carbon\Carbon::parse($res->due_date)->format('M d, Y') : 'N/A' }}</span></div>
                                <div><span class="font-semibold">Approved Date:</span> {{ $res->approved_date ? \Carbon\Carbon::parse($res->approved_date)->format('M d, Y h:i A') : 'Pending' }}</div>
                                <div><span class="font-semibold">Fulfilled Date:</span> {{ $res->fulfilled_date ? \Carbon\Carbon::parse($res->fulfilled_date)->format('M d, Y h:i A') : 'Not yet' }}</div>
                                <div><span class="font-semibold">Current Status:</span> <span class="font-bold text-[#102B70]">{{ $res->reservationStatus?->status ?? 'Pending' }}</span></div>
                            </div>
                        </div>
                    </div>

                    @if($res->comment)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-base">
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
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-base font-semibold transition-all cursor-pointer"
                    >
                        Close
                    </button>

                    @if($res->reservation_status_id === 1)
                        <button
                            type="button"
                            wire:click="approveReservation({{ $res->id }}); closeDetailModal();"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-semibold transition-all  cursor-pointer flex items-center gap-1.5"
                        >
                            <span>Approve Request</span>
                        </button>
                    @elseif($res->reservation_status_id === 2)
                        <button
                            type="button"
                            wire:click="fulfillReservation({{ $res->id }}); closeDetailModal();"
                            class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-semibold transition-all  cursor-pointer flex items-center gap-1.5"
                        >
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
            <div class="my-auto w-full max-w-md rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-4">
                <div class="flex items-center gap-3 text-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Cancel Reservation</h3>
                        <p class="text-base text-slate-500">The reserved copy will be released back to available inventory.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-base font-semibold text-[#334155]">Reason for cancellation (optional)</label>
                    <textarea
                        wire:model="cancelReason"
                        rows="3"
                        placeholder="e.g. Student requested cancellation, copy unavailable, pickup deadline expired..."
                        class="w-full rounded-xl border border-slate-300 p-3 text-base text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeCancelModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-base font-semibold transition-all cursor-pointer"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        wire:click="confirmCancelReservation"
                        class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-semibold transition-all  cursor-pointer"
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
            <div class="my-auto w-full max-w-xl rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-[#102B70]">Create New Reservation</h3>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">Close</button>
                </div>

                <div class="space-y-4">
                    <!-- Step 1: Select Student -->
                    <div class="space-y-1.5">
                        <label class="text-base font-semibold text-[#334155]">Select Student <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="createSearchStudent"
                            placeholder="Type student name or ID number..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @error('createSelectedStudentId') <p class="text-sm text-slate-700 font-semibold">{{ $message }}</p> @enderror

                        @if($this->searchedStudents->isNotEmpty() && !$createSelectedStudentId)
                            <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden divide-y divide-slate-100 max-h-40 overflow-y-auto">
                                @foreach($this->searchedStudents as $st)
                                    <button
                                        type="button"
                                        wire:click="selectStudent({{ $st->id }})"
                                        class="w-full text-left p-2.5 hover:bg-[#F8FAFC] flex items-center justify-between text-base cursor-pointer transition-colors"
                                    >
                                        <span class="font-bold text-slate-800">{{ $st->first_name }} {{ $st->last_name }}</span>
                                        <span class="font-mono text-slate-500 text-sm">{{ $st->school_id_number }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($createSelectedStudentId)
                            @php $selectedSt = \App\Models\Student::find($createSelectedStudentId); @endphp
                            @if($selectedSt)
                                <div class="p-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] flex items-center justify-between text-base">
                                    <span class="font-bold text-[#102B70]">Selected: {{ $selectedSt->first_name }} {{ $selectedSt->last_name }} ({{ $selectedSt->school_id_number }})</span>
                                    <button type="button" wire:click="$set('createSelectedStudentId', null)" class="text-slate-500 hover:text-slate-700 font-bold">Remove</button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Step 2: Select Book Copy -->
                    <div class="space-y-1.5">
                        <label class="text-base font-semibold text-[#334155]">Select Available Book <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="createSearchBook"
                            placeholder="Type book title or accession number..."
                            class="h-11 w-full rounded-xl border border-slate-300 px-3.5 text-base text-slate-800 placeholder-slate-400 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                        >
                        @error('createSelectedBookId') <p class="text-sm text-slate-700 font-semibold">{{ $message }}</p> @enderror

                        @if($this->searchedBooks->isNotEmpty() && !$createSelectedBookId)
                            <div class="rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden divide-y divide-slate-100 max-h-40 overflow-y-auto">
                                @foreach($this->searchedBooks as $bk)
                                    <button
                                        type="button"
                                        wire:click="selectBook({{ $bk->id }})"
                                        class="w-full text-left p-2.5 hover:bg-[#F8FAFC] flex items-center justify-between text-base cursor-pointer transition-colors"
                                    >
                                        <div class="min-w-0 pr-2">
                                            <div class="font-bold text-slate-800 truncate">{{ $bk->bookDetail?->bookData?->book_title ?? 'Book' }}</div>
                                            <div class="text-sm font-mono text-slate-500">Acc: {{ $bk->accession_number }}</div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-sm font-bold bg-slate-100 text-slate-700 shrink-0">Available</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($createSelectedBookId)
                            @php $selectedBk = \App\Models\Book::with('bookDetail.bookData')->find($createSelectedBookId); @endphp
                            @if($selectedBk)
                                <div class="p-2.5 rounded-xl border border-[#CBD5E1] bg-[#F8FAFC] flex items-center justify-between text-base">
                                    <span class="font-bold text-[#102B70]">Selected: {{ $selectedBk->bookDetail?->bookData?->book_title }} (Acc: {{ $selectedBk->accession_number }})</span>
                                    <button type="button" wire:click="$set('createSelectedBookId', null)" class="text-slate-500 hover:text-slate-700 font-bold">Remove</button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Pickup Days & Notes -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-base font-semibold text-[#334155]">Hold Window (Days)</label>
                            <input
                                type="number"
                                min="1"
                                max="14"
                                wire:model="createPickupDays"
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-base text-slate-800 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="text-base font-semibold text-[#334155]">Internal Notes</label>
                            <input
                                type="text"
                                wire:model="createNotes"
                                placeholder="Optional remarks..."
                                class="h-10 w-full rounded-xl border border-slate-300 px-3 text-base text-slate-800 focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] focus:outline-none"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="h-10 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-base font-semibold transition-all cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="submitManualReservation"
                        class="h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-semibold transition-all  cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Place Reservation</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
