<div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col gap-3">
    <div class="flex items-center justify-between shrink-0">
        <h3 class="text-xs font-bold text-[#334155] uppercase tracking-wider">Member Profile</h3>
    </div>

    @if($member)
        @php
            $initials = '';
            if (!empty($member['name'])) {
                $words = explode(' ', trim($member['name']));
                $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
            }
            $statusStr = strtolower($member['status'] ?? 'active');
            $isStatusActive = $statusStr === 'active';
        @endphp
        <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <!-- Branded Initials Avatar -->
                <div class="w-10 h-10 rounded-full bg-[#102B70] text-white flex items-center justify-center shrink-0 text-xs font-bold tracking-wider select-none shadow-2xs">
                    {{ $initials ?: 'M' }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-[#0F172A] font-bold text-sm leading-snug truncate" title="{{ $member['name'] }}">{{ $member['name'] }}</h4>
                        @if($isStatusActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]">
                                {{ $member['status'] ?? 'Inactive' }}
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-[#475569] font-medium mt-1 flex items-center flex-wrap gap-1.5">
                        <span class="font-semibold text-[#0F172A] tabular-nums">{{ $member['school_id'] }}</span>
                        <span class="text-[#CBD5E1]" aria-hidden="true">&bull;</span>
                        <span class="truncate">{{ $member['course'] }}</span>
                    </div>
                    @if(!empty($member['email']) && $member['email'] !== 'N/A')
                        <div class="text-xs text-[#64748B] mt-0.5 truncate">{{ $member['email'] }}</div>
                    @endif
                </div>
            </div>

            <!-- Change Member Action Area -->
            <div class="mt-1 pt-3 border-t border-[#E2E8F0] flex justify-end" x-data="{ confirmChange: false }">
                <button x-show="!confirmChange"
                        type="button"
                        @click="confirmChange = true"
                        class="h-8 px-3 border border-[#102B70] bg-white text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#102B70]/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Change Member
                </button>

                <div x-show="confirmChange" class="flex items-center gap-1.5" style="display: none;">
                    <span class="text-xs font-bold text-[#B91C1C] mr-1 select-none">Sure?</span>
                    <button type="button"
                            @click="confirmChange = false"
                            class="h-8 px-2.5 border border-[#CBD5E1] bg-white text-[#475569] hover:bg-slate-50 hover:text-[#0F172A] rounded-xl text-xs font-bold transition-all shadow-2xs">
                        Cancel
                    </button>
                    <button type="button"
                            wire:click="$parent.clearMember"
                            @click="confirmChange = false"
                            class="h-8 px-3 bg-[#B91C1C] hover:bg-[#991B1B] text-white rounded-xl text-xs font-bold transition-all shadow-2xs">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-[#F8FAFC] border border-dashed border-[#CBD5E1] rounded-xl p-6 flex flex-col items-center justify-center text-center gap-2">
            <div class="w-10 h-10 rounded-full bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="text-xs font-bold text-[#334155] uppercase tracking-wider">No Member Loaded</span>
            <p class="text-xs text-[#64748B] max-w-[200px] leading-relaxed">Scan or enter a member ID number to load student profile.</p>
        </div>
    @endif
</div>
