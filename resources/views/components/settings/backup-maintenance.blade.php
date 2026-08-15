<div class="w-full p-6 lg:p-8" x-data="{ 
    isBackingUp: false, 
    backupComplete: false,
    startBackup() {
        this.isBackingUp = true;
        this.backupComplete = false;
        setTimeout(() => {
            this.isBackingUp = false;
            this.backupComplete = true;
        }, 2500);
    }
}">
    <h2 class="text-lg font-bold text-slate-900">Backup & Maintenance</h2>
    <p class="mt-1 text-sm text-slate-500">Create administrative backups and review backup information.</p>
    
    <div class="max-w-[800px] w-full">
        <!-- Database Backup -->
        <div class="mt-8">
            <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Database Backup</h3>
            <p class="mt-1 text-xs text-slate-500 mb-4">Create an export of the current library system database.</p>
            
            <div class="w-full rounded-xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm flex flex-col sm:flex-row gap-5 items-start">
                <div class="h-12 w-12 shrink-0 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 border border-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69"/><path d="M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88"/><path d="M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3 4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></svg>
                </div>
            
            <div class="flex-1 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <h4 class="text-base font-bold text-slate-900">Database Backup</h4>
                    <span class="text-xs text-slate-500 font-medium">Last backup: {{ \Carbon\Carbon::parse($settings['backup']['last_backup'])->format('M d, Y • h:i A') }}</span>
                </div>
                
                <p class="text-sm text-slate-600 leading-relaxed mb-5">
                    Generate a new complete backup of the current library data, including all patron records, catalogs, and circulation history.
                </p>
                
                <!-- Default State -->
                <div x-show="!isBackingUp && !backupComplete">
                    <button @click="startBackup" class="flex items-center justify-center gap-2 h-10 px-5 rounded-lg bg-[#17357A] hover:bg-[#122D68] text-white text-sm font-semibold transition-colors shadow-sm w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Create Backup
                    </button>
                </div>
                
                <!-- Backing up State -->
                <div x-show="isBackingUp" style="display: none;" class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-[#17357A]">Creating backup...</span>
                        <span class="text-xs text-slate-500 animate-pulse">Please wait</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-[#17357A] h-1.5 rounded-full relative w-full overflow-hidden">
                            <div class="absolute inset-0 bg-white/30 animate-[translateX_1s_infinite_linear]" style="background-image: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transform: translateX(-100%);"></div>
                        </div>
                        <style>
                            @keyframes translateX {
                                100% { transform: translateX(100%); }
                            }
                        </style>
                    </div>
                </div>
                
                <!-- Success State -->
                <div x-show="backupComplete" style="display: none;" class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between bg-emerald-50 rounded-lg p-3 border border-emerald-200">
                    <div class="flex items-center gap-2 text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span class="text-sm font-semibold">Backup created successfully.</span>
                    </div>
                    <button class="text-sm font-semibold text-[#17357A] hover:text-[#122D68] transition-colors bg-white px-3 py-1.5 rounded-md border border-[#17357A]/20 shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Download Backup
                    </button>
                </div>
                
            </div>
        </div>
    </div>
</div>
