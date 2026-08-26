<div class="relative w-full h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-2 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-hidden">
    
    <!-- Lock page layout scroll to prevent entire page scrolling -->
    <style>
        main {
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 70px) !important;
        }
    </style>

    <div class="w-full max-w-[1460px] flex flex-col h-full overflow-hidden">
        <!-- Header Area (Shrink-0 to keep fixed height) -->
        <div class="w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">
            
            <!-- Top Row: Back Button & Tabs -->
            @include('livewire.components.circulation.circulation-tab')

            <!-- Title Row -->
            <div class="flex flex-col md:flex-row pt-2 justify-between items-start md:items-center">
                <div>
                    <h1 class="text-[1.35rem] font-bold text-[#102B70] tracking-tight leading-tight">
                        Check-In / Return
                    </h1>
                    <p class="mt-0.5 text-xs text-[#64748B] font-medium">
                        Scan or enter member ID or book barcode to process returns.
                    </p>
                </div>
                
                @if(session()->has('success_message'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="p-3 bg-[#F0FDF4] border border-[#BBF7D0] rounded-xl flex items-center gap-2 text-xs font-semibold text-[#15803D] transition-opacity duration-300">
                        <svg class="w-4 h-4 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ session('success_message') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content Grid (Three Column Workstation Grid Layout) -->
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10 mb-2 flex-1 overflow-hidden h-[calc(100%-80px)]">
            
            <!-- Left Section: Column 1 + Column 2 (col-span-9) -->
            <div class="lg:col-span-9 h-full flex flex-col gap-4 overflow-hidden">
                <div class="shrink-0 bg-white rounded-3xl border border-[#E2E8F0] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <livewire:components.circulation.qr-search-bar 
                        label="Member ID / Barcode"
                        placeholder="Enter or scan member ID or book barcode"
                    />

                    @if($errorMessage)
                        <div class="mt-3 p-3 bg-[#FEF2F2] border border-[#FCA5A5] rounded-xl flex items-center justify-between text-[11px] font-bold text-[#B91C1C] transition-all">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#EF4444] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>{{ $errorMessage }}</span>
                            </div>
                            <button type="button" wire:click="$set('errorMessage', '')" class="text-[#EF4444] hover:text-[#B91C1C] transition-colors focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Sub Grid: Column 1 & Column 2 side-by-side (flex-1 overflow-hidden) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 flex-1 overflow-hidden">
                    
                    <!-- COLUMN 1: Scanner + Scanned Book Details (md:col-span-5) -->
                    <div class="md:col-span-4 flex flex-col gap-4 h-full overflow-hidden">
                        <!-- Scanner Card -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0">
                            <livewire:components.circulation.live-camera />
                        </div>

                        <!-- Scanned Book Details Card (Flex-1) -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex-1 flex flex-col overflow-hidden">
                            <!-- Header -->
                            <div class="flex justify-between items-center shrink-0 mb-3">
                                <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Scanned Book</h3>
                                @if($lastReturnedBook)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                        Returned
                                    </span>
                                @endif
                            </div>

                            @if($lastReturnedBook)
                                <!-- Content -->
                                <div class="flex-1 overflow-y-auto flex flex-col gap-3 pr-1">
                                    <div class="flex gap-4">
                                        <!-- Book Cover -->
                                        <div class="w-12 h-16 bg-gradient-to-br from-[#102B70] to-[#F59E0B] rounded shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                            <span class="text-[9px] text-white/50 font-bold uppercase tracking-wider text-center px-1">{{ $lastReturnedBook['code'] }}</span>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <h4 class="font-extrabold text-[14px] leading-tight text-[#0F172A] truncate">{{ $lastReturnedBook['title'] }}</h4>
                                            <span class="text-[11px] text-[#64748B] font-semibold mt-0.5 truncate">{{ $lastReturnedBook['author'] }}</span>
                                            <span class="text-[10px] text-[#94A3B8] font-medium mt-1 truncate">{{ $lastReturnedBook['call_number'] }}</span>
                                        </div>
                                    </div>

                                    <!-- Metadata list -->
                                    <div class="flex flex-col gap-1.5 mt-1.5 text-xs font-semibold">
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[#64748B]">Accession No.</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['accession'] }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[#64748B]">Borrowed On</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['borrowed_on'] }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5">
                                            <span class="text-[#64748B]">Due Date</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['due_date'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Undo button (shrink-0) -->
                                <div class="mt-auto pt-3 shrink-0">
                                    <button wire:click="undoReturn('{{ $lastReturnedBook['accession'] }}')" class="w-full h-10 border border-[#FECACA] hover:bg-[#FEF2F2] text-[#EF4444] rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Undo Return
                                    </button>
                                </div>
                            @else
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-4 gap-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">No Book Scanned</span>
                                    <p class="text-[10px] text-[#94A3B8] max-w-[180px] leading-normal">Scan a book barcode to mark it as returned in this session.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- COLUMN 2: Borrowed Books (md:col-span-7) -->
                    <div class="md:col-span-8 bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] h-full flex flex-col overflow-hidden">
                        <div class="flex flex-col gap-3 h-full overflow-hidden">
                            <!-- Card Header (shrink-0) -->
                            <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider shrink-0">Borrowed Books ({{ count($borrowedBooks ?? []) }})</h3>
                            
                            <!-- Table Wrapper (flex-1 overflow-y-auto) -->
                            <div class="overflow-x-auto flex-1 overflow-y-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner">
                                <table class="w-full text-left border-collapse min-w-[600px]">
                                    <thead class="sticky top-0 bg-white z-10">
                                        <tr class="border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider">
                                            <th class="py-2.5 px-4 w-[45%] bg-white">Book</th>
                                            <th class="py-2.5 px-4 w-[20%] bg-white">Borrowed On</th>
                                            <th class="py-2.5 px-4. w-[20%] bg-white">Due Date</th>
                                            <th class="py-2.5 px-4 w-[15%] text-right bg-white">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#F1F5F9] text-xs text-[#0F172A]">
                                        @forelse($borrowedBooks ?? [] as $item)
                                            @php
                                                $isReturned = ($item['status'] ?? '') === 'Returned';
                                            @endphp
                                            <tr class="align-middle transition-colors {{ $isReturned ? 'bg-[#EFF6FF]/30 hover:bg-[#EFF6FF]/55' : 'hover:bg-[#F8FAFC]' }}">
                                                <td class="py-3 px-4">
                                                    <div class="flex items-center gap-3">
                                                        <!-- Miniature Cover representation -->
                                                        <div class="w-8 h-11 bg-gradient-to-br {{ $isReturned ? 'from-[#102B70] to-[#3B82F6]' : 'from-[#64748B] to-[#94A3B8]' }} rounded shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                                            <span class="text-[8px] text-white/50 font-bold uppercase tracking-wider text-center px-1">{{ $item['code'] ?? 'BOOK' }}</span>
                                                        </div>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="font-bold text-[13px] leading-tight text-[#0F172A] truncate {{ $isReturned ? 'text-[#102B70]' : '' }}">{{ $item['book'] }}</span>
                                                            <span class="text-[11px] text-[#64748B] mt-0.5 truncate">{{ $item['author'] ?? 'Unknown Author' }}</span>
                                                            <span class="text-[10px] text-[#94A3B8] mt-0.5">Accession No. {{ $item['accession'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-[#64748B]">
                                                    {{ $item['borrowed_on'] }}
                                                </td>
                                                <td class="py-3 px-4 font-semibold text-[#64748B]">
                                                    {{ $item['due_date'] }}
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    @if($isReturned)
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#10B981]">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Returned
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center text-[11px] font-semibold text-[#EF4444]">
                                                            Not Returned
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-12 text-center text-xs text-[#64748B] font-medium leading-relaxed bg-[#F8FAFC]/30">
                                                    No active borrowed books on account.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Right Section: Column 3 (col-span-3 Return Summary Panel) -->
            <div class="lg:col-span-3 h-full flex flex-col gap-4 overflow-hidden">
                <!-- Current Member Card -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0">
                    <div class="flex flex-col gap-2.5">
                        <span class="block text-xs font-bold text-[#64748B] uppercase tracking-wider">Current Member</span>
                        
                        @if($scannedMember)
                            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl p-3 flex flex-col gap-3 shadow-inner">
                                <div class="flex items-center gap-3">
                                    <!-- Profile Avatar -->
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 border border-[#E2E8F0] shadow-sm text-[#102B70]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[#0F172A] font-bold text-[14px] leading-tight truncate">{{ $scannedMember['name'] }}</h3>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[8px] font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                                {{ $scannedMember['status'] }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-[#64748B] font-medium mt-0.5">
                                            <span>{{ $scannedMember['school_id'] }}</span>
                                            <span class="mx-1 text-slate-300">&bull;</span>
                                            <span>{{ $scannedMember['course'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Aligned Action Button -->
                                <div class="flex justify-end">
                                    <button type="button" 
                                            wire:click="clearMember" 
                                            class="h-8 px-3 border border-[#CBD5E1] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A] rounded-xl text-[11px] font-bold transition-all flex items-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Change Member
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="bg-[#F8FAFC] border border-dashed border-[#CBD5E1] rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">No Member Loaded</span>
                                <p class="text-[10px] text-[#94A3B8] max-w-[180px] leading-normal">Scan or enter a member ID number to load their profile and books.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Return Summary Card (Flex-1) -->
                <div class="flex-1 overflow-hidden">
                    <div class="bg-white rounded-3xl border border-[#E2E8F0] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col h-full overflow-hidden">
                        <!-- Return Summary Panel Contents (Top Section - shrink-0) -->
                        <div class="flex flex-col gap-3 shrink-0">
                            <div>
                                <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Return Summary</h3>
                            </div>

                            <!-- Stats List -->
                            <div class="flex flex-col gap-0.5 text-sm font-semibold">
                                <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                                    <span class="text-[#64748B]">Total Books to Return</span>
                                    <span class="text-[#0F172A] font-bold">{{ $stats['total'] }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                                    <span class="text-[#64748B]">Returned</span>
                                    <span class="text-[#10B981] font-bold">{{ $stats['returned'] }}</span>
                                </div>
                                
                                <!-- Highlighted Remaining Row if > 0 -->
                                @if($stats['remaining'] > 0)
                                    <div class="flex justify-between items-center py-2.5 border-b border-[#FFEDD5] bg-[#FFFBEB] px-2 -mx-2 rounded-md transition-all">
                                        <span class="text-[#D97706] font-bold">Remaining</span>
                                        <span class="text-[#D97706] font-extrabold text-[14px]">{{ $stats['remaining'] }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                                        <span class="text-[#64748B]">Remaining</span>
                                        <span class="text-[#10B981] font-bold">{{ $stats['remaining'] }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                                    <span class="text-[#64748B]">Overdue Items</span>
                                    <span class="text-[#EF4444] font-bold">{{ $stats['overdue'] }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center py-2.5">
                                    <span class="text-[#64748B]">Return Date</span>
                                    <div class="flex items-center gap-1.5 text-[#0F172A] font-bold">
                                        <svg class="w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ $stats['return_date'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Flexible Whitespace Spacer (Middle spacer that expands dynamically) -->
                        <div class="flex-1 min-h-[20px]"></div>

                        <!-- Action Footer (Bottom Section - shrink-0) -->
                        <div class="shrink-0 flex flex-col gap-3">
                            @if(!$scannedMember)
                                <!-- State when no member is scanned -->
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex flex-col gap-0.5 text-center items-center justify-center">
                                    <span class="text-[11px] text-[#64748B] font-semibold leading-normal">Load a member profile to start</span>
                                </div>
                                
                                <button disabled class="w-full h-12 bg-slate-100 text-slate-400 rounded-xl font-bold flex items-center justify-center cursor-not-allowed text-xs uppercase tracking-wider">
                                    Waiting for Member
                                </button>
                            @elseif($stats['total'] == 0)
                                <!-- Member has no borrows -->
                                <div class="bg-[#EFF6FF] border border-[#DBEAFE] rounded-xl p-3 flex flex-col gap-0.5">
                                    <span class="text-xs font-bold text-[#1E40AF] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#1E40AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Account Clear
                                    </span>
                                    <span class="text-[11px] text-[#2563EB] leading-normal font-semibold">No active borrowed books on account</span>
                                </div>
                                
                                <button disabled class="w-full h-12 bg-slate-100 text-slate-400 rounded-xl font-bold flex items-center justify-center cursor-not-allowed text-xs uppercase tracking-wider">
                                    No Action Required
                                </button>
                            @elseif($stats['remaining'] > 0)
                                <!-- Informational Partial Return State Card -->
                                <div class="bg-[#FFF7ED] border border-[#FFEDD5] rounded-xl p-3 flex flex-col gap-0.5">
                                    <span class="text-xs font-bold text-[#C2410C] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Partial Return
                                    </span>
                                    <span class="text-[11px] text-[#EA580C] leading-normal font-semibold">{{ $stats['remaining'] }} {{ Str::plural('book', $stats['remaining']) }} will remain on the account</span>
                                </div>

                                 <!-- Review Return Button -->
                                <button type="button" 
                                        wire:click="reviewReturn" 
                                        class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-colors shadow-sm group">
                                    <span class="text-xs uppercase tracking-wider">Review Return</span>
                                    <svg class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            @else
                                <!-- Informational Complete Return State Card -->
                                <div class="bg-[#EFF6FF] border border-[#DBEAFE] rounded-xl p-3 flex flex-col gap-0.5">
                                    <span class="text-xs font-bold text-[#1E40AF] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#1E40AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        All Books Returned
                                    </span>
                                    <span class="text-[11px] text-[#2563EB] leading-normal font-semibold">Ready to complete this transaction</span>
                                </div>

                                <!-- Confirm Return Button -->
                                <button type="button" 
                                        wire:click="reviewReturn" 
                                        class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-colors shadow-sm group">
                                    <span class="text-xs uppercase tracking-wider">Confirm Return</span>
                                    <svg class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
    </div>

    <!-- Search Entity Modal -->
    <livewire:components.circulation.search-entity-modal />
</div>
