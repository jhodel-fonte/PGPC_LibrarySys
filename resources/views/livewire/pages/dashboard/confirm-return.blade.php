<div class="relative w-full h-auto lg:h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-4 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-y-auto lg:overflow-hidden check-in-workstation">
    <style>
        @media (min-width: 1024px) {
            @media (min-height: 800px) {
                main {
                    overflow: hidden !important;
                    display: flex;
                    flex-direction: column;
                    height: calc(100vh - 70px) !important;
                }
            }
        }

        /* Zoom and Low Height Viewport Overrides */
        @media (max-height: 799px) {
            .check-in-workstation,
            .check-in-workstation .lg\:overflow-hidden,
            .check-in-workstation .lg\:h-full,
            .check-in-workstation .lg\:h-\[calc\(100vh-70px\)\],
            .check-in-workstation .lg\:h-\[calc\(100\%-80px\)\] {
                height: auto !important;
                overflow: visible !important;
            }
            main {
                overflow: auto !important;
                height: auto !important;
            }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-custom {
            animation: spin 1s linear infinite;
            transform-origin: center;
        }
    </style>

    <div class="w-full max-w-[1460px] flex flex-col h-auto lg:h-full lg:overflow-hidden">

        <!-- Header -->
        <div class="w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">
            <!-- Back Link -->
            <div class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.circulation-desk.return') }}" wire:navigate class="inline-flex items-center gap-2 text-[#64748B] hover:text-[#102B70] font-semibold text-[13px] transition-colors group">
                    <svg class="w-4 h-4 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Workstation
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-[#64748B] font-semibold">Review returns</span>
            </div>

            <div class="flex flex-col">
                <h1 class="text-[1.35rem] font-bold text-[#102B70] tracking-tight leading-tight">Review Book Returns</h1>
                <p class="text-xs text-[#64748B] font-medium leading-relaxed mt-0.5">Carefully review the list of scanned books and overdue fine assessments before confirming the transaction.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 overflow-hidden mb-2 items-stretch">

            <!-- Left Column: Return Items -->
            <div class="lg:col-span-7 flex flex-col gap-4 overflow-hidden h-full">
                <div class="flex-1 flex flex-col overflow-hidden bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
                    <span class="block text-[11px] font-bold text-[#64748B] uppercase tracking-widest mb-4 shrink-0">Items to be Returned ({{ count($transactionsData) }})</span>

                    <!-- Table Container -->
                    <div class="overflow-x-auto flex-1 overflow-y-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner mb-4 custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead class="sticky top-0 bg-white z-10 border-b border-[#E2E8F0]">
                                <tr class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                                    <th class="py-3 px-5 w-[40%] bg-white">Book Details</th>
                                    <th class="py-3 px-5 w-[20%] bg-white">Borrowed Date</th>
                                    <th class="py-3 px-5 w-[20%] bg-white">Due Date</th>
                                    <th class="py-3 px-5 w-[20%] text-right bg-white">Fine Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F5F9] text-sm text-[#0F172A]">
                                @foreach($transactionsData as $item)
                                    <tr class="align-middle hover:bg-slate-50 transition-colors">
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3.5">
                                                <!-- Miniature Cover or Icon tile -->
                                                <div class="w-10 h-14 bg-gradient-to-br from-[#102B70] to-[#3B82F6] rounded-md shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                                    <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="font-bold text-[14.5px] leading-tight text-[#0F172A] truncate">{{ $item['title'] }}</span>
                                                    <span class="text-[12.5px] text-[#64748B] mt-0.5 truncate">{{ $item['author'] }}</span>
                                                    <span class="text-[11px] text-[#94A3B8] mt-0.5">Accession No. {{ $item['accession'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 font-semibold text-[#64748B]">
                                            {{ $item['issued_date'] }}
                                        </td>
                                        <td class="py-4 px-5 font-semibold text-[#64748B]">
                                            {{ $item['due_date'] }}
                                        </td>
                                        <td class="py-4 px-5 text-right">
                                            @if($item['fine_amount'] > 0)
                                                <div class="flex flex-col items-end gap-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                                        {{ round($item['overdue_days']) }} {{ Str::plural('day', round($item['overdue_days'])) }} overdue
                                                    </span>
                                                    <span class="font-bold text-sm text-[#EF4444]">₱{{ number_format($item['fine_amount'], 2) }}</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#10B981]">
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

                    <!-- Confirmation alert -->
                    <div class="bg-[#F0FDF4] rounded-xl p-4 flex gap-2.5 items-center shrink-0">
                        <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-semibold text-[#15803D]">All items are ready to be returned.</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Student Info, Return Summary & Actions -->
            <div class="lg:col-span-5 flex flex-col h-full overflow-hidden shrink-0">
                <div class="flex-1 flex flex-col justify-between overflow-y-auto bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-sm custom-scrollbar">
                    <div class="flex flex-col gap-6">

                        <!-- Student Information Section -->
                        <div>
                            @if($student)
                                @php
                                    $initials = '';
                                    $fullName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                                    if (!empty($fullName)) {
                                        $words = explode(' ', $fullName);
                                        $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
                                    }
                                    $isStatusActive = strtolower($student->libraryStatus->status ?? 'active') === 'active';
                                @endphp
                                <div class="flex flex-col gap-5">
                                    <!-- Avatar, Name & School ID / Active Badge -->
                                    <div class="flex items-center gap-3.5 pb-4">
                                        <!-- Branded Initials Avatar -->
                                        <div class="w-12 h-12 rounded-full bg-[#102B70] text-white flex items-center justify-center shrink-0 shadow-sm text-sm font-bold tracking-wider select-none">
                                            {{ $initials ?: 'S' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-extrabold text-[17px] text-[#0F172A] leading-tight truncate" title="{{ $fullName }}">{{ $fullName }}</h4>
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                <span class="text-sm font-bold text-[#64748B]">{{ $student->school_id_number }}</span>
                                                <span class="text-slate-300 text-[10px] select-none">&bull;</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9.5px] font-bold border {{ $isStatusActive ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
                                                    {{ $student->libraryStatus->status ?? 'Active' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Compact 2-Column Student Details Grid -->
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-3.5">
                                        <!-- Program / Course -->
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Program / Course</span>
                                            <span class="text-[13.5px] font-bold text-[#0F172A] truncate" title="{{ $student->program }}">{{ $student->program }}</span>
                                        </div>
                                        <!-- Year Level -->
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Year Level</span>
                                            <span class="text-[13.5px] font-bold text-[#0F172A]">{{ $student->year_level }}</span>
                                        </div>
                                        <!-- Email Address -->
                                        <div class="flex flex-col gap-0.5 col-span-2">
                                            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Email Address</span>
                                            <span class="text-[13.5px] font-bold text-[#0F172A] truncate" title="{{ $student->account->email ?? 'N/A' }}">{{ $student->account->email ?? 'N/A' }}</span>
                                        </div>
                                        <!-- Contact Number -->
                                        <div class="flex flex-col gap-0.5 col-span-2">
                                            <span class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Contact Number</span>
                                            <span class="text-[13.5px] font-bold text-[#0F172A] truncate" title="{{ $student->contact_num ?? 'N/A' }}">{{ $student->contact_num ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="border-[#E2E8F0]">

                        <!-- Return Summary -->
                        <div>
                            <span class="block text-[11px] font-bold text-[#64748B] uppercase tracking-widest mb-4">Return Summary</span>

                            @php
                                $totalBooks = count($transactionsData);
                            @endphp

                            <div class="flex justify-between items-center">
                                <span class="text-[#64748B] font-semibold text-[13px]">Scanned Books</span>
                                <span class="text-[#0F172A] font-extrabold text-[15px]">{{ $totalBooks }} {{ Str::plural('book', $totalBooks) }}</span>
                            </div>

                            @if($totalFine > 0)
                                <!-- Consolidated fine warning -->
                                <div class="mt-4 bg-[#FEF2F2] rounded-xl p-4 flex gap-3 items-start border border-[#FCA5A5]">
                                    <svg class="w-5 h-5 text-[#EF4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="flex flex-col gap-1 w-full">
                                        <span class="text-[13.5px] font-extrabold text-[#B91C1C]">Overdue Fine: ₱{{ number_format($totalFine, 2) }}</span>
                                        <span class="text-[11.5px] font-bold text-[#B91C1C] leading-relaxed">This amount will be added to the student's pending balance.</span>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-[#64748B] font-semibold text-[13px]">Overdue Fines</span>
                                    <span class="font-extrabold text-base text-[#10B981]">₱0.00</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 border-t border-[#E2E8F0] pt-5 flex flex-col gap-3">
                        <button type="button"
                                wire:click="confirmReturn"
                                wire:loading.attr="disabled"
                                wire:target="confirmReturn"
                                class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2.5 shadow-sm hover:shadow-md outline-none disabled:opacity-65 disabled:cursor-not-allowed group">
                            <span wire:loading.remove wire:target="confirmReturn" class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs uppercase tracking-wider">Confirm Return</span>
                            </span>
                            <span wire:loading wire:target="confirmReturn" class="text-xs uppercase tracking-wider flex items-center gap-2">
                                <svg class="animate-spin-custom h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Completing...
                            </span>
                        </button>

                        <a href="{{ route('admin.circulation-desk.return') }}" wire:navigate
                           class="w-full py-2 hover:text-[#102B70] text-[#64748B] text-xs font-bold transition-all flex items-center justify-center gap-1.5 focus:outline-none hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel & Go Back
                        </a>

                        <span class="block text-center text-[10px] text-[#94A3B8] font-medium mt-0.5">This action will update records in the system.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
