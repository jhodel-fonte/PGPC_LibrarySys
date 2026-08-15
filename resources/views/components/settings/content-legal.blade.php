<div class="w-full p-6 lg:p-8">
    <h2 class="text-lg font-bold text-slate-900">Content & Legal Management</h2>
    <p class="mt-1 text-sm text-slate-500">Manage policies and system-wide information displayed to users.</p>
    
    <div class="max-w-[960px] w-full">
        <!-- Terms and Conditions -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Terms and Conditions</h3>
                    <p class="mt-1 text-xs text-slate-500">Legal terms governing library membership and usage.</p>
                </div>
                @if($this->dirtyState['sections']['content_legal.terms_and_conditions'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            <span class="text-xs text-slate-400">Last updated: Aug 15, 2026</span>
        </div>
        
        <div class="border border-slate-200 rounded-xl bg-white overflow-hidden w-full focus-within:border-[#17357A] focus-within:ring-[3px] focus-within:ring-[#17357A]/10 transition-shadow">
            <!-- Simulated Toolbar -->
            <div class="flex items-center gap-1 p-2 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Bold"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Italic"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Underline"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg></button>
                <div class="w-px h-4 bg-slate-300 mx-1"></div>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Heading"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M4 18V6"/><path d="M20 18V6"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Bulleted List"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Numbered List"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg></button>
                <div class="w-px h-4 bg-slate-300 mx-1"></div>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Link"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
            </div>
            <textarea wire:model.live="settings.content_legal.terms_and_conditions" class="w-full min-h-[280px] p-4 text-sm text-slate-900 bg-transparent resize-y outline-none leading-relaxed"></textarea>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Data Privacy Policy -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Data Privacy Policy</h3>
                    <p class="mt-1 text-xs text-slate-500">Information describing how member and account data is collected, stored, and processed.</p>
                </div>
                @if($this->dirtyState['sections']['content_legal.data_privacy_policy'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            <span class="text-xs text-slate-400">Last updated: Aug 15, 2026</span>
        </div>
        
        <div class="border border-slate-200 rounded-xl bg-white overflow-hidden w-full focus-within:border-[#17357A] focus-within:ring-[3px] focus-within:ring-[#17357A]/10 transition-shadow">
            <!-- Simulated Toolbar -->
            <div class="flex items-center gap-1 p-2 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Bold"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Italic"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Underline"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg></button>
                <div class="w-px h-4 bg-slate-300 mx-1"></div>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Heading"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M4 18V6"/><path d="M20 18V6"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Bulleted List"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg></button>
                <button class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition-colors" title="Numbered List"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg></button>
            </div>
            <textarea wire:model.live="settings.content_legal.data_privacy_policy" class="w-full min-h-[280px] p-4 text-sm text-slate-900 bg-transparent resize-y outline-none leading-relaxed"></textarea>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Global Announcements -->
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Global Announcements</h3>
                <p class="mt-1 text-xs text-slate-500">Display system-wide notices at the top of the user portal.</p>
            </div>
            @if($this->dirtyState['sections']['content_legal.announcements'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 w-full">
            <!-- Form -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                        <select wire:model.live="settings.content_legal.announcements.status" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                            <option value="Enabled">Enabled</option>
                            <option value="Disabled">Disabled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Display Style</label>
                        <select wire:model.live="settings.content_legal.announcements.style" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                            <option value="Information">Information</option>
                            <option value="Notice">Notice</option>
                            <option value="Warning">Warning</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Title</label>
                    <input type="text" wire:model.live="settings.content_legal.announcements.title" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                    <textarea wire:model.live="settings.content_legal.announcements.message" rows="3" class="w-full rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none resize-none"></textarea>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Date / Time (Optional)</label>
                        <input type="datetime-local" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">End Date / Time (Optional)</label>
                        <input type="datetime-local" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-slate-500">
                    </div>
                </div>
            </div>
            
            <!-- Preview -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 h-fit">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3 block">Preview</span>
                
                @php
                    $style = $settings['content_legal']['announcements']['style'];
                    $styleClasses = match($style) {
                        'Information' => 'bg-blue-50 border-blue-200 text-blue-800',
                        'Notice' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'Warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                        'Critical' => 'bg-red-50 border-red-200 text-red-800',
                        default => 'bg-slate-50 border-slate-200 text-slate-800'
                    };
                    
                    $iconClass = match($style) {
                        'Information' => 'text-blue-500',
                        'Notice' => 'text-emerald-500',
                        'Warning' => 'text-amber-500',
                        'Critical' => 'text-red-500',
                        default => 'text-slate-500'
                    };
                @endphp
                
                <div class="rounded-lg border p-4 shadow-sm {{ $styleClasses }}">
                    <div class="flex gap-3">
                        <div class="shrink-0 mt-0.5">
                            @if($style === 'Information' || $style === 'Notice')
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconClass }}"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                            @elseif($style === 'Warning' || $style === 'Critical')
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconClass }}"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-bold">{{ $settings['content_legal']['announcements']['title'] ?: 'Announcement Title' }}</h4>
                            <p class="text-sm mt-1 opacity-90">{{ $settings['content_legal']['announcements']['message'] ?: 'This is what your announcement will look like to users.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
