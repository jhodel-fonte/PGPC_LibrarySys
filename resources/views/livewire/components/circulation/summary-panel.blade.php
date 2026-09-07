<div class="flex-1 lg:overflow-hidden">
    <!-- Return Summary Card (Flex-1) -->
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col h-auto lg:h-full lg:overflow-hidden">
        <!-- Return Summary Panel Contents (Top Section - shrink-0) -->
        <div class="flex flex-col gap-3 shrink-0">
            <div>
                <h3 class="text-xs font-bold text-[#334155] uppercase tracking-wider">Return Summary</h3>
            </div>

            <!-- Stats List -->
            <div class="flex flex-col gap-0.5 text-xs font-semibold">
                <div class="flex justify-between items-center py-2.5 border-b border-[#E2E8F0]">
                    <span class="text-[#475569]">Total Books to Return</span>
                    <span class="text-[#0F172A] font-bold tabular-nums">{{ $stats['total'] }}</span>
                </div>

                <div class="flex justify-between items-center py-2.5 border-b border-[#E2E8F0]">
                    <span class="text-[#475569]">Returned</span>
                    <span class="text-[#15803D] font-bold tabular-nums">{{ $stats['returned'] }}</span>
                </div>

                <!-- Highlighted Remaining Row if > 0 -->
                @if($stats['remaining'] > 0)
                    <div class="flex justify-between items-center py-2.5 border-b border-[#FEF3C7] bg-[#FFFBEB] px-2 -mx-2 rounded-lg transition-all">
                        <span class="text-[#D97706] font-bold">Remaining</span>
                        <span class="text-[#D97706] font-bold tabular-nums text-xs">{{ $stats['remaining'] }}</span>
                    </div>
                @else
                    <div class="flex justify-between items-center py-2.5 border-b border-[#E2E8F0]">
                        <span class="text-[#475569]">Remaining</span>
                        <span class="text-[#15803D] font-bold tabular-nums">{{ $stats['remaining'] }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center py-2.5 border-b border-[#E2E8F0]">
                    <span class="text-[#475569]">Overdue Items</span>
                    <span class="{{ $stats['overdue'] > 0 ? 'text-[#B91C1C]' : 'text-[#475569]' }} font-bold tabular-nums">{{ $stats['overdue'] }}</span>
                </div>

                @if(($stats['total_fine'] ?? 0) > 0)
                    <div class="flex justify-between items-center py-2.5 border-b border-[#FECACA] bg-[#FEF2F2] px-2 -mx-2 rounded-lg transition-all">
                        <span class="text-[#B91C1C] font-bold">Accumulated Fine</span>
                        <span class="text-[#B91C1C] font-bold tabular-nums">₱{{ number_format($stats['total_fine'], 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center py-2.5">
                    <span class="text-[#475569]">Return Date</span>
                    <div class="flex items-center gap-1.5 text-[#0F172A] font-bold tabular-nums">
                        <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $stats['return_date'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flexible Whitespace Spacer -->
        <div class="flex-1 min-h-[16px]"></div>

        <!-- Action Footer (Bottom Section - shrink-0) -->
        <div class="shrink-0 flex flex-col gap-3">
            @if(!$scannedMember)
                <!-- State when no member is scanned -->
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3 text-center">
                    <span class="text-xs text-[#475569] font-medium leading-normal">Load a member profile to start</span>
                </div>

                <button disabled class="w-full h-12 bg-[#F1F5F9] text-[#94A3B8] rounded-xl font-bold flex items-center justify-center cursor-not-allowed text-xs uppercase tracking-wider">
                    Waiting for Member
                </button>
            @elseif($stats['total'] == 0)
                <!-- Member has no borrows -->
                <div class="bg-[#EFF6FF] border border-[#DBEAFE] rounded-xl p-3 flex flex-col gap-0.5">
                    <span class="text-xs font-bold text-[#1E40AF] flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#1E40AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Account Clear
                    </span>
                    <span class="text-xs text-[#2563EB] leading-normal font-semibold">No active borrowed books on account</span>
                </div>

                <button disabled class="w-full h-12 bg-[#F1F5F9] text-[#94A3B8] rounded-xl font-bold flex items-center justify-center cursor-not-allowed text-xs uppercase tracking-wider">
                    No Action Required
                </button>
            @elseif($stats['returned'] == 0)
                <!-- State when member is loaded but no books have been scanned yet -->
                <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-3 text-center">
                    <span class="text-xs text-[#475569] font-medium leading-normal">Scan a book code to begin return</span>
                </div>

                <button disabled class="w-full h-12 bg-[#F1F5F9] text-[#94A3B8] rounded-xl font-bold flex items-center justify-center cursor-not-allowed text-xs uppercase tracking-wider">
                    Waiting for Scans
                </button>
            @elseif($stats['remaining'] > 0)
                <!-- Informational Partial Return State Card -->
                <div class="bg-[#FFFBEB] border border-[#FEF3C7] rounded-xl p-3 flex flex-col gap-0.5">
                    <span class="text-xs font-bold text-[#D97706] flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Partial Return
                    </span>
                    <span class="text-xs text-[#B45309] leading-normal font-semibold">{{ $stats['remaining'] }} {{ Str::plural('book', $stats['remaining']) }} will remain on the account</span>
                </div>

                <!-- Review Return Button -->
                <button type="button"
                        wire:click="$parent.reviewReturn"
                        wire:loading.attr="disabled"
                        wire:target="$parent.reviewReturn"
                        class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-colors shadow-2xs group disabled:opacity-65 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <span wire:loading.remove wire:target="$parent.reviewReturn" class="text-xs uppercase tracking-wider">Review Return</span>
                    <span wire:loading.flex wire:target="$parent.reviewReturn" class="text-xs uppercase tracking-wider items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                    <svg wire:loading.remove wire:target="$parent.reviewReturn" class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                <!-- Informational Complete Return State Card -->
                <div class="bg-[#DCFCE7] border border-[#BBF7D0] rounded-xl p-3 flex flex-col gap-0.5">
                    <span class="text-xs font-bold text-[#15803D] flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#15803D]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        All Books Returned
                    </span>
                    <span class="text-xs text-[#166534] leading-normal font-semibold">Ready to complete this transaction</span>
                </div>

                <!-- Confirm Return Button -->
                <button type="button"
                        wire:click="$parent.reviewReturn"
                        wire:loading.attr="disabled"
                        wire:target="$parent.reviewReturn"
                        class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-colors shadow-2xs group disabled:opacity-65 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <span wire:loading.remove wire:target="$parent.reviewReturn" class="text-xs uppercase tracking-wider">Confirm Return</span>
                    <span wire:loading.flex wire:target="$parent.reviewReturn" class="text-xs uppercase tracking-wider items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Completing...
                    </span>
                    <svg wire:loading.remove wire:target="$parent.reviewReturn" class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
