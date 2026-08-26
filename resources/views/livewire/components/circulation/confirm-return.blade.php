<div class="max-w-7xl mx-auto px-4 py-8 font-sans">
    <!-- Breadcrumbs / Top Navigation Header -->
    <div class="mb-6 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2 text-xs font-semibold text-[#64748B]">
            <a href="{{ route('admin.circulation-desk.index') }}" class="hover:text-[#102B70] transition-colors">Circulation Desk</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
            <a href="{{ route('admin.circulation-desk.return') }}" class="hover:text-[#102B70] transition-colors">Return Books</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-[#102B70] font-bold">Confirm Return</span>
        </div>
    </div>

    <!-- Header Panel -->
    <div class="mb-8 shrink-0 flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-[#102B70] tracking-tight">Confirm Return Transaction</h1>
        <p class="text-xs text-[#64748B] font-medium leading-relaxed">Please review the list of scanned books and their computed overdue status. Once confirmed, these items will be officially updated as available in the database.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left Column: Student Details & Fine Summary -->
        <div class="flex flex-col gap-6 lg:col-span-1">
            <!-- Student Information Card -->
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <h3 class="text-xs font-extrabold text-[#102B70] uppercase tracking-wider mb-4">Student Profile</h3>
                
                @if($student)
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-4">
                            <!-- Avatar -->
                            <div class="w-14 h-14 rounded-full bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shrink-0 shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-[16px] text-[#0F172A] leading-tight truncate">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                <p class="text-xs text-[#64748B] font-medium mt-1 truncate">{{ $student->school_id_number }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 mt-2 rounded-md text-[9px] font-bold border {{ strtolower($student->libraryStatus->status ?? 'active') === 'active' ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
                                    {{ $student->libraryStatus->status ?? 'Active' }}
                                </span>
                            </div>
                        </div>

                        <hr class="border-[#F1F5F9] my-1">

                        <div class="flex flex-col gap-2.5 text-xs text-[#64748B] font-semibold">
                            <div class="flex justify-between py-1">
                                <span>Program & Year</span>
                                <span class="text-[#0F172A]">{{ $student->program }} - Year {{ $student->year_level }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Email Address</span>
                                <span class="text-[#0F172A] truncate max-w-[180px]">{{ $student->account->email ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Contact Number</span>
                                <span class="text-[#0F172A]">{{ $student->contact_num ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Return Stats & Fines Summary Card -->
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <h3 class="text-xs font-extrabold text-[#102B70] uppercase tracking-wider mb-4">Summary</h3>
                
                @php
                    $totalBooks = count($transactions);
                    $overdueCount = 0;
                    $totalFine = 0.00;
                    foreach ($transactions as $t) {
                        $fine = $this->getFineAmount($t->due_date);
                        if ($fine > 0) {
                            $overdueCount++;
                            $totalFine += $fine;
                        }
                    }
                @endphp

                <div class="flex flex-col gap-3.5 text-xs font-semibold text-[#64748B]">
                    <div class="flex justify-between py-1 border-b border-[#F1F5F9]">
                        <span>Scanned Books for Return</span>
                        <span class="text-[#0F172A] font-bold text-[13px]">{{ $totalBooks }} {{ Str::plural('book', $totalBooks) }}</span>
                    </div>

                    <div class="flex justify-between py-1 border-b border-[#F1F5F9]">
                        <span>Overdue Items</span>
                        <span class="font-bold text-[13px] {{ $overdueCount > 0 ? 'text-[#EF4444]' : 'text-[#64748B]' }}">{{ $overdueCount }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-[#F1F5F9] items-center">
                        <span>Total Overdue Fines</span>
                        <span class="font-extrabold text-[16px] {{ $totalFine > 0 ? 'text-[#EF4444]' : 'text-[#10B981]' }}">
                            ₱{{ number_format($totalFine, 2) }}
                        </span>
                    </div>
                </div>

                @if($totalFine > 0)
                    <!-- Highlight Fine Notification -->
                    <div class="mt-4 bg-[#FEF2F2] border border-[#FECACA] rounded-xl p-3 flex gap-2.5 items-start">
                        <svg class="w-4.5 h-4.5 text-[#EF4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-bold text-[#B91C1C]">Overdue Fines Detected</span>
                            <span class="text-[10px] text-[#EF4444] font-medium leading-normal">Confirming the return will automatically log a pending fine record of ₱{{ number_format($totalFine, 2) }} under the student's profile.</span>
                        </div>
                    </div>
                @else
                    <!-- Success No Fine Notification -->
                    <div class="mt-4 bg-[#F0FDF4] border border-[#BBF7D0] rounded-xl p-3 flex gap-2.5 items-start">
                        <svg class="w-4.5 h-4.5 text-[#10B981] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-bold text-[#15803D]">Perfect Status</span>
                            <span class="text-[10px] text-[#16A34A] font-medium leading-normal">All books are scanned on time. No overdue fines will be generated.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Scanned Books Table List -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col gap-4 overflow-hidden">
                <h3 class="text-xs font-extrabold text-[#64748B] uppercase tracking-wider">Return Items Queue</h3>

                <div class="overflow-x-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner">
                    <table class="w-full text-left border-collapse min-w-[650px]">
                        <thead>
                            <tr class="border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider bg-slate-50">
                                <th class="py-3.5 px-4 w-[45%]">Book Details</th>
                                <th class="py-3.5 px-4 w-[18%]">Borrowed Date</th>
                                <th class="py-3.5 px-4 w-[18%]">Due Date</th>
                                <th class="py-3.5 px-4 w-[19%] text-right">Fine Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9] text-xs text-[#0F172A]">
                            @foreach($transactions as $t)
                                @php
                                    $book = $t->book;
                                    $detail = $book ? $book->bookDetail : null;
                                    $data = $detail ? $detail->bookData : null;
                                    $authorName = 'Unknown Author';
                                    if ($data && $data->authors->isNotEmpty()) {
                                        $authorName = $data->authors->map(function($a) {
                                            return trim($a->first_name . ' ' . $a->last_name);
                                        })->implode(', ');
                                    }

                                    $overdueDays = $this->getOverdueDays($t->due_date);
                                    $fineAmount = $this->getFineAmount($t->due_date);
                                @endphp
                                <tr class="align-middle hover:bg-[#F8FAFC]">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <!-- Book Miniature Cover -->
                                            <div class="w-9 h-12 bg-slate-50 border border-[#E2E8F0] rounded overflow-hidden flex items-center justify-center shrink-0">
                                                @if($detail && !empty($detail->cover_image))
                                                    <img src="{{ $detail->cover_image }}" alt="Cover" class="w-full h-full object-cover" onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-[#102B70] to-[#FCC719] flex items-center justify-center\'><span class=\'text-[8px] text-white font-bold\'>BOOK</span></div>';">
                                                @else
                                                    <div class="w-full h-full bg-gradient-to-br from-[#102B70] to-[#FCC719] flex items-center justify-center">
                                                        <span class="text-[8px] text-white/90 font-bold uppercase tracking-wider text-center px-1 leading-tight">{{ $book->accession_number }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-bold text-[13.5px] leading-tight text-[#0F172A] truncate">{{ $data ? $data->book_title : 'Unknown Book' }}</span>
                                                <span class="text-[11px] text-[#64748B] mt-0.5 truncate">{{ $authorName }}</span>
                                                <span class="text-[10px] text-[#94A3B8] mt-1 font-semibold">Accession: {{ $book ? $book->accession_number : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-[#64748B]">
                                        {{ $t->issued_date->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-[#64748B]">
                                        {{ $t->due_date->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if($fineAmount > 0)
                                            <div class="flex flex-col items-end gap-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                                    {{ $overdueDays }} {{ Str::plural('Day', $overdueDays) }} Overdue
                                                </span>
                                                <span class="font-bold text-xs text-[#EF4444]">₱{{ number_format($fineAmount, 2) }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#10B981]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                On Time
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Action Buttons inside the Card Container -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-4 mt-4 border-t border-[#E2E8F0] pt-6">
                    <a href="{{ route('admin.circulation-desk.return') }}" 
                       class="w-full sm:w-auto h-14 px-8 border border-[#102B70] text-[#102B70] hover:bg-slate-50 rounded-2xl text-[14px] font-bold transition-all flex items-center justify-center gap-1.5 focus:ring-4 focus:ring-[#102B70]/10 outline-none">
                        Cancel & Go Back
                    </a>
                    
                    <button type="button"
                            wire:click="confirmReturn"
                            class="w-full sm:w-auto h-14 px-10 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-2xl text-[14px] font-bold transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg focus:ring-4 focus:ring-[#102B70]/20 outline-none">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Confirm Return
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
