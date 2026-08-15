<div class="w-full p-6 lg:p-8">
    <h2 class="text-lg font-bold text-slate-900">Circulation & Rule Configurations</h2>
    <p class="mt-1 text-sm text-slate-500">Configure borrowing limits, loan durations, fines, and renewals.</p>
    
    <div class="max-w-[820px] w-full">
        <!-- Borrowing Limits -->
    <div class="mt-8">
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Borrowing Limits</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Maximum number of active loans allowed per account type.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($settings['circulation']['borrowing_limits'] as $type => $limit)
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-white shadow-sm">
                    <span class="text-sm font-medium text-slate-700">{{ $type }}</span>
                    <input type="number" wire:model.live="settings.circulation.borrowing_limits.{{ $type }}" wire:change="markAsDirty" min="1" max="50" class="h-9 w-20 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 focus:border-[#17357A] focus:bg-white focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-center">
                </div>
            @endforeach
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Loan Durations -->
    <div>
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Loan Durations</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Standard borrowing periods by item category.</p>
        
        <div class="w-full overflow-hidden rounded-xl border border-slate-200 shadow-sm hidden sm:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3.5">Item Category</th>
                        <th class="px-5 py-3.5 w-40 text-right">Loan Period (Days)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($settings['circulation']['loan_durations'] as $category => $days)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $category }}</td>
                            <td class="px-5 py-4 text-right">
                                <input type="number" wire:model.live="settings.circulation.loan_durations.{{ $category }}" wire:change="markAsDirty" min="1" max="90" class="h-9 w-20 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 focus:border-[#17357A] focus:bg-white focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-center inline-block">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200">
                <button class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Loan Rule
                </button>
            </div>
        </div>
        
        <!-- Mobile Loan Durations -->
        <div class="sm:hidden space-y-3 w-full">
            @foreach($settings['circulation']['loan_durations'] as $category => $days)
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-white shadow-sm">
                    <span class="text-sm font-medium text-slate-700">{{ $category }}</span>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model.live="settings.circulation.loan_durations.{{ $category }}" wire:change="markAsDirty" min="1" max="90" class="h-9 w-16 rounded-md border border-slate-200 bg-slate-50 px-2 text-sm text-slate-900 focus:border-[#17357A] focus:bg-white focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-center">
                        <span class="text-xs text-slate-500">days</span>
                    </div>
                </div>
            @endforeach
            <button class="w-full mt-2 h-10 rounded-lg border border-slate-200 bg-slate-50 text-sm font-medium text-slate-600 hover:bg-slate-100 flex items-center justify-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Loan Rule
            </button>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Fine Rules -->
    <div>
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Fine Rules</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Configure overdue penalties charged to patrons. These settings apply only to patron overdue fines. They do not represent library operating or administrative costs.</p>
        
        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-100 rounded-xl w-full mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 mt-0.5 shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <p class="text-sm text-blue-800 leading-snug">Changes apply to future fine calculations according to the system's configured fine policy.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Daily Overdue Fine (₱)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 text-sm font-medium pointer-events-none">₱</span>
                    <input type="number" step="0.50" min="0" wire:model.live="settings.circulation.fine_rules.daily_fine" wire:change="markAsDirty" class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-8 pr-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none">
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Per item, per day</p>
            </div>
        </div>
    </div>
    
    <hr class="my-10 border-slate-200">
    
    <!-- Renewal Limits -->
    <div class="mb-2">
        <h3 class="text-sm font-semibold text-slate-900 tracking-wide uppercase">Renewal Limits</h3>
        <p class="mt-1 text-xs text-slate-500 mb-4">Rules governing the extension of active loans.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Maximum Consecutive Renewals</label>
                <div class="flex items-center gap-3">
                    <input type="number" min="0" wire:model.live="settings.circulation.renewal_limits.max_consecutive" wire:change="markAsDirty" class="h-10 w-24 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 transition-shadow outline-none text-center">
                    <span class="text-sm text-slate-600">renewals</span>
                </div>
            </div>
        </div>
    </div>
</div>
