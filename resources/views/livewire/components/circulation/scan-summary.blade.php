@php
    $stats = $stats ?? [
        'total' => 0,
        'returned' => 0,
        'remaining' => 0,
        'overdue' => 0,
        'return_date' => date('M d, Y')
    ];
@endphp
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
        @if(!$member)
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
                    wire:click="$parent.reviewReturn" 
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
                    wire:click="$parent.reviewReturn" 
                    class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-colors shadow-sm group">
                <span class="text-xs uppercase tracking-wider">Confirm Return</span>
                <svg class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
