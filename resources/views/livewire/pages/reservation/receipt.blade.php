            @php
                $student = $reservation->student;
                $book = $reservation->book;
                $bookDetail = $book?->bookDetail;
                $bookData = $bookDetail?->bookData;
                $authors = $bookData?->authors?->map(fn($a) => $a->first_name . ' ' . $a->last_name)->join(', ') ?? 'Unknown Author';
                $coverUrl = $bookDetail?->cover_url ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp'));
                $dueDate = $reservation->due_date?->copy()->setTimezone('Asia/Manila');
                $claimTime = ($dueDate && $dueDate->hour >= 8 && $dueDate->hour <= 18)
                    ? $dueDate->format('h:i A')
                    : '5:00 PM';
                $statusId = (int) ($reservation->reservation_status_id ?? 1);
            @endphp

            <!-- Printable Voucher Card -->
            <div class="rounded-xl border border-[#E2E8F0] bg-white  overflow-hidden print:shadow-none print:border-none" id="reservation-slip">
                <!-- Voucher Header -->
                <div class="bg-[#102B70] text-white p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-base font-bold text-white uppercase tracking-wider block">PGPC Library System</span>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Official Reservation Slip</h2>
                        <p class="text-base text-white/80">Padre Garcia Polytechnic College &bull; Circulation Desk Hold Voucher</p>
                        <p class="text-sm text-white/90 font-medium pt-0.5">Keep this reference for claiming your item.</p>
                    </div>
                    <div class="sm:text-right sm:border-l sm:border-white/20 sm:pl-6">
                        <span class="text-base text-white/80 block uppercase tracking-wider font-semibold">Slip Reference</span>
                        <span class="text-lg sm:text-xl font-mono font-extrabold text-white">#RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <!-- Voucher Body -->
                <div class="p-4 sm:p-8 space-y-6">
                    <!-- Book Info Card -->
                    <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5 pb-6 border-b border-slate-200">
                        <div class="relative shrink-0 overflow-hidden border border-slate-200 bg-white">
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $bookData?->book_title ?? 'Book' }}"
                                class="h-36 w-26 object-cover"
                                onerror="this.src='{{ asset('images/book-cover.webp') }}'"
                            >
                        </div>
                        <div class="space-y-2 min-w-0 flex-1 w-full">
                            <div>
                                <span class="block text-sm font-semibold text-[#102B70]">
                                    {{ $bookData?->categories?->first()?->name ?? 'General Collection' }}
                                </span>
                                <h3 class="text-xl font-bold text-[#0F172A] leading-snug mt-1.5">
                                    {{ $bookData?->book_title ?? 'Untitled Book' }}
                                </h3>
                                <p class="text-sm font-semibold text-slate-600 mt-0.5">By {{ $authors }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3 pt-3 border-t border-slate-200 text-base">
                                <div class="space-y-0.5">
                                    <span class="text-slate-600 block text-sm">Accession No</span>
                                    <span class="font-bold text-[#0F172A] break-words block text-sm">{{ $book?->accession_number ?: 'N/A' }}</span>
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-slate-600 block text-sm">Call Number</span>
                                    <span class="font-bold text-[#102B70] break-words block text-sm">{{ $bookDetail?->call_number ?: 'N/A' }}</span>
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-slate-600 block text-sm">Location</span>
                                    <span class="font-bold text-slate-700 break-words block text-sm">{{ $book?->location ?: 'General Circulation' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup & Student Schedule Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-200">
                        <!-- Claim Deadline Box -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-slate-600">Pickup deadline</h4>
                            </div>

                            <div>
                                <div class="text-2xl font-black text-[#102B70]">
                                    {{ $dueDate ? $dueDate->format('M d, Y') : 'Not set' }}
                                </div>
                                <div class="text-base font-bold text-slate-600 mt-0.5">
                                    {{ $dueDate ? "Collect before {$claimTime} (Closing Time)" : "Deadline not set" }}
                                </div>
                            </div>

                            @if($dueDate && in_array($statusId, [1, 2], true))
                            <p class="text-base text-slate-600 leading-relaxed pt-1 border-t border-slate-200">
                                Items not collected by <strong class="text-slate-800 font-bold">{{ $claimTime }}</strong> will be automatically released back to the general stacks.
                            </p>
                            @endif
                        </div>

                        <!-- Reserved By Box -->
                        <div class="space-y-2.5 sm:border-l sm:border-slate-200 sm:pl-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-500">
                                    <h4 class="text-sm font-semibold text-slate-600">Reserved by</h4>
                                </div>
                            </div>

                            <div>
                                <div class="text-lg font-bold text-[#0F172A]">
                                    {{ $student?->first_name }} {{ $student?->last_name }}
                                </div>
                                <div class="text-base font-semibold text-[#102B70] mt-0.5">
                                    ID Number: <span class="font-bold font-mono">{{ $student?->school_id_number ?: 'Not assigned' }}</span>
                                </div>
                            </div>

                            <div class="text-base text-slate-600 pt-1.5 border-t border-slate-100 space-y-0.5">
                                <span class="text-slate-600 block text-sm">Academic Program:</span>
                                <span class="font-semibold text-slate-800 block text-base leading-snug" title="{{ $student?->program }}">{{ $student?->program ?: 'Undergraduate Student' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Verification Code -->
                    <div class="p-4 sm:p-5 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,18rem)] items-center gap-6">
                            <div class="space-y-2 text-center sm:text-left flex-1">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    <span class="text-base font-bold uppercase tracking-wider text-slate-500">Circulation Verification</span>
                                    @if($statusId === 1)
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Pending Approval
                                        </span>
                                    @elseif($statusId === 2)
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Ready for Pickup
                                        </span>
                                    @elseif($statusId === 3)
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Fulfilled
                                        </span>
                                    @elseif($statusId === 4)
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Cancelled
                                        </span>
                                    @elseif($statusId === 5)
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Expired
                                        </span>
                                    @else
                                        <span class="text-sm font-semibold text-[#102B70]">
                                            Hold Active
                                        </span>
                                    @endif
                                </div>
                                <p class="text-base text-slate-600 leading-relaxed">
                                    Present this QR code or reservation reference directly to the circulation librarian for instant checkout.
                                </p>
                                <div class="flex flex-wrap items-center gap-x-2 text-[#102B70]">
                                    <span class="text-sm font-semibold text-slate-600">Ref:</span>
                                    <span class="font-mono text-sm font-black text-[#102B70] tracking-wider">PGPC-RES-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                            @if($qrCodeSvg)
                                <x-qr-image :svg="$qrCodeSvg" :filename="'reservation-'.$reservation->id" label="Reservation verification QR code" />
                            @else
                                <p role="status" class="text-sm text-slate-600">QR code unavailable. Use the reference above.</p>
                            @endif
                        </div>
                    @if(in_array($statusId, [1, 2], true))
                    <!-- Important Instructions -->
                    <div class="p-5 rounded-xl bg-white border border-[#E2E8F0] space-y-2 ">
                        <div class="flex items-center gap-2 text-sm font-bold text-[#102B70]">
                            <span>Pickup Instructions:</span>
                        </div>
                        <ul class="text-base text-slate-600 space-y-1.5 pl-6 list-disc leading-relaxed">
                            <li>Present your validated <strong>PGPC Student ID Card</strong> or this voucher to the circulation desk staff.</li>
                            <li>Standard borrowing period applies upon claiming (maximum 7 days checkout).</li>
                            <li>If you no longer need this book, you can cancel your hold from your <a href="{{ route('student.profile') }}" wire:navigate class="text-[#102B70] font-semibold underline hover:text-[#0B225E]">profile</a> to make it available for other students.</li>
                        </ul>
                    </div>
                </div>

                    @endif
                @unless($receiptModal ?? false)
                <!-- Footer Action Buttons -->
                <div class="p-4 sm:p-6 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between print:hidden">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="h-12 px-6 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold  transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none"
                    >
                        <span>Print Slip / Save PDF</span>
                    </button>

                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('student.profile') }}"
                            wire:navigate
                            class="flex-1 sm:flex-initial h-12 px-6 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#102B70] text-sm font-bold transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none"
                        >
                            <span>Go to My Profile</span>
                        </a>

                        <a
                            href="{{ route('student.reservations') }}"
                            wire:navigate
                            class="flex-1 sm:flex-initial h-12 px-6 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold  hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none"
                        >
                            <span>Browse More Books</span>
                        </a>
                    </div>
                </div>
                @endunless
            </div>
