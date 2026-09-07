<div wire:loading.class="opacity-65 transition-opacity duration-300" class="{{ $class }} bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col lg:overflow-hidden">
<div wire:loading.class="opacity-65 transition-opacity duration-300" class="{{ $class }} bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col lg:overflow-hidden">
    <div class="flex flex-col gap-3 h-full overflow-hidden">
        <!-- Card Header (shrink-0) -->
        <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider shrink-0">{{ $displayTitle }} ({{ count($borrowedBooks ?? []) }})</h3>
        <div class="flex justify-between items-center shrink-0">
            <h3 class="text-xs font-bold text-[#334155] uppercase tracking-wider">{{ $displayTitle }} ({{ count($borrowedBooks ?? []) }})</h3>
        </div>

        <!-- Table Wrapper (flex-1 overflow-y-auto) -->
        <div class="overflow-x-auto flex-1 overflow-y-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b border-[#E2E8F0] text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                        <th class="py-3 px-5 w-[40%] bg-white">Book</th>
                        <th class="py-3 px-5 w-[20%] bg-white">{{ $mode === 'check-out' ? 'Added On' : 'Borrowed On' }}</th>
                        <th class="py-3 px-5 w-[20%] bg-white">Due Date</th>
                        <th class="py-3 px-5 w-[20%] text-right bg-white">{{ $mode === 'check-out' ? 'Action' : 'Status' }}</th>
        <div class="overflow-x-auto flex-1 overflow-y-auto border border-[#E2E8F0] rounded-xl bg-white custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[580px]">
                <thead class="sticky top-0 bg-[#F8FAFC] z-10 border-b border-[#E2E8F0]">
                    <tr class="text-xs font-bold text-[#475569] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 w-[42%]">Book</th>
                        <th scope="col" class="py-3 px-4 w-[20%]">{{ $mode === 'check-out' ? 'Added On' : 'Borrowed On' }}</th>
                        <th scope="col" class="py-3 px-4 w-[20%]">Due Date</th>
                        <th scope="col" class="py-3 px-4 w-[18%] text-right">{{ $mode === 'check-out' ? 'Action' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9] text-sm text-[#0F172A]">
                <tbody class="divide-y divide-[#E2E8F0] text-xs text-[#0F172A]">
                    @forelse($borrowedBooks ?? [] as $item)
                        @php
                            $isReturned = ($item['status'] ?? '') === 'Returned';
                            $isOverdue = !empty($item['is_overdue']) || !empty($item['overdue_days']);
                        @endphp
                        <tr class="align-middle transition-colors {{ $isReturned ? 'bg-[#EFF6FF]/30 hover:bg-[#EFF6FF]/55' : 'hover:bg-[#F8FAFC]' }}">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3.5">
                                    <!-- Miniature Cover representation -->
                                    <div class="w-10 h-14 bg-gradient-to-br {{ $isReturned ? 'from-[#102B70] to-[#3B82F6]' : 'from-[#64748B] to-[#94A3B8]' }} rounded-md shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                        <span class="text-[9px] text-white/50 font-bold uppercase tracking-wider text-center px-1 leading-tight">{{ $item['code'] ?? 'BOOK' }}</span>
                        <tr class="align-middle transition-colors {{ $isReturned ? 'bg-[#EFF6FF]/40 hover:bg-[#EFF6FF]/60' : 'hover:bg-[#F8FAFC]' }}">
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-3">
                                    <!-- Miniature Book Icon Tile -->
                                    <div class="w-9 h-12 rounded-lg {{ $isReturned ? 'bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]' : 'bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0]' }} flex flex-col items-center justify-center shrink-0 shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-[14.5px] leading-tight text-[#0F172A] truncate {{ $isReturned ? 'text-[#102B70]' : '' }}">{{ $item['book'] }}</span>
                                        <span class="text-[12.5px] text-[#64748B] mt-0.5 truncate">{{ $item['author'] ?? 'Unknown Author' }}</span>
                                        <span class="text-[11px] text-[#94A3B8] mt-0.5">Accession No. {{ $item['accession'] ?? 'N/A' }}</span>
                                        <span class="font-bold text-sm text-[#0F172A] leading-snug truncate" title="{{ $item['book'] }}">{{ $item['book'] }}</span>
                                        <span class="text-xs text-[#475569] mt-0.5 truncate">{{ $item['author'] ?? 'Unknown Author' }}</span>
                                        <span class="text-xs text-[#64748B] tabular-nums mt-0.5">Acc. No: <span class="font-mono text-[#0F172A] font-semibold">{{ $item['accession'] ?? 'N/A' }}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-[#64748B]">
                                {{ $item['borrowed_on'] ?? $item['added_on'] ?? now()->format('Y-m-d') }}
                            <td class="py-3.5 px-4 font-semibold text-[#475569] tabular-nums">
                                {{ $item['borrowed_on'] ?? $item['added_on'] ?? now()->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-5 font-semibold text-[#64748B]">
                            <td class="py-3.5 px-4 font-semibold text-[#475569] tabular-nums">
                                {{ $item['due_date'] ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                            <td class="py-3.5 px-4 text-right">
                                @if($mode === 'check-out')
                                    <button type="button"
                                            @click="$dispatch('remove-checkout-book', { accession: '{{ $item['accession'] ?? '' }}' })"
                                            class="h-7 px-3 border border-[#FECACA] hover:bg-[#FEF2F2] text-[#EF4444] rounded-lg text-xs font-bold transition-all shadow-sm">
                                            class="h-8 px-3 border border-[#FECACA] bg-white hover:bg-[#FEF2F2] text-[#B91C1C] rounded-lg text-xs font-bold transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20"
                                            aria-label="Remove {{ $item['book'] }} from checkout">
                                        Remove
                                    </button>
                                @else
                                    @if($isReturned)
                                        <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#10B981]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Returned
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                            Overdue{{ !empty($item['overdue_days']) ? ' · ' . $item['overdue_days'] . ' ' . Str::plural('day', $item['overdue_days']) : '' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[12px] font-semibold text-[#EF4444]">
                                            Not Returned
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0]">
                                            Active Loan
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-sm text-[#64748B] font-medium leading-relaxed bg-[#F8FAFC]/30">
                            <td colspan="4" class="py-14 text-center text-xs text-[#64748B] font-medium leading-relaxed bg-[#F8FAFC]">
                                {{ $mode === 'check-out' ? 'No books added to checkout queue.' : 'No active borrowed books on account.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
