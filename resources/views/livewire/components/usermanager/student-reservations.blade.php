@php
    $reservations = $this->reservations ?? collect();
    $activeReservations = $this->activeReservations ?? collect();
    $pastReservations = $this->pastReservations ?? collect();
@endphp

<div class="space-y-6">

    <!-- Active Reservations Section -->
    <div class="rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Active Reservations</h3>
                    @if($activeReservations->count() > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                            {{ $activeReservations->count() }} Active
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium">Books currently held or awaiting your collection at the circulation desk.</p>
            </div>

            <button
                type="button"
                wire:click="openReserveModal"
                class="h-10 px-4 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-[0.98] shrink-0"
            >
                <svg class="w-3.5 h-3.5 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Reserve a Book</span>
            </button>
        </div>

        @if($activeReservations->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeReservations as $res)
                    @php
                        $rBook = $res->book;
                        $rDetail = $rBook?->bookDetail;
                        $rData = $rDetail?->bookData;
                        $rTitle = $rData?->book_title ?? 'Untitled Book';
                        $rAuthors = $rData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
                        $rCover = $rDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                        $rStatusId = (int) $res->reservation_status_id;
                        $rDue = $res->due_date ? \Carbon\Carbon::parse($res->due_date) : null;
                        $rIsDueSoon = $rDue && $rDue->diffInDays(now()) <= 1;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-[#F8FAFC]/60 p-4 flex flex-col justify-between gap-4 hover:border-[#102B70]/40 transition-colors">
                        <div class="flex gap-3.5">
                            <img
                                src="{{ $rCover }}"
                                alt="{{ $rTitle }}"
                                class="h-24 w-16 rounded-lg object-cover shadow-xs border border-slate-200 shrink-0"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] font-bold text-[#102B70]">
                                        #RES-{{ str_pad($res->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if($rStatusId === 1)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1 shrink-0">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Approval
                                        </span>
                                    @elseif($rStatusId === 2)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1 shrink-0">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                            Ready for Pickup
                                        </span>
                                    @endif
                                </div>

                                <h4 class="text-sm font-bold text-[#0F172A] line-clamp-1" title="{{ $rTitle }}">
                                    {{ $rTitle }}
                                </h4>
                                <p class="text-xs text-slate-500 truncate font-medium">By {{ $rAuthors }}</p>

                                <div class="pt-1 text-[11px] text-slate-500 font-mono space-y-0.5">
                                    <div><span class="text-slate-400">Accession:</span> {{ $rBook?->accession_number ?: 'N/A' }}</div>
                                    <div><span class="text-slate-400">Location:</span> {{ $rBook?->location ?: 'Main Library' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pickup Window Banner -->
                        <div class="p-2.5 rounded-xl border {{ $rIsDueSoon ? 'border-amber-200 bg-amber-50/70' : 'border-slate-200 bg-white' }} flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ $rIsDueSoon ? 'text-amber-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="font-medium text-slate-700">Claim Deadline:</span>
                            </div>
                            <span class="font-bold {{ $rIsDueSoon ? 'text-amber-700 font-extrabold' : 'text-[#102B70]' }}">
                                {{ $rDue ? $rDue->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>

                        <!-- Card Actions -->
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                            <a
                                href="{{ route('student.reservations.confirmation', $res->id) }}"
                                wire:navigate
                                class="text-xs font-bold text-[#102B70] hover:underline flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>View Slip</span>
                            </a>

                            <button
                                type="button"
                                wire:click="cancelReservation({{ $res->id }})"
                                wire:confirm="Are you sure you want to cancel this reservation? The held copy will be released."
                                class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer"
                            >
                                Cancel Hold
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center space-y-3">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                </div>
                <h4 class="text-sm font-bold text-[#102B70]">No Active Reservations</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    You currently have no pending or awaiting-pickup book holds. You can reserve up to 3 books in advance.
                </p>
                <button
                    type="button"
                    wire:click="openReserveModal"
                    class="h-9 px-4 rounded-xl bg-[#102B70] text-white text-xs font-bold hover:bg-[#0B225E] transition-all cursor-pointer inline-flex items-center gap-1.5 shadow-xs"
                >
                    <svg class="w-3.5 h-3.5 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Reserve a Book Now</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Past / Historical Reservations -->
    <div class="rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-5 sm:p-6 lg:p-7 shadow-[0_4px_20px_rgba(16,43,112,0.03)] space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="text-base sm:text-lg font-bold text-[#102B70]">Reservation History</h3>
            <p class="text-xs text-slate-500 font-medium">Record of fulfilled, cancelled, and expired requests.</p>
        </div>

        @if($pastReservations->isNotEmpty())
            <div class="overflow-x-auto -mx-5 sm:-mx-6 lg:-mx-7 px-5 sm:px-6 lg:px-7">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-[#F1F5F9] text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                            <th class="py-3 px-3">Slip ID</th>
                            <th class="py-3 px-4">Book Title</th>
                            <th class="py-3 px-4">Reserved Date</th>
                            <th class="py-3 px-4">Closed Date</th>
                            <th class="py-3 px-4 text-right">Final Status</th>
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
                                <td class="py-3.5 px-3 font-mono text-xs font-bold text-[#102B70]">
                                    #RES-{{ str_pad($past->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#0F172A]">
                                    <div class="truncate max-w-xs">{{ $pData?->book_title ?? 'Untitled Book' }}</div>
                                    <div class="font-mono text-[11px] text-slate-400 font-normal">Acc: {{ $pBook?->accession_number ?: 'N/A' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $past->reservation_date ? \Carbon\Carbon::parse($past->reservation_date)->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $past->fulfilled_date ? \Carbon\Carbon::parse($past->fulfilled_date)->format('M d, Y') : ($past->cancelled_date ? \Carbon\Carbon::parse($past->cancelled_date)->format('M d, Y') : '—') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($pStatusId === 3)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Fulfilled
                                        </span>
                                    @elseif($pStatusId === 4)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Cancelled
                                        </span>
                                    @elseif($pStatusId === 5)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
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
            <p class="text-xs text-slate-400 italic py-2">No historical reservations on file.</p>
        @endif
    </div>

</div>
