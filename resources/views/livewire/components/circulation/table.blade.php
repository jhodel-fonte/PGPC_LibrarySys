<div wire:loading.class="opacity-65 transition-opacity duration-300" class="{{ $class }} bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col lg:overflow-hidden">
    <div class="flex flex-col gap-3 h-full overflow-hidden">
        <!-- Card Header (shrink-0) -->
        <div class="flex justify-between items-center shrink-0">
            <h3 class="text-xs font-bold text-[#334155] uppercase tracking-wider">{{ $displayTitle }} ({{ count($borrowedBooks ?? []) }})</h3>
        </div>

        <!-- Table Wrapper (flex-1 overflow-y-auto) -->
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
                <tbody class="divide-y divide-[#E2E8F0] text-xs text-[#0F172A]">
                    @forelse($borrowedBooks ?? [] as $item)
                        @php
                            $isReturned = ($item['status'] ?? '') === 'Returned';
                            $isOverdue = !empty($item['is_overdue']) || !empty($item['overdue_days']);
                            $isSelected = ($selectedAccession ?? null) === ($item['accession'] ?? null);
                        @endphp
                        <tr @if($mode === 'check-in')
                                @click="$dispatch('select-table-book', { accession: '{{ $item['accession'] ?? '' }}' })"
                            @endif
                            class="align-middle transition-colors {{ $mode === 'check-in' ? 'cursor-pointer' : '' }} {{ $isSelected ? 'bg-[#EFF6FF] ring-1 ring-inset ring-[#102B70]/30 shadow-2xs' : ($isReturned ? 'bg-[#EFF6FF]/40 hover:bg-[#EFF6FF]/60' : 'hover:bg-[#F8FAFC]') }}"
                        >
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-sm text-[#0F172A] leading-snug truncate" title="{{ $item['book'] }}">{{ $item['book'] }}</span>
                                        <span class="text-xs text-[#475569] mt-0.5 truncate">{{ $item['author'] ?? 'Unknown Author' }}</span>
                                        <span class="text-xs text-[#64748B] tabular-nums mt-0.5">Accession Number: <span class="font-mono text-[#0F172A] font-semibold">{{ $item['accession'] ?? 'N/A' }}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-[#475569] tabular-nums">
                                {{ $item['borrowed_on'] ?? $item['added_on'] ?? now()->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-[#475569] tabular-nums">
                                {{ $item['due_date'] ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($mode === 'check-out')
                                    <button type="button"
                                            @click.stop="$dispatch('remove-checkout-book', { accession: '{{ $item['accession'] ?? '' }}' })"
                                            class="h-8 px-3 border border-[#FECACA] bg-white hover:bg-[#FEF2F2] text-[#B91C1C] rounded-lg text-xs font-bold transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20"
                                            aria-label="Remove {{ $item['book'] }} from checkout">
                                        Remove
                                    </button>
                                @else
                                    @if($isReturned)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Returned
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA] rounded-md">
                                            Overdue{{ !empty($item['overdue_days']) ? ' · ' . $item['overdue_days'] . ' ' . Str::plural('day', $item['overdue_days']) : '' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-[#475569] bg-slate-100 rounded-md">
                                            Active Loan
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
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
