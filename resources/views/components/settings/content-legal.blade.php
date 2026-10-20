<div class="w-full p-6 lg:p-8" x-data="{ 
    updateModalOpen: false, 
    policyTypeToUpdate: '',
    updateMode: 'immediate',
    scheduledDate: '',
    
    openUpdateModal(type) {
        this.policyTypeToUpdate = type;
        this.updateMode = 'immediate';
        this.scheduledDate = '';
        this.updateModalOpen = true;
    },
    
    confirmUpdate() {
        if (this.updateMode === 'schedule' && !this.scheduledDate) {
            alert('Please select a date');
            return;
        }
        
        const requireAck = document.getElementById('require_ack_' + this.policyTypeToUpdate)?.checked ?? false;
        
        $wire.updatePolicy(this.policyTypeToUpdate, this.updateMode === 'immediate', requireAck, this.scheduledDate);
        this.updateModalOpen = false;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#E2E8F0] pb-5 gap-3">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">Content & Legal Management</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Manage institutional policy drafts and member terms displayed across the portal.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('terms') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-[#102B70] bg-[#EFF6FF] border border-[#BFDBFE] hover:bg-[#DBEAFE] transition-colors shadow-2xs">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Preview Public Terms</span>
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                Compliance Docs
            </span>
        </div>
    </div>
    
    <div class="max-w-[960px] w-full">
        @foreach([
            'terms' => ['title' => 'Terms and Conditions', 'desc' => 'Legal terms governing library membership, borrowing privileges, and facility usage.'],
            'privacy' => ['title' => 'Data Privacy Policy', 'desc' => 'Information describing how patron and circulation data is collected, stored, and protected under RA 10173.'],
            'cookie' => ['title' => 'Cookie & Session Policy', 'desc' => 'Guidelines on session tokens, preferences, and cookie persistence in the PGPC Library System.']
        ] as $type => $info)
        
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">{{ $info['title'] }}</h3>
                        <p class="mt-0.5 text-xs text-[#64748B]">{{ $info['desc'] }}</p>
                    </div>
                    @if($this->dirtyState['sections']["content_legal.{$type}"] ?? false)
                        <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Modified
                        </div>
                    @endif
                </div>
                <div class="flex flex-col sm:items-end text-xs text-[#64748B]">
                    <span class="font-bold text-[#102B70]">Version {{ $settings['content_legal'][$type]['version'] ?? '1.0' }}</span>
                    <span>Updated {{ \Carbon\Carbon::parse($settings['content_legal'][$type]['updated_at'] ?? now())->format('M d, Y') }}</span>
                </div>
            </div>
            
            <div class="border border-[#E2E8F0] rounded-xl bg-white overflow-hidden w-full focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF] transition-all shadow-sm">
                <!-- Rich Editor Simulated Toolbar -->
                <div class="flex items-center gap-1 px-3 py-2 border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <button type="button" class="p-1.5 text-[#334155] hover:bg-slate-200 rounded-lg transition-colors" title="Bold"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg></button>
                    <button type="button" class="p-1.5 text-[#334155] hover:bg-slate-200 rounded-lg transition-colors" title="Italic"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg></button>
                    <button type="button" class="p-1.5 text-[#334155] hover:bg-slate-200 rounded-lg transition-colors" title="Underline"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg></button>
                    <div class="h-4 w-px bg-slate-300 mx-1"></div>
                    <span class="text-[11px] text-[#64748B] font-medium pl-1">Drafting document content</span>
                </div>
                
                <textarea wire:model.live="settings.content_legal.{{ $type }}.content" rows="6" class="w-full p-4 text-sm text-[#0F172A] font-normal bg-transparent resize-y outline-none leading-relaxed" placeholder="Type policy wording here..."></textarea>
                
                <div class="bg-[#F8FAFC] border-t border-[#E2E8F0] px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" id="require_ack_{{ $type }}" wire:model.live="settings.content_legal.{{ $type }}.require_acknowledgement" class="h-4 w-4 rounded border-slate-300 text-[#102B70] focus:ring-[#102B70] cursor-pointer">
                        <span class="text-xs font-bold text-[#334155]">Require users to acknowledge this version upon next sign-in</span>
                    </label>
                    
                    <button type="button" @click="openUpdateModal('{{ $type }}')" class="inline-flex justify-center items-center py-2 px-4 rounded-xl text-xs font-bold text-white bg-[#102B70] hover:bg-[#0B225E] shadow-sm transition-all whitespace-nowrap cursor-pointer">
                        Update Policy Version
                    </button>
                </div>
            </div>
        </div>
        
        @if(!$loop->last)
            <hr class="my-8 border-[#E2E8F0]">
        @endif
        
        @endforeach
    </div>

    <!-- Update Policy Modal -->
    <div x-show="updateModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <!-- Background overlay -->
            <div x-show="updateModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" aria-hidden="true" @click="updateModalOpen = false"></div>

            <!-- Modal panel -->
            <div x-show="updateModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-[#E2E8F0]">
                <div class="bg-white p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-xl bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="w-full">
                            <h3 class="text-base font-bold text-[#102B70]" id="modal-title">
                                Update Policy Configuration
                            </h3>
                            <p class="text-xs text-[#64748B] mt-0.5">
                                Select when this updated policy should take effect. This will increment the policy revision.
                            </p>
                            
                            <div class="mt-4 space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer p-3.5 border rounded-xl transition-all" :class="updateMode === 'immediate' ? 'border-[#102B70] bg-[#EFF6FF]/60' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'">
                                    <input type="radio" x-model="updateMode" value="immediate" class="mt-1 h-4 w-4 text-[#102B70] focus:ring-[#102B70]">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-[#0F172A]">Apply Immediately</span>
                                        <span class="text-[11px] text-[#64748B]">The new policy revision will become active immediately across all user portals.</span>
                                    </div>
                                </label>
                                
                                <label class="flex items-start gap-3 cursor-pointer p-3.5 border rounded-xl transition-all" :class="updateMode === 'schedule' ? 'border-[#102B70] bg-[#EFF6FF]/60' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'">
                                    <input type="radio" x-model="updateMode" value="schedule" class="mt-1 h-4 w-4 text-[#102B70] focus:ring-[#102B70]">
                                    <div class="flex flex-col w-full">
                                        <span class="text-xs font-bold text-[#0F172A]">Schedule Update</span>
                                        <span class="text-[11px] text-[#64748B]">The policy will be queued and automatically published at a specified timestamp.</span>
                                        
                                        <div x-show="updateMode === 'schedule'" class="mt-3">
                                            <input type="datetime-local" x-model="scheduledDate" class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-medium text-[#0F172A] focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] outline-none">
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-[#F8FAFC] px-6 py-4 flex flex-col sm:flex-row-reverse gap-2 border-t border-[#E2E8F0]">
                    <button type="button" @click="confirmUpdate" class="w-full sm:w-auto inline-flex justify-center rounded-xl px-5 py-2.5 bg-[#102B70] text-xs font-bold text-white hover:bg-[#0B225E] shadow-sm transition-all cursor-pointer">
                        Confirm Update
                    </button>
                    <button type="button" @click="updateModalOpen = false" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-[#E2E8F0] px-4 py-2.5 bg-white text-xs font-bold text-[#334155] hover:bg-[#F8FAFC] transition-all cursor-pointer">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
