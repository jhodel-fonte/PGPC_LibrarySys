<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-base text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[800px] space-y-6 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        @if(!$reservation)
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center space-y-3">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h2 class="text-xl font-bold text-[#102B70]">Reservation Not Found</h2>
                <p class="text-base text-slate-500">The requested reservation voucher could not be located or has expired.</p>
                <a href="{{ route('student.reservations') }}" wire:navigate class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-[#102B70] text-white text-sm font-bold">
                    Browse Available Books
                </a>
            </div>
        @else
            @php
                $student = $reservation->student;
                $book = $reservation->book;
                $bookDetail = $book?->bookDetail;
                $bookData = $bookDetail?->bookData;
                $authors = $bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
                $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                $dueDate = $reservation->due_date ? \Carbon\Carbon::parse($reservation->due_date) : null;
            @endphp

            <!-- Success Banner -->
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 sm:p-5 flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-emerald-900">Book Reservation Confirmed!</h1>
                    <p class="text-sm sm:text-base text-emerald-800">
                        Your hold has been recorded in the library circulation system. Present this voucher or your School ID at the circulation desk to collect your copy.
                    </p>
                </div>
            </div>

            <!-- Printable Voucher Card -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-md overflow-hidden print:shadow-none print:border-none" id="reservation-slip">
                <!-- Voucher Header -->
                <div class="bg-[#102B70] text-white p-5 sm:p-6 flex items-center justify-between">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-[#FCC719] uppercase tracking-wider block">PGPC Library System</span>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Official Reservation Slip</h2>
                        <p class="text-sm text-white/80">Keep this reference for claiming your item.</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-white/80 block uppercase tracking-wider font-semibold">Slip Reference</span>
                        <span class="text-base sm:text-lg font-mono font-extrabold text-[#FCC719]">#RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <!-- Voucher Body -->
                <div class="p-6 sm:p-8 space-y-6">
                    <!-- Book Info Summary -->
                    <div class="flex flex-col sm:flex-row gap-5 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $bookData?->book_title ?? 'Book' }}"
                            class="h-32 w-22 rounded-lg object-cover shadow-xs border border-slate-200 shrink-0 self-center sm:self-start"
                            onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                        >
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <span class="text-xs font-bold text-[#102B70] uppercase tracking-wider block">
                                {{ $bookData?->categories?->first()?->name ?? 'General Stack' }}
                            </span>
                            <h3 class="text-lg font-bold text-[#0F172A] leading-snug">
                                {{ $bookData?->book_title ?? 'Untitled Book' }}
                            </h3>
                            <p class="text-sm text-slate-600 font-medium">By {{ $authors }}</p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-sm pt-2 text-slate-600 border-t border-slate-200">
                                <div><span class="font-bold text-slate-800">Accession No:</span> {{ $book?->accession_number ?: 'N/A' }}</div>
                                <div><span class="font-bold text-slate-800">Call No:</span> {{ $bookDetail?->call_number ?: 'N/A' }}</div>
                                <div><span class="font-bold text-slate-800">Location:</span> {{ $book?->location ?: 'Main Library' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup & Student Schedule Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Pickup Window Box -->
                        <div class="p-4.5 rounded-xl border border-amber-200 bg-amber-50/50 space-y-2">
                            <div class="flex items-center gap-2 text-amber-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h4 class="text-xs font-bold uppercase tracking-wider">Claim Deadline</h4>
                            </div>
                            <div class="text-2xl font-extrabold text-[#102B70]">
                                {{ $dueDate ? $dueDate->format('M d, Y') : '—' }}
                            </div>
                            <p class="text-sm text-slate-600">
                                Items not collected by <strong class="text-slate-800">{{ $dueDate ? $dueDate->format('h:i A') : 'closing time' }}</strong> will be automatically released back to the general stacks.
                            </p>
                        </div>

                        <!-- Borrower Info Box -->
                        <div class="p-4.5 rounded-xl border border-slate-200 bg-white space-y-2">
                            <div class="flex items-center gap-2 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <h4 class="text-xs font-bold uppercase tracking-wider">Reserved By</h4>
                            </div>
                            <div class="text-lg font-bold text-[#102B70]">
                                {{ $student?->first_name }} {{ $student?->last_name }}
                            </div>
                            <div class="text-sm text-slate-600 space-y-0.5 font-mono">
                                <div>ID Number: {{ $student?->school_id_number ?: 'Not assigned' }}</div>
                                <div>Program: {{ $student?->program ?: 'Undergraduate' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Verification Code -->
                    @if($qrCodeSvg)
                        <div class="p-4.5 rounded-xl border border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="space-y-1 text-center sm:text-left">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Desk Scan & Verification Code</h4>
                                <p class="text-sm text-slate-600">Librarians can scan this QR code directly at the circulation desk for fast checkout.</p>
                                <span class="font-mono text-sm font-bold text-[#102B70] block">PGPC-RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="h-28 w-28 bg-white p-2 rounded-xl border border-slate-200 flex items-center justify-center shrink-0">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    @endif

                    <!-- Important Instructions -->
                    <div class="p-4.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-600 space-y-2">
                        <div class="font-bold text-[#102B70] text-base">Pickup Instructions:</div>
                        <ul class="list-disc list-inside space-y-1 text-slate-600 leading-relaxed">
                            <li>Present your validated PGPC Student ID Card to the circulation desk staff.</li>
                            <li>Standard circulation borrowing privileges apply (maximum 7 days checkout once picked up).</li>
                            <li>If you no longer need this book, please cancel your reservation from your profile to allow other students to borrow it.</li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="p-4 sm:p-6 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="h-11 px-5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-base font-bold transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Print Slip / Save PDF</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('student.profile') }}"
                            wire:navigate
                            class="h-11 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#102B70] text-base font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                        >
                            <span>Go to My Profile</span>
                        </a>

                        <a
                            href="{{ route('student.reservations') }}"
                            wire:navigate
                            class="h-11 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-base font-bold shadow-xs hover:shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
                        >
                            <span>Browse More Books</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
