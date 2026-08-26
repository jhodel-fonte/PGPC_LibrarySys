<div class="flex flex-col gap-3 h-full overflow-hidden">
    <!-- Card Header (shrink-0) -->
    <h3 class="text-xs font-bold text-[#64748B] uppercase tracking-wider shrink-0">Borrowed Books ({{ count($books ?? []) }})</h3>
    
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
                @forelse($books ?? [] as $item)
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
