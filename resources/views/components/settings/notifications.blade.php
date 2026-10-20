<div class="w-full p-6 lg:p-8" x-data="{ showEmailWarning: false }">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">Notification & Communications</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Control delivery channels, templates, global banners, and scheduled jobs.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            Alerts Hub
        </span>
    </div>
    
    <div class="max-w-[960px] w-full">
        <!-- Delivery Channels -->
        <div class="mt-8">
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Delivery Channels</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Master switches for automated patron and librarian notification channels.</p>
                </div>
                @if($this->dirtyState['sections']['notifications.channels'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email Switch -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm hover:border-[#BFDBFE] transition-all">
                    <div class="flex gap-3.5 pr-4">
                        <div class="mt-0.5 shrink-0 h-10 w-10 rounded-xl bg-[#EFF6FF] flex items-center justify-center text-[#102B70] border border-[#BFDBFE]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#0F172A]">Email Dispatching</h4>
                            <p class="mt-1 text-xs text-[#64748B] leading-relaxed">Send institutional email alerts for loan renewals, due dates, and circulation activities.</p>
                            
                            @if(!($settings['notifications']['channels']['email'] ?? true))
                                <div class="mt-3 flex items-start gap-2 p-2.5 rounded-lg border border-amber-200 bg-amber-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500 shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                    <p class="text-xs text-amber-800 font-medium">Outgoing emails are paused in this configuration.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Toggle Switch -->
                    <button 
                        type="button" 
                        wire:click="$toggle('settings.notifications.channels.email')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#102B70] focus:ring-offset-2 {{ ($settings['notifications']['channels']['email'] ?? true) ? 'bg-[#102B70]' : 'bg-slate-200' }}" 
                        role="switch" 
                        aria-checked="{{ ($settings['notifications']['channels']['email'] ?? true) ? 'true' : 'false' }}">
                        <span class="sr-only">Toggle Email Notifications</span>
                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($settings['notifications']['channels']['email'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <!-- In-App Switch -->
                <div class="flex items-start justify-between p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm hover:border-[#BFDBFE] transition-all">
                    <div class="flex gap-3.5 pr-4">
                        <div class="mt-0.5 shrink-0 h-10 w-10 rounded-xl bg-[#EFF6FF] flex items-center justify-center text-[#102B70] border border-[#BFDBFE]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-[#0F172A]">In-App Notifications</h4>
                            <p class="mt-1 text-xs text-[#64748B] leading-relaxed">Display real-time bell notifications and dashboard toast messages.</p>
                        </div>
                    </div>
                    <!-- Toggle Switch -->
                    <button 
                        type="button" 
                        wire:click="$toggle('settings.notifications.channels.in_app')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#102B70] focus:ring-offset-2 {{ ($settings['notifications']['channels']['in_app'] ?? true) ? 'bg-[#102B70]' : 'bg-slate-200' }}" 
                        role="switch" 
                        aria-checked="{{ ($settings['notifications']['channels']['in_app'] ?? true) ? 'true' : 'false' }}">
                        <span class="sr-only">Toggle In-App Notifications</span>
                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($settings['notifications']['channels']['in_app'] ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Notification Templates -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Notification Templates</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Manage automated notice templates sent to patrons and faculty.</p>
                </div>
                @if($this->dirtyState['sections']['notifications.templates'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full border border-[#E2E8F0] rounded-xl overflow-hidden hidden sm:block shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Notification Template</th>
                            <th class="px-5 py-3.5 w-36">Channel</th>
                            <th class="px-5 py-3.5 w-32">Status</th>
                            <th class="px-5 py-3.5 w-24 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @foreach(($settings['notifications']['templates'] ?? []) as $template)
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors group">
                                <td class="px-5 py-3.5 font-bold text-[#0F172A]">{{ $template['name'] }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
                                        {{ $template['channel'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $template['status'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button type="button" class="text-xs font-bold text-[#102B70] hover:text-[#0B225E] hover:underline cursor-pointer">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile view for templates -->
            <div class="sm:hidden space-y-3 w-full">
                @foreach(($settings['notifications']['templates'] ?? []) as $template)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm">
                        <div>
                            <h4 class="text-sm font-bold text-[#0F172A]">{{ $template['name'] }}</h4>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-[#64748B]">{{ $template['channel'] }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span class="text-xs font-bold text-emerald-600">{{ $template['status'] }}</span>
                            </div>
                        </div>
                        <button type="button" class="text-xs font-bold text-[#102B70] px-3 py-1.5 rounded-lg hover:bg-[#EFF6FF]">Edit</button>
                    </div>
                @endforeach
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Global Announcements Designer Preview -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Global System Announcements</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Broadcast prominent banner announcements across student and staff dashboards.</p>
                </div>
                <button type="button" class="flex items-center gap-2 h-10 px-4 rounded-xl bg-[#102B70] text-white hover:bg-[#0B225E] text-xs font-bold transition-all shadow-sm cursor-pointer self-start sm:self-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    New Announcement
                </button>
            </div>
            
            <div class="space-y-3">
                <div class="p-4 rounded-xl border border-[#BFDBFE] bg-[#EFF6FF]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#102B70] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-[#102B70]">Semester Book Borrowing Deadline Reminder</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                            </div>
                            <p class="text-xs text-[#334155] mt-1 leading-relaxed">All patrons must return checked out items prior to the term-end exam week on Nov 15.</p>
                            <span class="text-[11px] text-[#64748B] mt-1 block">Visible to: All Registered Students & Faculty</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button type="button" class="px-3 py-1.5 text-xs font-bold text-[#102B70] hover:bg-white rounded-lg border border-transparent hover:border-[#BFDBFE] transition-all">Edit</button>
                        <button type="button" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-all">Remove</button>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-[#E2E8F0] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#F8FAFC] text-slate-500 border border-[#E2E8F0] flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-[#0F172A]">Annual Inventory Scheduled Maintenance</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Draft</span>
                            </div>
                            <p class="text-xs text-[#64748B] mt-1 leading-relaxed">Circulation desk will be limited to counter inquiry on Friday afternoon.</p>
                            <span class="text-[11px] text-[#64748B] mt-1 block">Scheduled: Dec 01, 2026</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button type="button" class="px-3 py-1.5 text-xs font-bold text-[#102B70] hover:bg-[#F8FAFC] rounded-lg transition-all">Edit</button>
                        <button type="button" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-all">Remove</button>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Daily Processing Schedule -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Daily Processing Schedule</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Automated cron schedule for daily overdue calculation and patron notifications.</p>
                </div>
                @if($this->dirtyState['sections']['notifications.daily_cron'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full p-5 rounded-xl border border-[#E2E8F0] bg-white shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-[#0F172A]">Daily System Routine</h4>
                        <p class="text-xs text-[#64748B] mt-0.5">Executes overnight to update borrower penalty charges and dispatch alerts.</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-bold text-[#334155]">Run at:</span>
                        <input type="time" wire:model.live="settings.notifications.daily_cron" class="h-10 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-xs font-bold text-[#102B70] focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] outline-none transition-all">
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-[#E2E8F0] flex items-center justify-between">
                    <p class="text-xs font-medium text-[#64748B] flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Next scheduled run: <span class="font-bold text-[#0F172A]">Tomorrow at {{ \Carbon\Carbon::parse($settings['notifications']['daily_cron'] ?? '00:00')->format('g:i A') }}</span>
                    </p>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Cron Service Ready
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
