<div class="w-full p-6 lg:p-8">
    <h2 class="text-lg font-bold text-slate-900">AI & Integration Settings</h2>
    <p class="mt-1 text-sm text-slate-500">Configure connections to machine-learning services used by the library system.</p>
    
    <div class="max-w-[850px] w-full">
        <!-- Machine Learning Endpoints -->
    <div class="mt-8">
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Machine Learning Service</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Connection parameters for the recommendation engine.</p>
        
        <div class="w-full rounded-xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm">
            <h4 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#17357A]"><path d="M12 5a3 3 0 1 0-3 3"/><path d="M9 8a3 3 0 1 0 3 3"/><path d="M12 11a3 3 0 1 0 3-3"/><path d="M15 8a3 3 0 1 0-3-3"/><path d="M12 2v3"/><path d="M12 19v3"/><path d="M22 12h-3"/><path d="M5 12H2"/><path d="M19 5l-2 2"/><path d="M7 17l-2 2"/><path d="M19 19l-2-2"/><path d="M7 7l-2-2"/></svg>
                Recommendation Service
            </h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_120px] gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Service URL</label>
                    <input type="text" wire:model.live="settings.ai_integrations.recommendation_service.url" wire:change="markAsDirty" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Port</label>
                    <input type="text" wire:model.live="settings.ai_integrations.recommendation_service.port" wire:change="markAsDirty" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                </div>
            </div>
            
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 mb-6">
                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Full Endpoint Preview</span>
                <code class="text-sm font-mono text-slate-700">{{ $settings['ai_integrations']['recommendation_service']['url'] }}:{{ $settings['ai_integrations']['recommendation_service']['port'] }}</code>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-600">Connection Status:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-emerald-700">Connected</span>
                    </div>
                </div>
                
                <button class="h-9 px-4 rounded-lg bg-white border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors shadow-sm w-full sm:w-auto">
                    Test Connection
                </button>
            </div>
        </div>
        
        <p class="mt-3 text-xs text-slate-500 flex items-start gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            Use trusted endpoints configured for the PGPC Library System.
        </p>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Predictive Confidence -->
    <div>
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Predictive Confidence</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Recommendations below this confidence level will not be surfaced to users.</p>
        
        <div class="w-full p-5 lg:p-6 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <label class="block text-sm font-medium text-slate-900">Recommendation Confidence Threshold</label>
                <div class="flex items-center gap-1 bg-[#EEF3FF] px-2 py-1 rounded-md text-[#17357A] font-bold">
                    <span>{{ $settings['ai_integrations']['confidence_threshold'] }}</span>
                    <span class="text-xs">%</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4 mb-2">
                <span class="text-xs font-medium text-slate-400">0%</span>
                <input type="range" min="0" max="100" step="1" 
                    wire:model.live="settings.ai_integrations.confidence_threshold" wire:change="markAsDirty"
                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#17357A]">
                <span class="text-xs font-medium text-slate-400">100%</span>
            </div>
            
            <div class="mt-4 p-3 rounded-lg bg-blue-50/50 border border-blue-100/50 text-xs text-slate-600 leading-relaxed">
                Lower values may show more recommendations. Higher values may reduce the number of recommendations shown but increase relevance.
            </div>
        </div>
    </div>
</div>
