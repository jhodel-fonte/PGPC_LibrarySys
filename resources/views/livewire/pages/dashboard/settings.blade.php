<div class="min-h-full bg-[#F8FAFC] relative pb-12 font-sans">
    <div class="mx-auto w-full max-w-[1440px] p-4 sm:p-6 lg:p-8 space-y-6 relative">
        
        <!-- Subtle PGPC Watermark Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-start justify-center opacity-[0.012] z-0 pt-20">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 2. Main Grid Layout -->
        <div class="relative z-10 flex flex-col lg:grid lg:grid-cols-[260px_minmax(0,1fr)] gap-6 items-start">
            
            <!-- Left Sidebar Navigation -->
            <div class="w-full rounded-2xl border border-[#E2E8F0] bg-white p-3 shadow-sm sticky top-6 space-y-1">
                <!-- Institution Group -->
                <div class="px-3 pt-2 pb-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Institution</span>
                </div>
                <button type="button" wire:click="setTab('general')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'general' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'general' ? 'text-[#FCC719]' : 'text-slate-400' }}"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                        <span>General Information</span>
                    </div>
                    @if($this->dirtyState['categories']['general'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#FCC719]"></span>
                    @endif
                </button>

                <!-- Library Rules Group -->
                <div class="px-3 pt-3 pb-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Circulation & Policies</span>
                </div>
                <button type="button" wire:click="setTab('circulation')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'circulation' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'circulation' ? 'text-[#FCC719]' : 'text-slate-400' }}"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="m9 9.5 2 2 4-4"/></svg>
                        <span>Circulation Rules</span>
                    </div>
                    @if($this->dirtyState['categories']['circulation'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#FCC719]"></span>
                    @endif
                </button>

                <!-- Communication Group -->
                <div class="px-3 pt-3 pb-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Communications</span>
                </div>
                <button type="button" wire:click="setTab('content')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'content' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'content' ? 'text-[#FCC719]' : 'text-slate-400' }}"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                        <span>Content & Legal</span>
                    </div>
                    @if($this->dirtyState['categories']['content_legal'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#FCC719]"></span>
                    @endif
                </button>
                <button type="button" wire:click="setTab('notifications')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'notifications' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'notifications' ? 'text-[#FCC719]' : 'text-slate-400' }}"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span>Notifications</span>
                    </div>
                    @if($this->dirtyState['categories']['notifications'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#FCC719]"></span>
                    @endif
                </button>

                <!-- System Group -->
                <div class="px-3 pt-3 pb-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Technology & Operations</span>
                </div>
                <button type="button" wire:click="setTab('ai')" class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'ai' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'ai' ? 'text-[#FCC719]' : 'text-slate-400' }}"><path d="M12 5a3 3 0 1 0-3 3"/><path d="M9 8a3 3 0 1 0 3 3"/><path d="M12 11a3 3 0 1 0 3-3"/><path d="M15 8a3 3 0 1 0-3-3"/><path d="M12 2v3"/><path d="M12 19v3"/><path d="M22 12h-3"/><path d="M5 12H2"/><path d="M19 5l-2 2"/><path d="M7 17l-2 2"/><path d="M19 19l-2-2"/><path d="M7 7l-2-2"/></svg>
                        <span>AI & Integrations</span>
                    </div>
                    @if($this->dirtyState['categories']['ai_integrations'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-[#FCC719]"></span>
                    @endif
                </button>
                <button type="button" wire:click="setTab('backup')" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'backup' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'backup' ? 'text-[#FCC719]' : 'text-slate-400' }}"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69"/><path d="M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88"/><path d="M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3 4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></svg>
                    <span>Backup & Maintenance</span>
                </button>
                <button type="button" wire:click="setTab('logs')" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs transition-all text-left cursor-pointer {{ $activeTab === 'logs' ? 'bg-[#102B70] text-white shadow-sm font-bold' : 'text-[#334155] hover:bg-[#F8FAFC] hover:text-[#102B70] font-semibold' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'logs' ? 'text-[#FCC719]' : 'text-slate-400' }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>System Logs</span>
                </button>
            </div>

            <!-- Right Content Area -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm overflow-hidden min-h-[580px] w-full">
                @if($activeTab === 'general')
                    @include('components.settings.general')
                @elseif($activeTab === 'circulation')
                    @include('components.settings.circulation-rules')
                @elseif($activeTab === 'content')
                    @include('components.settings.content-legal')
                @elseif($activeTab === 'notifications')
                    @include('components.settings.notifications')
                @elseif($activeTab === 'ai')
                    @include('components.settings.ai-integrations')
                @elseif($activeTab === 'backup')
                    @include('components.settings.backup-maintenance')
                @elseif($activeTab === 'logs')
                    @include('components.settings.system-logs')
                @endif
            </div>

        </div>
    </div>
</div>
