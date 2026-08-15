<div class="w-full p-6 lg:p-8">
    <h2 class="text-lg font-bold text-slate-900">System Logs</h2>
    <p class="mt-1 text-sm text-slate-500">Review system activities, administrative actions, and system errors.</p>
    
    <div class="mt-8">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-700">Filter by:</span>
                <select class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm text-slate-700 focus:border-[#17357A] outline-none">
                    <option value="all">All Events</option>
                    <option value="auth">Authentication</option>
                    <option value="settings">Settings Changes</option>
                    <option value="system">System Errors</option>
                </select>
            </div>
            
            <button class="h-9 px-3 rounded-md bg-white border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Export Logs
            </button>
        </div>
        
        <!-- Logs Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 w-40">Timestamp</th>
                        <th class="px-4 py-3 w-32">Level</th>
                        <th class="px-4 py-3">Message</th>
                        <th class="px-4 py-3 w-32">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($settings['system_logs'] ?? [] as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($log['timestamp'])->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">
                                @if($log['level'] === 'INFO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 tracking-wider">INFO</span>
                                @elseif($log['level'] === 'SUCCESS')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 tracking-wider">SUCCESS</span>
                                @elseif($log['level'] === 'WARNING')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 tracking-wider">WARNING</span>
                                @elseif($log['level'] === 'ERROR')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800 tracking-wider">ERROR</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 truncate max-w-xl">{{ $log['message'] }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $log['user'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">No system logs available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination Footer -->
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="text-xs text-slate-500">Showing 1 to 5 of 124 entries</span>
                <div class="flex gap-1">
                    <button class="h-7 w-7 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-400 disabled:opacity-50" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button class="h-7 w-7 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
