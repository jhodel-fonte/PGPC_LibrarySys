<div class="min-h-full bg-[#eef3f7]">
    {{-- <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-end justify-center opacity-[0.018] z-0 pb-10">
        <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
    </div> --}}

    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-end justify-center opacity-[0.018] z-0 pb-10">
    </div>

    <div class="mx-auto w-full max-w-[1600px] max-h-auto p-4 lg:p-6 space-y-5">
        <!-- Top Row: Hero and Attention -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.05fr)_minmax(320px,1fr)] gap-5">
            <!-- Greeting Banner -->
            <x-widget.greeting-banner staffName="{{ Auth::user()->name ?? 'Admin' }}" />
            
            <!-- Requires Attention -->
            <div class="h-full">
                <x-dashboard.requires-attention 
                    :overdueCount="$overdueItems" 
                    :pendingCount="$pendingReservations" 
                />
            </div>
        </div>

        <!-- Middle Row: 3 Diagram Cards (Multiple Series, Donut, Grouped Bars) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
            
            <!-- 1. Multiple Series Line Chart: Borrowing and Returning Trends -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 flex flex-col justify-between min-h-[270px]">
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[#0284c7] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l6-4 4 4 8-6" />
                            </svg>
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 leading-tight">Borrowing and Returning Trends</h2>
                    </div>

                    <div class="relative">
                        <select wire:model.live="trendsFilter" class="text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 pr-6 appearance-none cursor-pointer focus:outline-none focus:border-[#0284c7]">
                            <option value="7_days">Last 7 Days</option>
                            <option value="30_days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                        </select>
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                </div>

                <!-- TallStackUI Multiple Series Chart -->
                <x-widget.chart 
                    :series="$borrowingTrendsSeries" 
                    :labels="$borrowingTrendsLabels" 
                    line 
                    grid 
                    legend 
                    markers 
                    height="180" 
                />
            </div>

            <!-- 2. Donut Chart: Most Borrowed Book Categories -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 flex flex-col justify-between min-h-[270px]">
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[#0284c7] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 leading-tight">Most Borrowed Book Categories</h2>
                    </div>

                    <div class="relative">
                        <select wire:model.live="categoryFilter" class="text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 pr-6 appearance-none cursor-pointer focus:outline-none focus:border-[#0284c7]">
                            <option value="all_time">All Time</option>
                            <option value="this_month">This Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                </div>

                <!-- TallStackUI Donut Chart -->
                <x-widget.chart 
                    :series="$categoryDonutSeries" 
                    :labels="$categoryDonutLabels" 
                    donut 
                    legend 
                    height="180" 
                    :centerLabel="['value' => $categoryDonutTotal, 'label' => 'Total Borrowed']" 
                />
            </div>

            <!-- 3. Grouped Bar Chart: Monthly Library Activity -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 flex flex-col justify-between min-h-[270px]">
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[#0284c7] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16v-4m4 4v-8m4 4v-10m4 10v-6" />
                            </svg>
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 leading-tight">Monthly Library Activity</h2>
                    </div>

                    <div class="relative">
                        <select wire:model.live="activityFilter" class="text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 pr-6 appearance-none cursor-pointer focus:outline-none focus:border-[#0284c7]">
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                        </select>
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                </div>

                <!-- TallStackUI Grouped Bar Chart -->
                <x-widget.chart 
                    :series="$monthlyActivitySeries" 
                    :labels="$monthlyActivityLabels" 
                    bar 
                    grid 
                    legend 
                    height="180" 
                />
            </div>

        </div>

        <!-- Bottom Row: Current Borrowers and Most Borrowed -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.05fr)_minmax(320px,1fr)] gap-5 items-start">
            <!-- Current Borrowers -->
            <x-dashboard.current-borrowers :borrowers="$currentBorrowers" />

            <!-- Most Borrowed Titles -->
            <x-dashboard.most-borrowed-titles :titles="$mostBorrowedTitles" />
        </div>
    </div>
</div>