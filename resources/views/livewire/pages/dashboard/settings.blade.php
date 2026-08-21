<div class="min-h-full bg-slate-50 relative pb-10" x-data="{
    showToast: false,
    toastMessage: '',
    init() {
        window.addEventListener('toast', (e) => {
            this.toastMessage = e.detail[0].message;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        });
    }
}">
    <!-- Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg border border-emerald-200"
         style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span class="text-sm font-semibold" x-text="toastMessage"></span>
    </div>

    <div class="mx-auto w-full max-w-[1420px] p-4 lg:p-6 space-y-6 relative">
        
        <!-- Subtle PGPC Watermark Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-start justify-center opacity-[0.015] z-0 pt-20">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header & Actions -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#17357A] flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#17357A]"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    System Settings
                </h1>
                <p class="mt-1 text-sm text-slate-500">Configure library policies, notifications, integrations, and system behavior.</p>
            </div>
            
            <div class="flex items-center gap-3" x-data="{ saved: false }" x-on:settings-saved.window="saved = true; setTimeout(() => saved = false, 3000)">
                <div x-show="saved" x-transition.opacity style="display: none;" class="flex items-center gap-1.5 text-sm font-semibold text-emerald-600 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Saved successfully
                </div>
                @if($this->dirtyState['is_dirty'])
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#FFFBEB] rounded-lg border border-[#FEF3C7] mr-2">
                        <div class="w-2 h-2 rounded-full bg-[#B45309] animate-pulse"></div>
                        <span class="text-xs font-semibold text-[#B45309]">Unsaved changes</span>
                    </div>
                    <button wire:click="discardChanges" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                        Discard
                    </button>
                @endif
                <button wire:click="saveChanges" wire:loading.attr="disabled" {{ !$this->dirtyState['is_dirty'] ? 'disabled' : '' }} class="flex items-center justify-center gap-2 h-10 px-4 min-w-[140px] rounded-lg bg-[#17357A] hover:bg-[#122D68] text-white text-sm font-semibold transition-all shadow-sm {{ $this->dirtyState['is_dirty'] ? 'ring-2 ring-offset-2 ring-[#17357A]/50' : '' }} disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading.remove wire:target="saveChanges" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <svg wire:loading wire:target="saveChanges" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveChanges">Save Changes</span>
                    <span wire:loading wire:target="saveChanges" style="display: none;">Saving...</span>
                </button>
            </div>
        </div>

        <!-- 2. Grid Layout -->
        <div class="relative z-10 flex flex-col lg:grid lg:grid-cols-[250px_minmax(0,1120px)] lg:justify-center gap-5 lg:gap-6 items-start">
            
            <!-- Left Sidebar Navigation -->
            <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm sticky top-6">
                <!-- Institution -->
                <div class="px-2 pt-2 pb-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Institution</span>
                </div>
                <button wire:click="setTab('general')" class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'general' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'general' ? 'text-[#17357A]' : 'text-slate-400' }}"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                        General
                    </div>
                    @if($this->dirtyState['categories']['general'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    @endif
                </button>

                <!-- Library Rules -->
                <div class="px-2 pt-4 pb-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Library Rules</span>
                </div>
                <button wire:click="setTab('circulation')" class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'circulation' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'circulation' ? 'text-[#17357A]' : 'text-slate-400' }}"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="m9 9.5 2 2 4-4"/></svg>
                        Circulation Rules
                    </div>
                    @if($this->dirtyState['categories']['circulation'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    @endif
                </button>

                <!-- Communication -->
                <div class="px-2 pt-4 pb-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Communication</span>
                </div>
                <button wire:click="setTab('content')" class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'content' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'content' ? 'text-[#17357A]' : 'text-slate-400' }}"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                        Content & Legal
                    </div>
                    @if($this->dirtyState['categories']['content_legal'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    @endif
                </button>
                <button wire:click="setTab('notifications')" class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'notifications' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'notifications' ? 'text-[#17357A]' : 'text-slate-400' }}"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        Notifications
                    </div>
                    @if($this->dirtyState['categories']['notifications'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    @endif
                </button>

                <!-- System -->
                <div class="px-2 pt-4 pb-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">System</span>
                </div>
                <button wire:click="setTab('ai')" class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'ai' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'ai' ? 'text-[#17357A]' : 'text-slate-400' }}"><path d="M12 5a3 3 0 1 0-3 3"/><path d="M9 8a3 3 0 1 0 3 3"/><path d="M12 11a3 3 0 1 0 3-3"/><path d="M15 8a3 3 0 1 0-3-3"/><path d="M12 2v3"/><path d="M12 19v3"/><path d="M22 12h-3"/><path d="M5 12H2"/><path d="M19 5l-2 2"/><path d="M7 17l-2 2"/><path d="M19 19l-2-2"/><path d="M7 7l-2-2"/></svg>
                        AI & Integrations
                    </div>
                    @if($this->dirtyState['categories']['ai_integrations'] ?? false)
                        <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    @endif
                </button>
                <button wire:click="setTab('backup')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'backup' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'backup' ? 'text-[#17357A]' : 'text-slate-400' }}"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69"/><path d="M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88"/><path d="M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3 4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></svg>
                    Backup & Maintenance
                </button>
                <button wire:click="setTab('logs')" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-left {{ $activeTab === 'logs' ? 'bg-[#EEF3FF] text-[#17357A] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#17357A]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $activeTab === 'logs' ? 'text-[#17357A]' : 'text-slate-400' }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    System Logs
                </button>
            </div>

            <!-- Right Content Area -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden min-h-[500px] w-full">
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
