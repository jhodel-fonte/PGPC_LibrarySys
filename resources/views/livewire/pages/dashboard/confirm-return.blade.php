<div class="relative w-full h-[calc(100vh-70px)] flex flex-col justify-start pt-2 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-hidden">
    <!-- Lock page layout scroll to prevent entire page scrolling -->
    <style>
        main {
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 70px) !important;
        }
    </style>

    <div class="w-full max-w-[1460px] mx-auto flex flex-col h-full overflow-hidden">
        <!-- Header Panel (shrink-0) -->
        <div class="mb-5 shrink-0 flex items-center gap-4">
            <!-- Icon rounded-square container with soft border -->
            <div class="w-12 h-12 rounded-2xl bg-[#F0FDF4] border border-[#DCFCE7] text-[#10B981] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#102B70] tracking-tight leading-tight">Confirm Return Transaction</h1>
                <p class="text-xs text-[#64748B] font-medium leading-relaxed mt-0.5">Please review the returned books and calculated fine status before confirmation.</p>
            </div>
        </div>

        <!-- Main Workspace Grid (flex-1 overflow-hidden) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 overflow-hidden mb-2 items-stretch">
            
            <!-- Left Column: Return Items (70% width -> lg:col-span-8) -->
            <div class="lg:col-span-8 flex flex-col gap-4 overflow-hidden h-full">
                <!-- Items to Be Returned Card -->
                <div class="flex-1 flex flex-col overflow-hidden bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <span class="block text-[11px] font-extrabold text-[#64748B] uppercase tracking-wider mb-4 shrink-0">Items to be Returned ({{ count($transactionsData) }})</span>
                    
                    <!-- Table Container -->
                    <div class="flex-1 overflow-y-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner mb-4 custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead class="sticky top-0 bg-[#F8FAFC] z-10 border-b border-[#E2E8F0]">
                                <tr class="text-[11px] font-extrabold text-[#64748B] uppercase tracking-wider">
                                    <th class="py-3.5 px-4 w-[8%] text-center">#</th>
                                    <th class="py-3.5 px-4 w-[50%]">Book Details</th>
                                    <th class="py-3.5 px-4 w-[16%]">Borrowed Date</th>
                                    <th class="py-3.5 px-4 w-[16%]">Due Date</th>
                                    <th class="py-3.5 px-4 w-[10%] text-right">Fine Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F5F9] text-xs text-[#0F172A]">
                                @foreach($transactionsData as $index => $item)
                                    <tr class="align-middle hover:bg-[#F8FAFC] transition-colors">
                                        <td class="py-4 px-4 text-center font-bold text-[#64748B]">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-3">
                                                <!-- Cover -->
                                                <div class="w-8 h-11 rounded bg-gradient-to-br from-[#102B70] to-[#FCC719] shadow-sm flex items-center justify-center shrink-0 overflow-hidden relative border border-[#102B70]/10">
                                                    <span class="text-[7px] text-white/90 font-extrabold uppercase tracking-widest text-center px-1 leading-none z-10">BOOK</span>
                                                    <div class="absolute bottom-0 inset-x-0 h-1 bg-[#FCC719]"></div>
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="font-bold text-[13px] leading-tight text-[#0F172A] truncate">{{ $item['title'] }}</span>
                                                    <span class="text-[11px] text-[#64748B] mt-0.5 truncate">{{ $item['author'] }}</span>
                                                    <span class="text-[10px] text-[#94A3B8] mt-0.5 font-semibold">Accession No. {{ $item['accession'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-[#64748B]">
                                            {{ $item['issued_date'] }}
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-[#64748B]">
                                            {{ $item['due_date'] }}
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            @if($item['fine_amount'] > 0)
                                                <div class="flex flex-col items-end gap-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                                        {{ round($item['overdue_days']) }} {{ Str::plural('Day', round($item['overdue_days'])) }} Overdue
                                                    </span>
                                                    <span class="font-bold text-[11px] text-[#EF4444]">₱{{ number_format($item['fine_amount'], 2) }}</span>
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

                    <!-- Green confirmation alert -->
                    <div class="bg-[#F0FDF4] border border-[#DCFCE7] rounded-2xl p-4 flex gap-2.5 items-center shrink-0">
                        <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-bold text-[#15803D]">All items are ready to be returned.</span>
                    </div>
                </div>

            </div>
            
            <!-- Right Column: Combined Member Info, Return Summary & Action Buttons (30% width -> lg:col-span-4) -->
            <div class="lg:col-span-4 flex flex-col h-full overflow-hidden shrink-0">
                <div class="flex-1 flex flex-col justify-between overflow-y-auto bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] custom-scrollbar">
                    <div class="flex flex-col gap-5">
                        <!-- Member Information Section -->
                        <div>
                            <span class="block text-[11px] font-extrabold text-[#64748B] uppercase tracking-wider mb-4">Member Information</span>
                            
                            @if($student)
                                <div class="flex flex-col gap-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar -->
                                        <div class="w-12 h-12 rounded-full bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shrink-0 shadow-sm">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-bold text-[14.5px] text-[#0F172A] leading-tight truncate">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                                    {{ $student->libraryStatus->status ?? 'Active' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#64748B] font-medium mt-1 truncate">{{ $student->school_id_number }} &nbsp;•&nbsp; {{ $student->program }} - {{ $student->year_level }}</p>
                                        </div>
                                    </div>

                                    <hr class="border-[#F1F5F9] my-1">

                                    <div class="flex flex-col gap-2.5 text-xs">
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9] last:border-0">
                                            <span class="text-[#64748B] font-semibold">Email Address</span>
                                            <span class="text-[#0F172A] font-bold text-right">{{ $student->account->email ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9] last:border-0">
                                            <span class="text-[#64748B] font-semibold">Contact Number</span>
                                            <span class="text-[#0F172A] font-bold text-right">{{ $student->contact_num ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-start py-1.5 border-b border-[#F1F5F9] last:border-0">
                                            <span class="text-[#64748B] font-semibold shrink-0">Program</span>
                                            <span class="text-[#0F172A] font-bold text-right pl-4 leading-tight">{{ $student->program }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9] last:border-0">
                                            <span class="text-[#64748B] font-semibold">Year Level</span>
                                            <span class="text-[#0F172A] font-bold text-right">{{ $student->year_level }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="border-[#E2E8F0] my-1">

                        <!-- Return Summary Section -->
                        <div>
                            <span class="block text-[11px] font-extrabold text-[#64748B] uppercase tracking-wider mb-4">Return Summary</span>
                            
                            @php
                                $totalBooks = count($transactionsData);
                            @endphp

                            <div class="flex flex-col gap-3 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-[#F1F5F9] last:border-0">
                                    <div class="flex items-center gap-2.5 text-[#64748B] font-semibold">
                                        <div class="w-8 h-8 rounded-lg bg-[#EFF6FF] text-[#3B82F6] flex items-center justify-center">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                        </div>
                                        <span>Scanned Books</span>
                                    </div>
                                    <span class="text-[#0F172A] font-bold text-[13px]">{{ $totalBooks }} {{ Str::plural('book', $totalBooks) }}</span>
                                </div>

                                <div class="flex justify-between items-center py-1 border-b border-[#F1F5F9] last:border-0">
                                    <div class="flex items-center gap-2.5 text-[#64748B] font-semibold">
                                        <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#F97316] flex items-center justify-center">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <span>Overdue Items</span>
                                    </div>
                                    <span class="font-bold text-[13px] {{ $overdueCount > 0 ? 'text-[#EF4444]' : 'text-[#0F172A]' }}">{{ $overdueCount }}</span>
                                </div>

                                <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9] last:border-0">
                                    <div class="flex items-center gap-2.5 text-[#64748B] font-semibold">
                                        <div class="w-8 h-8 rounded-lg bg-[#FEF2F2] text-[#EF4444] flex items-center justify-center">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <span>Total Overdue Fines</span>
                                    </div>
                                    <span class="font-extrabold text-[15px] {{ $totalFine > 0 ? 'text-[#EF4444]' : 'text-[#10B981]' }}">
                                        ₱{{ number_format($totalFine, 2) }}
                                    </span>
                                </div>
                            </div>

                            @if($totalFine > 0)
                                <!-- Warning fine panel layout -->
                                <div class="mt-4 bg-[#FEF2F2] border border-[#FECACA] rounded-2xl p-4 flex gap-3 items-start">
                                    <svg class="w-5 h-5 text-[#EF4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-bold text-[#B91C1C]">Overdue Fines Detected</span>
                                        <span class="text-[10.5px] text-[#EF4444] font-semibold leading-relaxed">Confirming this return will automatically log a pending fine record of ₱{{ number_format($totalFine, 2) }} under the member's profile.</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons Section -->
                    <div class="mt-6 border-t border-[#E2E8F0] pt-5 flex flex-col gap-3">
                        <button type="button"
                                wire:click="confirmReturn"
                                class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg focus:ring-4 focus:ring-[#102B70]/20 outline-none">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirm Return
                        </button>
                        
                        <a href="{{ route('admin.circulation-desk.return') }}" 
                           class="w-full h-12 border border-[#E2E8F0] hover:bg-slate-50 text-[#334155] rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all flex items-center justify-center gap-2 focus:ring-4 focus:ring-slate-100 outline-none">
                            <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
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
