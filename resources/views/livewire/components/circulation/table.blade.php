<div wire:loading.class="opacity-65 transition-opacity duration-300" class="{{ $class }} bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col lg:overflow-hidden">
    <div class="flex flex-col gap-3 h-full overflow-hidden">
        <!-- Card Header (shrink-0) -->
        <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider shrink-0">{{ $displayTitle }} ({{ count($borrowedBooks ?? []) }})</h3>

        <!-- Table Wrapper (flex-1 overflow-y-auto) -->
        <div class="overflow-x-auto flex-1 overflow-y-auto border border-[#E2E8F0] rounded-2xl bg-white shadow-inner">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b border-[#E2E8F0] text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                        <th class="py-3 px-5 w-[40%] bg-white">Book</th>
                        <th class="py-3 px-5 w-[20%] bg-white">{{ $mode === 'check-out' ? 'Added On' : 'Borrowed On' }}</th>
                        <th class="py-3 px-5 w-[20%] bg-white">Due Date</th>
                        <th class="py-3 px-5 w-[20%] text-right bg-white">{{ $mode === 'check-out' ? 'Action' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9] text-sm text-[#0F172A]">
                    @forelse($borrowedBooks ?? [] as $item)
                        @php
                            $isReturned = ($item['status'] ?? '') === 'Returned';
                        @endphp
                        <tr class="align-middle transition-colors {{ $isReturned ? 'bg-[#EFF6FF]/30 hover:bg-[#EFF6FF]/55' : 'hover:bg-[#F8FAFC]' }}">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3.5">
                                    <!-- Miniature Cover representation -->
                                    <div class="w-10 h-14 bg-gradient-to-br {{ $isReturned ? 'from-[#102B70] to-[#3B82F6]' : 'from-[#64748B] to-[#94A3B8]' }} rounded-md shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                        <span class="text-[9px] text-white/50 font-bold uppercase tracking-wider text-center px-1 leading-tight">{{ $item['code'] ?? 'BOOK' }}</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-[14.5px] leading-tight text-[#0F172A] truncate {{ $isReturned ? 'text-[#102B70]' : '' }}">{{ $item['book'] }}</span>
                                        <span class="text-[12.5px] text-[#64748B] mt-0.5 truncate">{{ $item['author'] ?? 'Unknown Author' }}</span>
                                        <span class="text-[11px] text-[#94A3B8] mt-0.5">Accession No. {{ $item['accession'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-semibold text-[#64748B]">
                                {{ $item['borrowed_on'] ?? $item['added_on'] ?? now()->format('Y-m-d') }}
                            </td>
                            <td class="py-4 px-5 font-semibold text-[#64748B]">
                                {{ $item['due_date'] ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                @if($mode === 'check-out')
                                    <button type="button"
                                            @click="$dispatch('remove-checkout-book', { accession: '{{ $item['accession'] ?? '' }}' })"
                                            class="h-7 px-3 border border-[#FECACA] hover:bg-[#FEF2F2] text-[#EF4444] rounded-lg text-xs font-bold transition-all shadow-sm">
                                        Remove
                                    </button>
                                @else
                                    @if($isReturned)
                                        <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#10B981]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Returned
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[12px] font-semibold text-[#EF4444]">
                                            Not Returned
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-sm text-[#64748B] font-medium leading-relaxed bg-[#F8FAFC]/30">
                                {{ $mode === 'check-out' ? 'No books added to checkout queue.' : 'No active borrowed books on account.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
