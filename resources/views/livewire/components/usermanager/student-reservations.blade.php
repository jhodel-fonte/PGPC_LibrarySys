@php
    $reservations = $this->reservations ?? collect();
    $activeReservations = $this->activeReservations ?? collect();
    $pastReservations = $this->pastReservations ?? collect();
@endphp

<div class="space-y-5 text-base">

    <!-- Active Reservations Section -->
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-3">
            <div>
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h3 class="text-lg sm:text-xl font-bold text-[#102B70]">Active Reservations</h3>
                    @if($activeReservations->count() > 0)
                        <span class="text-sm font-semibold text-slate-600">
                            {{ $activeReservations->count() }} active
                        </span>
                    @endif
                </div>
                <p class="text-sm sm:text-base text-slate-500 font-medium mt-0.5">Books currently held or awaiting your collection at the circulation desk.</p>
            </div>

            <button
                type="button"
                wire:click="openReserveModal"
                class="h-11 w-full sm:w-auto px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer active:scale-[0.98] shrink-0"
            >
                <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Reserve a Book</span>
            </button>
        </div>

        @if($activeReservations->isNotEmpty())
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @foreach($activeReservations as $res)
                    @php
                        $rBook = $res->book;
                        $rDetail = $rBook?->bookDetail;
                        $rData = $rDetail?->bookData;
                        $rTitle = $rData?->book_title ?? 'Untitled Book';
                        $rAuthors = $rData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
                        $rCover = $rDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                        $rStatusId = (int) $res->reservation_status_id;
                        $rDue = $res->due_date?->copy()->setTimezone('Asia/Manila');
                        $rIsDueSoon = $rDue && $rDue->diffInDays(now()) <= 1;
                    @endphp
                    <div x-data="{ confirmCancel: false }" class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 flex flex-col gap-4">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <img
                                src="{{ $rCover }}"
                                alt="{{ $rTitle }}"
                                class="h-24 w-16 sm:h-28 sm:w-20 rounded-md object-cover border border-slate-200 shrink-0"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <span class="text-xs font-semibold text-slate-600">
                                        #RES-{{ str_pad($res->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if($rStatusId === 1)
                                        <span class="text-xs font-semibold text-amber-800 flex items-center gap-1.5">

                                            Pending Approval
                                        </span>
                                    @elseif($rStatusId === 2)
                                        <span class="text-xs font-semibold text-blue-800 flex items-center gap-1.5">

                                            Ready for Pickup
                                        </span>
                                    @endif
                                </div>

                                <h4 class="text-base font-bold text-[#0F172A] leading-snug break-words" title="{{ $rTitle }}">
                                    {{ $rTitle }}
                                </h4>
                                <p class="text-sm text-slate-600 leading-relaxed break-words">By {{ $rAuthors }}</p>

                                <div class="pt-1 text-xs text-slate-600 space-y-1 break-words">
                                    <div><span class="font-semibold">Accession:</span> {{ $rBook?->accession_number ?: 'N/A' }}</div>
                                    <div><span class="font-semibold">Location:</span> {{ $rBook?->location ?: 'Main Library' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pickup Window Banner -->
                        <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ $rIsDueSoon ? 'text-amber-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="font-medium text-slate-700">Claim Deadline:</span>
                            </div>
                            <span class="font-bold {{ $rIsDueSoon ? 'text-amber-700 font-extrabold' : 'text-[#102B70]' }}">
                                {{ $rDue ? $rDue->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>

                        <!-- Card Actions -->
                        <div class="flex flex-wrap items-center gap-2 mt-auto">
                            <a
                                href="{{ $isProfile ? route('student.reservations.confirmation', ['reservation' => str_pad((string) $res->id, 5, '0', STR_PAD_LEFT)]) : route(auth()->user()->isAdmin() ? 'admin.reservations' : 'librarian.reservations', ['search' => $res->id]) }}"
                                wire:navigate
                                class="min-h-11 px-4 rounded-lg bg-[#102B70] text-white hover:bg-[#0B225E] text-sm font-semibold inline-flex items-center justify-center gap-2 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>{{ $isProfile ? 'View Slip' : 'View Reservation' }}</span>
                            </a>

                            <button
                                type="button"
                                x-on:click="confirmCancel = true; $nextTick(() => $refs.keepHold.focus())"
                                class="min-h-11 px-4 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 cursor-pointer transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]"
                            >
                                Cancel Hold
                            </button>
                        </div>

                        <div x-show="confirmCancel" x-cloak x-trap.inert.noscroll="confirmCancel"
                            x-on:keydown.escape.window="confirmCancel = false"
                            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-black/60 p-4"
                            role="dialog" aria-modal="true" aria-labelledby="cancel-hold-title-{{ $res->id }}" aria-describedby="cancel-hold-description-{{ $res->id }}">
                            <div class="my-auto w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-2xl">
                                <h3 id="cancel-hold-title-{{ $res->id }}" class="text-base font-bold text-slate-900">Cancel Hold?</h3>
                                <p id="cancel-hold-description-{{ $res->id }}" class="text-sm text-slate-600">Cancel your reservation for <strong>{{ $rTitle }}</strong>? The held copy will be released.</p>
                                <div class="flex flex-wrap justify-end gap-3 pt-2">
                                    <button type="button" x-ref="keepHold" x-on:click="confirmCancel = false"
                                        class="min-h-11 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]">Keep Hold</button>
                                    <button type="button" wire:click="cancelReservation({{ $res->id }})" wire:loading.attr="disabled" wire:target="cancelReservation"
                                        x-on:click="confirmCancel = false"
                                        class="min-h-11 rounded-xl bg-[#102B70] px-5 text-sm font-semibold text-white hover:bg-[#0B225E] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70] disabled:opacity-50">Confirm Cancellation</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center space-y-3">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                </div>
                <h4 class="text-base font-bold text-[#102B70]">No Active Reservations</h4>
                <p class="text-sm sm:text-base text-slate-500 max-w-sm mx-auto">
                    You currently have no pending or awaiting-pickup book holds. You can reserve up to 3 books in advance.
                </p>
                <button
                    type="button"
                    wire:click="openReserveModal"
                    class="h-10 px-5 rounded-xl bg-[#102B70] text-white text-sm font-bold hover:bg-[#0B225E] transition-all cursor-pointer inline-flex items-center gap-2 shadow-xs"
                >
                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Reserve a Book Now</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Past / Historical Reservations -->
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="text-lg sm:text-xl font-bold text-[#102B70]">Reservation History</h3>
            <p class="text-sm sm:text-base text-slate-500 font-medium mt-0.5">Record of fulfilled, cancelled, and expired requests.</p>
        </div>

        @if($pastReservations->isNotEmpty())
            <!-- Mobile Stacked Card View (< 768px) -->
            <div class="block md:hidden divide-y divide-slate-100">
                @foreach($pastReservations as $past)
                    @php
                        $pBook = $past->book;
                        $pDetail = $pBook?->bookDetail;
                        $pData = $pDetail?->bookData;
                        $pStatusId = (int) $past->reservation_status_id;
                        $pStatusName = $past->reservationStatus?->status ?? 'Closed';
                    @endphp
                    <div class="py-3.5 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-[#102B70]">
                                #RES-{{ str_pad($past->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                            @if($pStatusId === 3)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                    Fulfilled
                                </span>
                            @elseif($pStatusId === 4)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                    Cancelled
                                </span>
                            @elseif($pStatusId === 5)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shrink-0">
                                    Expired
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 shrink-0">
                                    {{ $pStatusName }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="font-bold text-[#0F172A] text-sm leading-snug line-clamp-2">
                                {{ $pData?->book_title ?? 'Untitled Book' }}
                            </p>
                            <p class="font-mono text-xs text-slate-500 mt-0.5">
                                Acc: {{ $pBook?->accession_number ?: 'N/A' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                            <div>
                                <span class="text-slate-400 font-medium block">Reserved Date</span>
                                <span class="font-medium text-slate-700">
                                    {{ $past->reservation_date ? \Carbon\Carbon::parse($past->reservation_date)->format('M d, Y') : '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium block">Closed Date</span>
                                <span class="font-medium text-slate-700">
                                    {{ $past->fulfilled_date ? \Carbon\Carbon::parse($past->fulfilled_date)->format('M d, Y') : ($past->cancelled_date ? \Carbon\Carbon::parse($past->cancelled_date)->format('M d, Y') : '—') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop / Tablet Table View (768px+) -->
            <div tabindex="0" role="region" aria-label="Reservation history table" class="hidden md:block overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-7 px-4 sm:px-6 lg:px-7 focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                <table class="w-full text-left border-collapse text-sm sm:text-base">
                    <thead>
                        <tr class="border-b border-[#F1F5F9] text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-3">Slip ID</th>
                            <th scope="col" class="py-3.5 px-4">Book Title</th>
                            <th scope="col" class="py-3.5 px-4">Reserved Date</th>
                            <th scope="col" class="py-3.5 px-4">Closed Date</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Final Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pastReservations as $past)
                            @php
                                $pBook = $past->book;
                                $pDetail = $pBook?->bookDetail;
                                $pData = $pDetail?->bookData;
                                $pStatusId = (int) $past->reservation_status_id;
                                $pStatusName = $past->reservationStatus?->status ?? 'Closed';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-3 font-mono text-sm font-bold text-[#102B70]">
                                    #RES-{{ str_pad($past->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#0F172A]">
                                    <div class="truncate max-w-xs text-base font-bold">{{ $pData?->book_title ?? 'Untitled Book' }}</div>
                                    <div class="font-mono text-xs text-slate-500 font-normal">Acc: {{ $pBook?->accession_number ?: 'N/A' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 text-sm">
                                    {{ $past->reservation_date ? \Carbon\Carbon::parse($past->reservation_date)->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 text-sm">
                                    {{ $past->fulfilled_date ? \Carbon\Carbon::parse($past->fulfilled_date)->format('M d, Y') : ($past->cancelled_date ? \Carbon\Carbon::parse($past->cancelled_date)->format('M d, Y') : '—') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($pStatusId === 3)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Fulfilled
                                        </span>
                                    @elseif($pStatusId === 4)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Cancelled
                                        </span>
                                    @elseif($pStatusId === 5)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                            {{ $pStatusName }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-slate-400 italic py-2">No historical reservations on file.</p>
        @endif
    </div>

</div>
