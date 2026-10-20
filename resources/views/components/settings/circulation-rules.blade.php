<div class="w-full p-6 lg:p-8">
    <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-5">
        <div>
            <h2 class="text-xl font-bold text-[#102B70]">Circulation & Rule Configurations</h2>
            <p class="mt-1 text-xs sm:text-sm text-[#64748B]">Configure borrowing limits, loan durations, overdue penalties, and renewals.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#F8FAFC] text-[#334155] border border-[#E2E8F0]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70]"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="m9 9.5 2 2 4-4"/></svg>
            Policy Engine
        </span>
    </div>
    
    <div class="max-w-[880px] w-full">
        <!-- Borrowing Limits -->
        <div class="mt-8">
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Borrowing Limits</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Maximum number of active physical book loans allowed simultaneously per account type.</p>
                </div>
                @if($this->dirtyState['sections']['circulation.borrowing_limits'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($settings['circulation']['borrowing_limits'] as $type => $limit)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm hover:border-[#BFDBFE] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#EFF6FF] flex items-center justify-center text-[#102B70] shrink-0 font-bold text-xs">
                                {{ strtoupper(substr($type, 0, 2)) }}
                            </div>
                            <div>
                                <span class="text-sm font-bold text-[#0F172A]">{{ $type }}</span>
                                <p class="text-[11px] text-[#64748B]">Concurrent active checkouts</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live="settings.circulation.borrowing_limits.{{ $type }}" min="1" max="50" class="h-10 w-20 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm font-bold text-[#102B70] focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none text-center">
                            <span class="text-xs font-semibold text-[#64748B]">items</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Loan Durations -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Loan Durations</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Standard borrowing periods according to catalog material item category.</p>
                </div>
                @if($this->dirtyState['sections']['circulation.loan_durations'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="w-full overflow-hidden rounded-xl border border-[#E2E8F0] shadow-sm hidden sm:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Item Category</th>
                            <th class="px-5 py-3.5 w-44 text-right">Loan Period (Days)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @foreach($settings['circulation']['loan_durations'] as $category => $days)
                            <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-[#0F172A] flex items-center gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                                    {{ $category }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <input type="number" wire:model.live="settings.circulation.loan_durations.{{ $category }}" min="1" max="90" class="h-9 w-20 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] px-2.5 text-sm font-bold text-[#102B70] focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none text-center inline-block">
                                        <span class="text-xs font-medium text-[#64748B]">days</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-3 bg-[#F8FAFC] border-t border-[#E2E8F0]">
                    <button type="button" class="text-xs font-bold text-[#102B70] hover:text-[#0B225E] flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add Custom Loan Rule
                    </button>
                </div>
            </div>
            
            <!-- Mobile Loan Durations -->
            <div class="sm:hidden space-y-3 w-full">
                @foreach($settings['circulation']['loan_durations'] as $category => $days)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-[#E2E8F0] bg-white shadow-sm">
                        <span class="text-sm font-bold text-[#0F172A]">{{ $category }}</span>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live="settings.circulation.loan_durations.{{ $category }}" min="1" max="90" class="h-9 w-16 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] px-2 text-sm font-bold text-[#102B70] focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none text-center">
                            <span class="text-xs text-slate-500">days</span>
                        </div>
                    </div>
                @endforeach
                <button type="button" class="w-full mt-2 h-10 rounded-xl border border-[#E2E8F0] bg-white text-xs font-bold text-[#102B70] hover:bg-[#EFF6FF] flex items-center justify-center gap-2 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Custom Loan Rule
                </button>
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Fine Rules -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Fine & Overdue Rules</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Configure overdue penalties charged to patrons for late returns.</p>
                </div>
                @if($this->dirtyState['sections']['circulation.fine_rules'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="flex items-start gap-3 p-4 bg-[#EFF6FF] border border-[#BFDBFE] rounded-xl w-full mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#102B70] mt-0.5 shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <p class="text-xs text-[#102B70] font-medium leading-relaxed">Changes apply strictly to future overdue calculations according to the institutional loan policy.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Daily Overdue Fine (₱)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[#102B70] text-sm font-bold pointer-events-none">₱</span>
                        <input type="number" step="0.50" min="0" wire:model.live="settings.circulation.fine_rules.daily_fine" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-8 pr-3.5 text-sm font-bold text-[#0F172A] focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none">
                    </div>
                    <p class="mt-1.5 text-xs text-[#64748B]">Fee accumulated per item, per overdue calendar day.</p>
                </div>
            </div>
        </div>
        
        <hr class="my-8 border-[#E2E8F0]">
        
        <!-- Renewal Limits -->
        <div>
            <div class="flex items-center gap-3 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-[#102B70] tracking-wider uppercase">Renewal Limits</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">Rules governing online and counter extensions of active loans.</p>
                </div>
                @if($this->dirtyState['sections']['circulation.renewal_limits'] ?? false)
                    <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Modified
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5 uppercase tracking-wide">Maximum Consecutive Renewals</label>
                    <div class="flex items-center gap-3">
                        <input type="number" min="0" wire:model.live="settings.circulation.renewal_limits.max_consecutive" class="h-11 w-24 rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm font-bold text-[#102B70] focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all outline-none text-center">
                        <span class="text-xs font-semibold text-[#64748B]">extensions allowed per checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
