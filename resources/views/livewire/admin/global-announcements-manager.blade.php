<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Global Announcements</h3>
            <p class="mt-1 text-xs text-slate-500">Display system-wide notices at the top of the user portal.</p>
        </div>
        @if(!$showForm)
            <button wire:click="create" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-[#17357A] hover:bg-[#0f2459] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] transition-colors">
                Add Announcement
            </button>
        @endif
    </div>

    @if($showForm)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 w-full bg-white p-6 rounded-xl border border-slate-200">
            <!-- Form -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                        <select wire:model.live="activeAnnouncement.status" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                            <option value="Enabled">Enabled</option>
                            <option value="Disabled">Disabled</option>
                        </select>
                        @error('activeAnnouncement.status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Display Style</label>
                        <select wire:model.live="activeAnnouncement.display_style" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                            <option value="Information">Information</option>
                            <option value="Notice">Notice</option>
                            <option value="Warning">Warning</option>
                            <option value="Critical">Critical</option>
                        </select>
                        @error('activeAnnouncement.display_style') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Title</label>
                    <input type="text" wire:model.live="activeAnnouncement.title" placeholder="e.g. System Maintenance" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                    @error('activeAnnouncement.title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                    <textarea wire:model.live="activeAnnouncement.message" rows="3" placeholder="Enter announcement details..." class="w-full rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none resize-none"></textarea>
                    @error('activeAnnouncement.message') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Date / Time (Optional)</label>
                        <x-date time format="YYYY-MM-DD HH:mm" wire:model.live="activeAnnouncement.starts_at" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">End Date / Time (Optional)</label>
                        <x-date time format="YYYY-MM-DD HH:mm" wire:model.live="activeAnnouncement.ends_at" />
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button wire:click="save" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-[#17357A] hover:bg-[#0f2459] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] transition-colors">
                        Save Announcement
                    </button>
                    <button wire:click="cancel" class="inline-flex justify-center py-2 px-4 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
            
            <!-- Preview -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 h-fit">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3 block">Live Preview</span>
                
                @php
                    $style = $activeAnnouncement['display_style'] ?? 'Information';
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
                            <h4 class="text-sm font-bold">{{ $activeAnnouncement['title'] ?: 'Announcement Title' }}</h4>
                            <p class="text-sm mt-1 opacity-90">{{ $activeAnnouncement['message'] ?: 'This is what your announcement will look like to users.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @if(count($announcements) > 0)
            <div class="w-full border border-slate-200 rounded-xl overflow-hidden mt-4">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3 w-32">Style</th>
                            <th class="px-4 py-3 w-32">Status</th>
                            <th class="px-4 py-3 w-48 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($announcements as $announcement)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $announcement['title'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $announcement['display_style'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $announcement['status'] === 'Enabled' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $announcement['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button wire:click="edit({{ $announcement['id'] }})" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">Edit</button>
                                    <button wire:click="delete({{ $announcement['id'] }})" wire:confirm="Are you sure you want to delete this announcement?" class="text-sm font-medium text-red-600 hover:text-red-800 transition-colors">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-slate-500 border border-slate-200 rounded-xl bg-white mt-4">
                No announcements created yet.
            </div>
        @endif
    @endif
</div>
