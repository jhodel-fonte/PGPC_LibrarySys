<div class="flex flex-col gap-2.5">
    <span class="block text-xs font-bold text-[#64748B] uppercase tracking-wider">Current Member</span>
    
    @if($member)
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
                        <h3 class="text-[#0F172A] font-bold text-[14px] leading-tight truncate">{{ $member['name'] }}</h3>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[8px] font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                            {{ $member['status'] }}
                        </span>
                    </div>
                    <div class="text-[11px] text-[#64748B] font-medium mt-0.5">
                        <span>{{ $member['school_id'] }}</span>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <span>{{ $member['course'] }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Aligned Action Button -->
            <div class="flex justify-end">
                <button type="button" 
                        wire:click="$parent.clearMember" 
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