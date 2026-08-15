<div class="w-full p-6 lg:p-8">
    <h2 class="text-lg font-bold text-slate-900">General Institution Settings</h2>
    <p class="mt-1 text-sm text-slate-500">Basic information and operating schedule for the library.</p>
    
    <div class="max-w-[960px] w-full">
        <!-- Institution Information -->
    <div class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Institution Information</h3>
                <p class="mt-1 text-xs text-slate-500">Basic public information displayed throughout the library system.</p>
            </div>
            @if($this->dirtyState['sections']['general.library_name'] ?? false || $this->dirtyState['sections']['general.email'] ?? false || $this->dirtyState['sections']['general.phone'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-5 w-full">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Library Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.live="settings.general.library_name" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Official Email</label>
                <input type="email" wire:model.live="settings.general.email" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number</label>
                <input type="text" wire:model.live="settings.general.phone" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
            </div>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Operating Hours -->
    <div class="mt-6">
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Operating Hours</h3>
                <p class="mt-1 text-xs text-slate-500">Set the normal opening and closing schedule.</p>
            </div>
            @if($this->dirtyState['sections']['general.operating_hours'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="w-full overflow-hidden rounded-xl border border-slate-200 hidden sm:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 w-1/4">Day</th>
                        <th class="px-4 py-3 w-1/4">Status</th>
                        <th class="px-4 py-3 w-1/4">Opens</th>
                        <th class="px-4 py-3 w-1/4">Closes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-700 capitalize">{{ $day }}</td>
                            <td class="px-4 py-3">
                                <select wire:model.live="settings.general.operating_hours.{{ $day }}.status" class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs font-medium text-slate-700 focus:border-[#17357A] outline-none">
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.opens" 
                                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-slate-700 outline-none disabled:opacity-50 disabled:bg-slate-50"
                                    {{ $settings['general']['operating_hours'][$day]['status'] === 'Closed' ? 'disabled' : '' }}>
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.closes" 
                                    class="h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs text-slate-700 outline-none disabled:opacity-50 disabled:bg-slate-50"
                                    {{ $settings['general']['operating_hours'][$day]['status'] === 'Closed' ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Operating Hours -->
        <div class="sm:hidden w-full space-y-3">
            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                <div class="w-full border border-slate-200 rounded-xl p-4 bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-medium text-slate-700 capitalize">{{ $day }}</span>
                        <select wire:model.live="settings.general.operating_hours.{{ $day }}.status" class="h-8 rounded-md border border-slate-200 bg-white px-2 text-xs font-medium text-slate-700 focus:border-[#17357A] outline-none">
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    @if($settings['general']['operating_hours'][$day]['status'] === 'Open')
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-xs text-slate-500 mb-1">Opens</label>
                                <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.opens" class="h-9 w-full rounded-md border border-slate-200 bg-white px-2 text-sm text-slate-700 outline-none">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-slate-500 mb-1">Closes</label>
                                <input type="time" wire:model.live="settings.general.operating_hours.{{ $day }}.closes" class="h-9 w-full rounded-md border border-slate-200 bg-white px-2 text-sm text-slate-700 outline-none">
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-slate-400 italic">Library is closed.</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Holiday Calendar -->
    <div class="mt-6">
        <div class="flex items-center gap-3 mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Scheduled Closures & Holidays</h3>
                <p class="mt-1 text-xs text-slate-500">Dates when the library will be exceptionally closed.</p>
            </div>
            @if($this->dirtyState['sections']['general.closures'] ?? false)
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Modified
                </div>
            @endif
        </div>
        
        <div class="w-full space-y-3">
            <button class="flex items-center gap-2 h-9 px-3 rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-sm font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Closure Date
            </button>
            
            <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                @foreach($settings['general']['closures'] as $index => $closure)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 px-4 gap-3 hover:bg-slate-50/50">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-6">
                            <span class="text-sm font-medium text-slate-700 min-w-[120px]">{{ \Carbon\Carbon::parse($closure['date'])->format('M d, Y') }}</span>
                            <span class="text-sm text-slate-600">{{ $closure['reason'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 self-end sm:self-auto">
                            <button class="text-xs font-medium text-blue-600 hover:text-blue-700">Edit</button>
                            <button class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
