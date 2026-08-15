<div class="w-full p-6 lg:p-8" x-data="{ showEmailWarning: false }">
    <h2 class="text-lg font-bold text-slate-900">Notification Management</h2>
    <p class="mt-1 text-sm text-slate-500">Control delivery channels, templates, and scheduled notification processing.</p>
    
    <div class="max-w-[960px] w-full">
        <!-- Delivery Channels -->
    <div class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Delivery Channels</h3>
                <p class="mt-1 text-xs text-slate-500">Master switches for system notification methods.</p>
            </div>
            @if($this->dirtyState['sections']['notifications.channels'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Email Switch -->
            <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 bg-white">
                <div class="flex gap-4 pr-4">
                    <div class="mt-0.5 shrink-0 h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Email Notifications</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">Send system-generated email notifications to users for due dates, reservations, and announcements.</p>
                        
                        <!-- Warning when disabled -->
                        @if(!$settings['notifications']['channels']['email'])
                            <div class="mt-3 flex items-start gap-2 p-2.5 rounded-lg border border-amber-200 bg-amber-50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500 shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <p class="text-xs text-amber-800 font-medium">Disabling email notifications will stop all outgoing system emails until this setting is enabled again.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Toggle Switch -->
                <button 
                    type="button" 
                    wire:click="$toggle('settings.notifications.channels.email')"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#17357A] focus:ring-offset-2 {{ $settings['notifications']['channels']['email'] ? 'bg-emerald-500' : 'bg-slate-200' }}" 
                    role="switch" 
                    aria-checked="{{ $settings['notifications']['channels']['email'] ? 'true' : 'false' }}">
                    <span class="sr-only">Toggle Email Notifications</span>
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $settings['notifications']['channels']['email'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            <!-- In-App Switch -->
            <div class="flex items-start justify-between p-4 rounded-xl border border-slate-200 bg-white">
                <div class="flex gap-4 pr-4">
                    <div class="mt-0.5 shrink-0 h-10 w-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">In-App Notifications</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">Display alerts and notifications inside the library portal.</p>
                    </div>
                </div>
                <!-- Toggle Switch -->
                <button 
                    type="button" 
                    wire:click="$toggle('settings.notifications.channels.in_app')"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#17357A] focus:ring-offset-2 {{ $settings['notifications']['channels']['in_app'] ? 'bg-emerald-500' : 'bg-slate-200' }}" 
                    role="switch" 
                    aria-checked="{{ $settings['notifications']['channels']['in_app'] ? 'true' : 'false' }}">
                    <span class="sr-only">Toggle In-App Notifications</span>
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $settings['notifications']['channels']['in_app'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Notification Templates -->
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Notification Templates</h3>
                <p class="mt-1 text-xs text-slate-500">Manage the content and formatting of system messages.</p>
            </div>
            @if($this->dirtyState['sections']['notifications.templates'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="w-full border border-slate-200 rounded-xl overflow-hidden hidden sm:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3">Notification Template</th>
                        <th class="px-4 py-3 w-32">Channel</th>
                        <th class="px-4 py-3 w-32">Status</th>
                        <th class="px-4 py-3 w-20 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($settings['notifications']['templates'] as $template)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $template['name'] }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $template['channel'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                    {{ $template['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button class="text-sm font-medium text-blue-600 hover:text-blue-800 opacity-0 group-hover:opacity-100 transition-opacity">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Mobile view for templates -->
        <div class="sm:hidden space-y-3 w-full">
            @foreach($settings['notifications']['templates'] as $template)
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-white">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $template['name'] }}</h4>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{ $template['channel'] }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span class="text-xs font-medium text-emerald-600">{{ $template['status'] }}</span>
                        </div>
                    </div>
                    <button class="text-sm font-medium text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Edit</button>
                </div>
            @endforeach
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Daily Processing Schedule -->
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Daily Processing Schedule</h3>
                <p class="mt-1 text-xs text-slate-500">Used for scheduled overdue checks and related daily library processing.</p>
            </div>
            @if($this->dirtyState['sections']['notifications.daily_cron'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="w-full p-5 rounded-xl border border-slate-200 bg-white">
            <h4 class="text-sm font-medium text-slate-900 mb-3">Daily System Check</h4>
            
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600">Run every day at</span>
                <input type="time" wire:model.live="settings.notifications.daily_cron" class="h-10 w-32 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-center">
            </div>
            
            <p class="mt-4 text-xs font-medium text-slate-500 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Next scheduled run: <span class="text-slate-700">Tomorrow at {{ \Carbon\Carbon::parse($settings['notifications']['daily_cron'])->format('g:i A') }}</span>
            </p>
        </div>
    </div>
</div>
