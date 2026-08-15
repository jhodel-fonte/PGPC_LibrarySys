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
        
        // Find requireAcknowledgement checkbox value for the current type
        const requireAck = document.getElementById('require_ack_' + this.policyTypeToUpdate).checked;
        
        $wire.updatePolicy(this.policyTypeToUpdate, this.updateMode === 'immediate', requireAck, this.scheduledDate);
        this.updateModalOpen = false;
    }
}">
    <h2 class="text-lg font-bold text-slate-900">Content & Legal Management</h2>
    <p class="mt-1 text-sm text-slate-500">Manage policies and system-wide information displayed to users.</p>
    
    <div class="max-w-[960px] w-full">
        
        @foreach([
            'terms' => ['title' => 'Terms and Conditions', 'desc' => 'Legal terms governing library membership and usage.'],
            'privacy' => ['title' => 'Data Privacy Policy', 'desc' => 'Information describing how member and account data is collected, stored, and processed.'],
            'cookie' => ['title' => 'Cookie Policy', 'desc' => 'Information on how cookies are used in the application.']
        ] as $type => $info)
        
        <div class="mt-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-4">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">{{ $info['title'] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $info['desc'] }}</p>
                    </div>
                    @if($this->dirtyState['sections']["content_legal.{$type}"] ?? false)
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Modified
                        </div>
                    @endif
                </div>
                <div class="flex flex-col sm:items-end text-xs text-slate-500">
                    <span>Version: {{ $settings['content_legal'][$type]['version'] }}</span>
                    <span>Last updated: {{ \Carbon\Carbon::parse($settings['content_legal'][$type]['updated_at'])->format('M d, Y H:i') }}</span>
                </div>
            </div>
            
            <div class="border border-slate-200 rounded-xl bg-white overflow-hidden w-full focus-within:border-[#17357A] focus-within:ring-[3px] focus-within:ring-[#17357A]/10 transition-shadow">
                <!-- Simulated Toolbar -->
                <div class="flex items-center gap-1 p-2 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                    <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Bold"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg></button>
                    <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Italic"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg></button>
                    <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Underline"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg></button>
                </div>
                <textarea wire:model.live="settings.content_legal.{{ $type }}.content" class="w-full min-h-[200px] p-4 text-sm text-slate-900 bg-transparent resize-y outline-none leading-relaxed"></textarea>
                
                <div class="bg-slate-50 border-t border-slate-200 px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="require_ack_{{ $type }}" wire:model.live="settings.content_legal.{{ $type }}.require_acknowledgement" class="focus:ring-[#17357A] h-4 w-4 text-[#17357A] border-slate-300 rounded cursor-pointer">
                        <span class="text-sm font-medium text-slate-700">Require users to acknowledge this version</span>
                    </label>
                    
                    <button type="button" @click="openUpdateModal('{{ $type }}')" class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-[#17357A] hover:bg-[#0f2459] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] transition-colors whitespace-nowrap">
                        Update Policy
                    </button>
                </div>
            </div>
        </div>
        
        @if(!$loop->last)
            <hr class="my-10 border-slate-200">
        @endif
        
        @endforeach
    </div>

    <!-- Update Policy Modal -->
    <div x-show="updateModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="updateModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="updateModalOpen = false"></div>

            <!-- Modal panel -->
            <div x-show="updateModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                Update Policy Configuration
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500">
                                    When should this updated policy take effect? This will bump the policy version.
                                </p>
                                
                                <div class="mt-4 space-y-4">
                                    <label class="flex items-start gap-3 cursor-pointer p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/50 hover:bg-blue-50/50': updateMode === 'immediate'}">
                                        <div class="flex items-center h-5">
                                            <input type="radio" x-model="updateMode" value="immediate" class="focus:ring-[#17357A] h-4 w-4 text-[#17357A] border-slate-300">
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-slate-900">Apply Immediately</span>
                                            <span class="text-xs text-slate-500">The new policy will be active immediately.</span>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start gap-3 cursor-pointer p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors" :class="{'border-blue-500 bg-blue-50/50 hover:bg-blue-50/50': updateMode === 'schedule'}">
                                        <div class="flex items-center h-5">
                                            <input type="radio" x-model="updateMode" value="schedule" class="focus:ring-[#17357A] h-4 w-4 text-[#17357A] border-slate-300">
                                        </div>
                                        <div class="flex flex-col w-full">
                                            <span class="text-sm font-medium text-slate-900">Schedule Update</span>
                                            <span class="text-xs text-slate-500">The policy will be automatically applied on the selected date.</span>
                                            
                                            <div x-show="updateMode === 'schedule'" class="mt-3">
                                                <x-date time format="YYYY-MM-DD HH:mm" x-model="scheduledDate" />
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                    <button type="button" @click="confirmUpdate" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-[#17357A] text-base font-medium text-white hover:bg-[#0f2459] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm Update
                    </button>
                    <button type="button" @click="updateModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
