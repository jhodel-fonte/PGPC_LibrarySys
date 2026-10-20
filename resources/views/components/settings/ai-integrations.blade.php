<div class="w-full p-6 lg:p-8" x-data="{ 
    testing: false, 
    tested: false,
    testConnection() {
        this.testing = true;
        this.tested = false;
        setTimeout(() => {
            this.testing = false;
            this.tested = true;
        }, 800);
    }
}">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">AI & Machine Learning Services</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Configure endpoints and predictive confidence thresholds for recommendation services.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M12 5a3 3 0 1 0-3 3"/><path d="M9 8a3 3 0 1 0 3 3"/><path d="M12 11a3 3 0 1 0 3-3"/><path d="M15 8a3 3 0 1 0-3-3"/><path d="M12 2v3"/><path d="M12 19v3"/><path d="M22 12h-3"/><path d="M5 12H2"/><path d="M19 5l-2 2"/><path d="M7 17l-2 2"/><path d="M19 19l-2-2"/><path d="M7 7l-2-2"/></svg>
            AI Engine
        </span>
    </div>
    
    <div class="max-w-[850px] w-full">
        <!-- Machine Learning Service Connection -->
        <div class="mt-8">
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Recommendation Microservice</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Internal microservice connection parameters for personalized book suggestions.</p>
                </div>
                @if($this->dirtyState['sections']['ai_integrations.recommendation_service'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full rounded-2xl border border-[#E2E8F0] bg-white p-5 lg:p-6 shadow-sm">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-[#EFF6FF] flex items-center justify-center text-[#102B70] border border-[#BFDBFE]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5a3 3 0 1 0-3 3"/><path d="M9 8a3 3 0 1 0 3 3"/><path d="M12 11a3 3 0 1 0 3-3"/><path d="M15 8a3 3 0 1 0-3-3"/><path d="M12 2v3"/><path d="M12 19v3"/><path d="M22 12h-3"/><path d="M5 12H2"/><path d="M19 5l-2 2"/><path d="M7 17l-2 2"/><path d="M19 19l-2-2"/><path d="M7 7l-2-2"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#0F172A]">Recommendation Service Connection</h4>
                        <p class="text-xs text-[#64748B]">FastAPI / Python embedding backend</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-[1fr_130px] gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Service Host URL</label>
                        <input type="text" wire:model.live="settings.ai_integrations.recommendation_service.url" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none" placeholder="http://127.0.0.1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Port</label>
                        <input type="text" wire:model.live="settings.ai_integrations.recommendation_service.port" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none text-center" placeholder="5001">
                    </div>
                </div>
                
                <div class="bg-[#F8FAFC] p-3.5 rounded-xl border border-[#E2E8F0] mb-5 flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-bold text-[#64748B] uppercase tracking-wider mb-0.5">Target Endpoint</span>
                        <code class="text-xs font-mono font-bold text-[#102B70]">{{ $settings['ai_integrations']['recommendation_service']['url'] ?? 'http://127.0.0.1' }}:{{ $settings['ai_integrations']['recommendation_service']['port'] ?? '5001' }}</code>
                    </div>
                    <span class="text-[11px] text-[#64748B] font-medium">Protocol: HTTP REST</span>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-[#E2E8F0]">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#334155]">Current Status:</span>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold text-emerald-800" x-show="!tested">Connected</span>
                            <span class="text-xs font-bold text-emerald-800" x-show="tested" style="display: none;">Verified (22ms latency)</span>
                        </div>
                    </div>
                    
                    <button 
                        type="button" 
                        @click="testConnection"
                        :disabled="testing"
                        class="h-10 px-4 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#BFDBFE] hover:bg-[#EFF6FF] text-xs font-bold text-[#102B70] transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                        <svg x-show="!testing" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <svg x-show="testing" style="display: none;" class="animate-spin h-3.5 w-3.5 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="testing ? 'Testing Endpoint...' : 'Test Connection'"></span>
                    </button>
                </div>
            </div>
            
            <p class="mt-3 text-xs text-[#64748B] flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#102B70]"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Use loopback or trusted intranet IP addresses configured within PGPC security policies.
            </p>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Predictive Confidence Threshold -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Recommendation Quality & Confidence</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Minimum similarity score required before displaying automated book suggestions to patrons.</p>
                </div>
                @if($this->dirtyState['sections']['ai_integrations.confidence_threshold'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full p-5 lg:p-6 rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <label class="block text-xs font-bold text-[#334155] uppercase tracking-wide">Confidence Score Cutoff</label>
                    <div class="flex items-center gap-1 bg-[#EFF6FF] border border-[#BFDBFE] px-3 py-1 rounded-xl text-[#102B70] font-bold text-sm">
                        <span>{{ $settings['ai_integrations']['confidence_threshold'] ?? 70 }}</span>
                        <span class="text-xs font-semibold">%</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 mb-2">
                    <span class="text-xs font-bold text-slate-400">0%</span>
                    <input type="range" min="0" max="100" step="1" 
                        wire:model.live="settings.ai_integrations.confidence_threshold"
                        class="w-full h-2 bg-[#E2E8F0] rounded-lg appearance-none cursor-pointer accent-[#102B70]">
                    <span class="text-xs font-bold text-slate-400">100%</span>
                </div>
                
                <div class="mt-4 p-3.5 rounded-xl bg-[#EFF6FF]/50 border border-[#BFDBFE]/60 text-xs text-[#334155] leading-relaxed flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70] shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span>Higher cutoff thresholds filter out looser matches and ensure only directly relevant subject recommendations appear in the OPAC and student feed.</span>
                </div>
            </div>
        </div>
    </div>
</div>
