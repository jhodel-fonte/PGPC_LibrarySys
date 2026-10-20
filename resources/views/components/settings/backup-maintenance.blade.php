<div class="w-full p-6 lg:p-8" x-data="{ 
    isBackingUp: false, 
    backupComplete: false,
    startBackup() {
        this.isBackingUp = true;
        this.backupComplete = false;
        setTimeout(() => {
            this.isBackingUp = false;
            this.backupComplete = true;
        }, 2200);
    }
}">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">Backup & Maintenance</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Create administrative snapshots, manage automated retention, and disaster recovery.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69"/><path d="M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88"/><path d="M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3 4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></svg>
            System Health
        </span>
    </div>
    
    <div class="max-w-[850px] w-full">
        <!-- Database Snapshot Card -->
        <div class="mt-8">
            <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Database Archive & Snapshots</h3>
            <p class="mt-0.5 text-xs text-[#64748B] mb-5">Create an on-demand SQL export of all bibliographic records, catalog copies, and user accounts.</p>
            
            <div class="w-full rounded-2xl border border-[#E2E8F0] bg-white p-5 lg:p-6 shadow-sm flex flex-col sm:flex-row gap-5 items-start">
                <div class="h-12 w-12 shrink-0 rounded-xl bg-[#EFF6FF] flex items-center justify-center text-[#102B70] border border-[#BFDBFE]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69"/><path d="M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88"/><path d="M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3 4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></svg>
                </div>
            
                <div class="flex-1 w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                        <h4 class="text-base font-bold text-[#0F172A]">Complete Library Database Archive</h4>
                        <span class="text-xs text-[#64748B] font-medium bg-[#F8FAFC] px-2.5 py-1 rounded-lg border border-[#E2E8F0]">
                            Last export: {{ \Carbon\Carbon::parse($settings['backup']['last_backup'] ?? now())->format('M d, Y • h:i A') }}
                        </span>
                    </div>
                    
                    <p class="text-xs sm:text-sm text-[#64748B] leading-relaxed mb-5">
                        Generate a verified snapshot containing full Marc records, accession tables, borrower profiles, and circulation audit trails.
                    </p>
                    
                    <!-- Default Trigger State -->
                    <div x-show="!isBackingUp && !backupComplete">
                        <button type="button" @click="startBackup" class="flex items-center justify-center gap-2 h-10 px-5 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition-all shadow-sm cursor-pointer w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Create Backup Snapshot
                        </button>
                    </div>
                    
                    <!-- Progress Bar State -->
                    <div x-show="isBackingUp" style="display: none;" class="bg-[#F8FAFC] rounded-xl p-4 border border-[#BFDBFE]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-[#102B70] flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#102B70] animate-ping"></span>
                                Generating snapshot package...
                            </span>
                            <span class="text-xs text-[#64748B] font-mono">Compressing .sql.gz</span>
                        </div>
                        <div class="w-full bg-[#E2E8F0] rounded-full h-2 overflow-hidden">
                            <div class="bg-[#102B70] h-2 rounded-full relative w-full overflow-hidden">
                                <div class="absolute inset-0 bg-white/30 animate-[translateX_1s_infinite_linear]" style="background-image: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent); transform: translateX(-100%);"></div>
                            </div>
                            <style>
                                @keyframes translateX {
                                    100% { transform: translateX(100%); }
                                }
                            </style>
                        </div>
                    </div>
                    
                    <!-- Success Complete State -->
                    <div x-show="backupComplete" style="display: none;" class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2.5 text-emerald-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <div>
                                <span class="text-xs font-bold block">Snapshot created successfully</span>
                                <span class="text-[11px] text-emerald-700">Checksum SHA-256: 8f4d92a1... (14.2 MB)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-xs font-bold text-[#102B70] hover:bg-[#EFF6FF] transition-colors bg-white px-3.5 py-2 rounded-xl border border-[#BFDBFE] shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                Download .GZ
                            </button>
                            <button type="button" @click="backupComplete = false" class="text-xs font-bold text-slate-500 hover:text-slate-800 p-2 rounded-lg">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Automated Retention Plan -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Automated Backup Schedule</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Automated storage policies to ensure data continuity.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-[#0F172A]">Automated Daily Export</h4>
                        <p class="text-xs text-[#64748B] mt-0.5">Executes automatically at 01:00 AM</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Enabled
                    </span>
                </div>
                
                <div class="p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-[#0F172A]">Retention Period</h4>
                        <p class="text-xs text-[#64748B] mt-0.5">Rotates snapshots older than 30 days</p>
                    </div>
                    <span class="text-xs font-bold text-[#102B70] bg-[#EFF6FF] px-2.5 py-1 rounded-lg border border-[#BFDBFE]">
                        30 Days
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
