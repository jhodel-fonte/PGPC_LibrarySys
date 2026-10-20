<div class="w-full p-6 lg:p-8">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">System & Audit Logs</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Review administrative actions, catalog imports, security events, and audit trails.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Audit Trail
        </span>
    </div>
    
    <div class="mt-8">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold text-[#334155] uppercase tracking-wide">Filter:</span>
                <select class="h-10 rounded-xl border border-[#E2E8F0] bg-white px-3 text-xs font-bold text-[#334155] focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] outline-none transition-all shadow-sm">
                    <option value="all">All Events & Levels</option>
                    <option value="auth">Authentication & Sign-in</option>
                    <option value="catalog">MARC Extraction & Accessions</option>
                    <option value="settings">Configuration & Settings</option>
                    <option value="errors">System Alerts & Errors</option>
                </select>
            </div>
            
            <button type="button" class="h-10 px-4 rounded-xl bg-white border border-[#E2E8F0] text-xs font-bold text-[#102B70] hover:bg-[#EFF6FF] hover:border-[#BFDBFE] transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Export CSV Logs
            </button>
        </div>
        
        <!-- Logs Table -->
        <div class="border border-[#E2E8F0] rounded-xl overflow-hidden shadow-sm bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 w-44">Timestamp</th>
                            <th class="px-5 py-3.5 w-28">Level</th>
                            <th class="px-5 py-3.5">Activity Message</th>
                            <th class="px-5 py-3.5 w-44">User / Actor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @forelse($settings['system_logs'] ?? [] as $log)
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                                <td class="px-5 py-3.5 text-[#64748B] text-xs font-medium font-mono">
                                    {{ \Carbon\Carbon::parse($log['timestamp'])->format('M d, Y h:i:s A') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($log['level'] === 'INFO')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">INFO</span>
                                    @elseif($log['level'] === 'SUCCESS')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">SUCCESS</span>
                                    @elseif($log['level'] === 'WARNING')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">WARNING</span>
                                    @elseif($log['level'] === 'ERROR')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-800 border border-red-200">ERROR</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-[#0F172A] font-semibold text-xs truncate max-w-md">
                                    {{ $log['message'] }}
                                </td>
                                <td class="px-5 py-3.5 text-[#334155] text-xs font-medium">
                                    {{ $log['user'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-xs text-[#64748B]">No audit events logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Footer -->
            <div class="px-5 py-3 border-t border-[#E2E8F0] bg-[#F8FAFC] flex items-center justify-between">
                <span class="text-xs text-[#64748B]">Showing <span class="font-bold text-[#0F172A]">{{ count($settings['system_logs'] ?? []) }}</span> recorded audit events</span>
                <div class="flex gap-1.5">
                    <button type="button" class="h-8 w-8 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-slate-400 disabled:opacity-40 cursor-not-allowed" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" class="h-8 w-8 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-[#102B70] hover:bg-[#EFF6FF] font-bold text-xs transition-colors">
                        1
                    </button>
                    <button type="button" class="h-8 w-8 flex items-center justify-center rounded-lg border border-[#E2E8F0] bg-white text-slate-400 disabled:opacity-40 cursor-not-allowed" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
