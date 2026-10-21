<div class="w-full p-6 lg:p-8">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">General Institution Settings</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Basic information and operating schedule for the library.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/></svg>
            Institution Profile
        </span>
    </div>
    
    <div class="max-w-[960px] w-full">
        <!-- Institution Information -->
        <div class="mt-8">
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Institution Information</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Basic public information displayed throughout the library system.</p>
                </div>
                @if($this->dirtyState['sections']['general.library_name'] ?? false || $this->dirtyState['sections']['general.email'] ?? false || $this->dirtyState['sections']['general.phone'] ?? false || $this->dirtyState['sections']['general.address'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 w-full">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">
                        Library Name <span class="text-[#EF4444]">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </span>
                        <input type="text" wire:model.live="settings.general.library_name" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none" placeholder="e.g. PGPC College Library">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Official Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input type="email" wire:model.live="settings.general.email" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none" placeholder="library@pgpc.edu.ph">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Phone Number</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <input type="text" wire:model.live="settings.general.phone" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none" placeholder="+63 (2) 8123-4567">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Library Facility Location</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <input type="text" wire:model.live="settings.general.address" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-3.5 text-sm text-[#0F172A] font-medium focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none" placeholder="e.g. Main Building, 2nd Floor Library">
                    </div>
                </div>
            </div>

        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Operating Hours -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Operating Hours</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Set the normal opening and closing schedule for patrons.</p>
                </div>
                @if($this->dirtyState['sections']['general.operating_hours'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full overflow-hidden rounded-xl border border-[#E2E8F0] hidden sm:block shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 w-1/4">Day</th>
                            <th class="px-5 py-3.5 w-1/4">Status</th>
                            <th class="px-5 py-3.5 w-1/4">Opens</th>
                            <th class="px-5 py-3.5 w-1/4">Closes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-[#0F172A] capitalize flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($settings['general']['operating_hours'][$day]['status'] ?? '') === 'Open' ? 'bg-[#3B82F6]' : 'bg-slate-300' }}"></span>
                                    {{ $day }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <select wire:model.live="settings.general.operating_hours.{{ $day }}.status" class="h-9 w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 text-xs font-bold text-[#334155] focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] outline-none transition-all">
                                        <option value="Open">Open</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </td>
                                <td class="px-5 py-3.5">
                                    <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.opens" 
                                        class="h-9 w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 text-xs font-medium text-[#0F172A] focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] outline-none transition-all disabled:opacity-40 disabled:bg-[#F8FAFC]"
                                        {{ ($settings['general']['operating_hours'][$day]['status'] ?? '') === 'Closed' ? 'disabled' : '' }}>
                                </td>
                                <td class="px-5 py-3.5">
                                    <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.closes" 
                                        class="h-9 w-full rounded-lg border border-[#E2E8F0] bg-white px-2.5 text-xs font-medium text-[#0F172A] focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] outline-none transition-all disabled:opacity-40 disabled:bg-[#F8FAFC]"
                                        {{ ($settings['general']['operating_hours'][$day]['status'] ?? '') === 'Closed' ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Operating Hours -->
            <div class="sm:hidden w-full space-y-3">
                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    <div class="w-full border border-[#E2E8F0] rounded-xl p-4 bg-white shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-[#0F172A] capitalize">{{ $day }}</span>
                            <select wire:model.live="settings.general.operating_hours.{{ $day }}.status" class="h-8 rounded-lg border border-[#E2E8F0] bg-white px-2.5 text-xs font-bold text-[#334155] focus:border-[#102B70] outline-none">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        @if(($settings['general']['operating_hours'][$day]['status'] ?? '') === 'Open')
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-[#64748B] mb-1">Opens</label>
                                    <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.opens" class="h-9 w-full rounded-lg border border-[#E2E8F0] bg-white px-2 text-sm text-[#0F172A] outline-none focus:border-[#102B70]">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-[#64748B] mb-1">Closes</label>
                                    <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.closes" class="h-9 w-full rounded-lg border border-[#E2E8F0] bg-white px-2 text-sm text-[#0F172A] outline-none focus:border-[#102B70]">
                                </div>
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic bg-[#F8FAFC] py-2 px-3 rounded-lg border border-dashed border-slate-200">Library is closed on this day.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Holiday Calendar & Scheduled Closures -->
        <div>
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Scheduled Closures & Holidays</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Dates when the library service will be exceptionally unavailable.</p>
                </div>
                @if($this->dirtyState['sections']['general.closures'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full space-y-3">
                <button type="button" class="flex items-center gap-2 h-10 px-4 rounded-xl border border-[#E2E8F0] bg-white text-[#102B70] hover:bg-[#EFF6FF] hover:border-[#BFDBFE] text-xs font-bold transition-all shadow-sm cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Closure Date
                </button>
                
                <div class="border border-[#E2E8F0] rounded-xl divide-y divide-[#E2E8F0] overflow-hidden bg-white shadow-sm">
                    @forelse(($settings['general']['closures'] ?? []) as $index => $closure)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 px-4 gap-3 hover:bg-[#F8FAFC] transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE] min-w-[120px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    {{ \Carbon\Carbon::parse($closure['date'])->format('M d, Y') }}
                                </span>
                                <span class="text-sm font-semibold text-[#0F172A]">{{ $closure['reason'] ?? $closure['description'] ?? 'Holiday' }}</span>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <button type="button" class="px-2.5 py-1 text-xs font-bold text-[#102B70] hover:bg-[#EFF6FF] rounded-lg transition-colors cursor-pointer">Edit</button>
                                <button type="button" class="px-2.5 py-1 text-xs font-bold text-[#EF4444] hover:bg-[#FEF2F2] rounded-lg transition-colors cursor-pointer">Remove</button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-[#64748B]">No upcoming scheduled closures.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
