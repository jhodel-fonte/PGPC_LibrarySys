<div class="bg-white rounded-3xl border border-[#E2E8F0] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex-1 flex flex-col h-auto lg:h-full lg:overflow-hidden">
    <div class="flex flex-col gap-3 shrink-0">
        <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Checkout Summary</h3>

        <div class="flex flex-col gap-0.5 text-sm font-semibold">
            <!-- Books Count Row -->
            <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                <span class="text-[#64748B]">Total Books to Issue</span>
                <span class="text-[#0F172A] font-bold">{{ $stats['total'] }}</span>
            </div>

            <!-- Remaining Slots Row -->
            @if($scannedMember)
                <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9] {{ $stats['borrow_limit_reached'] ? 'bg-[#FEF2F2] px-2 -mx-2 rounded-md' : '' }}">
                    <span class="{{ $stats['borrow_limit_reached'] ? 'text-[#B91C1C]' : 'text-[#64748B]' }}">Remaining Slots</span>
                    <span class="{{ $stats['borrow_limit_reached'] ? 'text-[#EF4444]' : 'text-[#102B70]' }} font-bold">
                        {{ $stats['remaining_slots'] }} / 3
                    </span>
                </div>

                <!-- Warnings indicator inside Summary Panel -->
                @if($stats['overdue_count'] > 0)
                    <div class="flex justify-between items-center py-2.5 border-b border-[#FEF2F2] bg-[#FEF2F2] px-2 -mx-2 rounded-md transition-all">
                        <span class="text-[#B91C1C] font-bold">Overdue Books</span>
                        <span class="text-[#EF4444] font-extrabold">{{ $stats['overdue_count'] }}</span>
                    </div>
                @endif

                @if($stats['unpaid_fines'] > 0)
                    <div class="flex justify-between items-center py-2.5 border-b border-[#FEF2F2] bg-[#FEF2F2] px-2 -mx-2 rounded-md transition-all">
                        <span class="text-[#B91C1C] font-bold">Outstanding Fines</span>
                        <span class="text-[#EF4444] font-extrabold">₱{{ number_format($stats['unpaid_fines'], 2) }}</span>
                    </div>
                @endif
            @endif

            <!-- Due Date Row -->
            <div class="flex justify-between items-center py-2.5">
                <span class="text-[#64748B]">Due Date</span>
                <div class="flex items-center gap-1.5 text-[#0F172A] font-bold">
                    <svg class="w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $stats['due_date'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-[20px]"></div>

    <!-- Checkout Button block -->
    <div class="shrink-0 pt-3 border-t border-[#F1F5F9]">
        @php
            $cannotCheckout = !$scannedMember || empty($checkoutBooks) ||
                              ($stats['overdue_count'] ?? 0) > 0 ||
                              ($stats['unpaid_fines'] ?? 0) > 0 ||
                              (strtolower($scannedMember['status'] ?? 'active') !== 'active');
        @endphp
        <button type="button"
                wire:click="$parent.confirmCheckout"
                wire:loading.attr="disabled"
                wire:target="$parent.confirmCheckout"
                @if($cannotCheckout) disabled @endif
                class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-all shadow-sm group disabled:opacity-65 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="$parent.confirmCheckout" class="text-xs uppercase tracking-wider">Confirm Check-Out</span>
            <span wire:loading.flex wire:target="$parent.confirmCheckout" class="text-xs uppercase tracking-wider items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Completing...
            </span>
            <svg wire:loading.remove wire:target="$parent.confirmCheckout" class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
</div>
