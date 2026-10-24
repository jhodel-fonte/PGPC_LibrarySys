<div class="min-h-full bg-[#F8FAFC]">

    <div class="relative z-10 mx-auto w-full max-w-[1600px] p-4 lg:p-6 space-y-6">

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Analytics</h1>
                <p class="mt-1 text-sm text-slate-500 font-medium">Track circulation dynamics, collection utilization, and patron engagement trends.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                {{-- Time Range Dropdown --}}
                <div class="relative">
                    <select 
                        wire:model.live="timeRange"
                        class="inline-flex h-10 items-center pl-9 pr-9 rounded-lg border border-[#CBD5E1] bg-white text-sm font-semibold text-[#102B70] shadow-sm appearance-none cursor-pointer hover:border-[#102B70] hover:bg-slate-50 focus:outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] transition-all"
                    >
                        <option value="7_days">Last 7 Days</option>
                        <option value="30_days">Last 30 Days</option>
                        <option value="90_days">Last 90 Days</option>
                        <option value="this_semester">This Semester</option>
                        <option value="this_year">Academic Year</option>
                    </select>
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#102B70]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#102B70]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- Export Report Action --}}
                <div class="relative" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        type="button" 
                        class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#102B70] hover:bg-[#0B225E] text-white px-4 text-sm font-semibold transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                        </svg>
                        <span>Export Report</span>
                        <svg class="w-3.5 h-3.5 ml-0.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div 
                        x-show="open" 
                        @click.away="open = false" 
                        x-cloak 
                        class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50 text-xs font-medium text-[#334155]"
                    >
                        <a href="javascript:void(0)" @click="open = false; window.print()" class="flex items-center gap-2 px-3.5 py-2 hover:bg-[#F8FAFC] text-[#0F172A]">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Official Report
                        </a>
                        <a href="javascript:void(0)" @click="open = false" class="flex items-center gap-2 px-3.5 py-2 hover:bg-[#F8FAFC] text-[#0F172A]">
                            <svg class="w-4 h-4 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export to CSV / Excel
                        </a>
                        <a href="javascript:void(0)" @click="open = false" class="flex items-center gap-2 px-3.5 py-2 hover:bg-[#F8FAFC] text-[#0F172A]">
                            <svg class="w-4 h-4 text-[#EF4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            CHED / ALCUCOA Summary
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. EXECUTIVE SUMMARY KPI CARDS (5 METRICS) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            {{-- KPI 1: Circulation Volume --}}
            <x-widget.data-card 
                title="Total Circulation" 
                :value="$kpiMetrics['totalCirculation']['value']" 
                change="↑ {{ $kpiMetrics['totalCirculation']['change'] }}" 
                :subtext="$kpiMetrics['totalCirculation']['subtext']" 
                changeColor="emerald" 
            />

            {{-- KPI 2: Active On-Loan --}}
            <x-widget.data-card 
                title="Active On-Loan" 
                :value="$kpiMetrics['activeLoans']['value']" 
                :change="$kpiMetrics['activeLoans']['change']" 
                :subtext="$kpiMetrics['activeLoans']['subtext']" 
                changeColor="blue" 
            />

            {{-- KPI 3: Patron Engagement --}}
            <x-widget.data-card 
                title="Active Patrons" 
                :value="$kpiMetrics['activePatrons']['value']" 
                change="↑ {{ $kpiMetrics['activePatrons']['change'] }}" 
                :subtext="$kpiMetrics['activePatrons']['subtext']" 
                changeColor="amber" 
            />

            {{-- KPI 4: Collection Turnover --}}
            <x-widget.data-card 
                title="Turnover Rate" 
                :value="$kpiMetrics['turnoverVelocity']['value']" 
                :change="$kpiMetrics['turnoverVelocity']['change']" 
                :subtext="$kpiMetrics['turnoverVelocity']['subtext']" 
                changeColor="emerald" 
            />

            {{-- KPI 5: Delinquent / Overdue --}}
            <x-widget.data-card 
                title="Overdue Items" 
                :value="$kpiMetrics['overdueResolution']['value']" 
                :change="$kpiMetrics['overdueResolution']['change']" 
                :subtext="$kpiMetrics['overdueResolution']['subtext']" 
                changeColor="emerald" 
            />

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- MULTI-SERIES LINE GRAPH: CIRCULATION DYNAMICS (8 COLS) --}}
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6 flex flex-col justify-between min-w-0">
                
                {{-- Chart Card Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#102B70]"></span>
                            <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Circulation Dynamics & Activity Trends</h2>
                        </div>
                        <p class="text-xs text-[#64748B] mt-0.5">
                            Comparative trajectory of loans, returns, and digital reservation requests over time
                        </p>
                    </div>

                    {{-- Metric Segment Filters --}}
                    <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-lg text-xs font-semibold text-slate-600">
                        <button 
                            type="button" 
                            wire:click="setCirculationMetric('all')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $circulationMetric === 'all' ? 'bg-white text-[#102B70] shadow-sm font-bold' : 'hover:text-[#0F172A]' }}"
                        >
                            All Streams
                        </button>
                        <button 
                            type="button" 
                            wire:click="setCirculationMetric('borrowed')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $circulationMetric === 'borrowed' ? 'bg-white text-[#102B70] shadow-sm font-bold' : 'hover:text-[#0F172A]' }}"
                        >
                            Loans Only
                        </button>
                        <button 
                            type="button" 
                            wire:click="setCirculationMetric('returned')"
                            class="px-2.5 py-1 rounded-md transition-all {{ $circulationMetric === 'returned' ? 'bg-white text-[#3B82F6] shadow-sm font-bold' : 'hover:text-[#0F172A]' }}"
                        >
                            Returns Only
                        </button>
                    </div>
                </div>

                {{-- TallStackUI Multi-Series Line Graph --}}
                <div class="py-4 my-auto" wire:loading.class="opacity-50 transition-opacity">
                    <x-widget.chart 
                        :series="$circulationTrend['series']" 
                        :labels="$circulationTrend['labels']" 
                        :colors="['#102B70', '#3B82F6', '#FCC719']"
                        line 
                        grid 
                        legend 
                        markers 
                        height="260" 
                    />
                </div>

                {{-- Insight Sub-Metrics Footer --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2.5 bg-slate-50/75 p-2.5 rounded-xl border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-[#EFF6FF] text-[#102B70] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-semibold block">Peak Checkout Day</span>
                            <span class="font-bold text-slate-800">Wednesday (68 loans/day)</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-slate-50/75 p-2.5 rounded-xl border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-semibold block">Return Compliance</span>
                            <span class="font-bold text-slate-800">94.6% On-Time Check-In</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-slate-50/75 p-2.5 rounded-xl border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-semibold block">Average Loan Period</span>
                            <span class="font-bold text-slate-800">4.2 Days (Limit: 7 Days)</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- DONUT CHART: COLLECTION CIRCULATION BY CATEGORY (4 COLS) --}}
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6 flex flex-col justify-between min-w-0">
                
                <div>
                    {{-- Chart Card Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Category Distribution</h2>
                            <p class="text-xs text-[#64748B] mt-0.5">Circulation share by Dewey classification</p>
                        </div>
                        <span class="px-2 py-1 rounded-md text-[11px] font-semibold bg-[#EFF6FF] text-[#102B70]">
                            Catalog
                        </span>
                    </div>

                    {{-- TallStackUI Donut Chart Component --}}
                    <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                        <x-widget.chart 
                            :series="$categoryDonut['series']" 
                            :labels="$categoryDonut['labels']" 
                            :colors="['#102B70', '#3B82F6', '#FCC719', '#10B981', '#8B5CF6', '#94A3B8']"
                            donut 
                            legend 
                            height="210" 
                            :centerLabel="['value' => $categoryDonut['total'], 'label' => 'Total Circulated']" 
                        />
                    </div>
                </div>

                {{-- Highlights Box --}}
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1">
                    <div class="flex items-center justify-between font-semibold text-slate-700">
                        <span>Dominant Subject Area</span>
                        <span class="text-[#102B70] font-bold">Computer Studies & Tech</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Represents over 28% of overall borrowing activity, driven by Capstone thesis research.
                    </p>
                </div>

            </div>

        </div>

        {{-- 4. SECONDARY ANALYTICAL ROW: GROUPED BAR CHART & HOURLY VELOCITY --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- GROUPED BAR CHART: DEPARTMENT UTILIZATION (7 COLS) --}}
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6 flex flex-col justify-between min-w-0">
                
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6]"></span>
                                <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Academic Department Resource Utilization</h2>
                            </div>
                            <p class="text-xs text-[#64748B] mt-0.5">
                                Comparison of home borrowing vs in-library room study usage across colleges
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                            Grouped Comparison
                        </span>
                    </div>

                    {{-- TallStackUI Grouped Bar Chart --}}
                    <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                        <x-widget.chart 
                            :series="$departmentBars['series']" 
                            :labels="$departmentBars['labels']" 
                            :colors="['#102B70', '#3B82F6']"
                            bar 
                            grid 
                            legend 
                            height="240" 
                        />
                    </div>
                </div>

                {{-- Department Footnote --}}
                <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100 text-slate-500">
                    <span>Highest Participation: <strong class="text-slate-800">BS Computer Science</strong></span>
                    <span class="text-slate-400">Audited across 1,290 verified student accounts</span>
                </div>

            </div>

            {{-- HOURLY CIRCULATION VELOCITY & DESK RUSH HOURS (5 COLS) --}}
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6 flex flex-col justify-between min-w-0">
                
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Circulation Desk Rush Hours</h2>
                            <p class="text-xs text-[#64748B] mt-0.5">Operating schedule traffic profile (8:00 AM – 5:00 PM)</p>
                        </div>
                        <span class="p-1.5 rounded-lg bg-[#EFF6FF] text-[#102B70]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>

                    {{-- Hourly Bar Distribution --}}
                    <div class="py-4 space-y-2.5">
                        @foreach ($hourlyTraffic as $slot)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-semibold">
                                    <div class="flex items-center gap-2">
                                        <span class="w-16 text-slate-600 font-medium">{{ $slot['hour'] }}</span>
                                        @if(isset($slot['tag']))
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $slot['isPeak'] ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $slot['tag'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-slate-700 tabular-nums">{{ $slot['count'] }} actions</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-500 {{ $slot['isPeak'] ? 'bg-[#FCC719]' : ($slot['pct'] > 60 ? 'bg-[#102B70]' : 'bg-[#3B82F6]') }}" 
                                        style="width: {{ $slot['pct'] }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Desk Staffing Recommendation --}}
                <div class="bg-[#EFF6FF] border border-[#DBEAFE] p-3 rounded-xl flex items-center gap-2.5 text-xs text-[#102B70]">
                    <svg class="w-4 h-4 text-[#102B70] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Staffing Alert:</strong> Highest circulation rush occurs at <strong>2:00 PM</strong>. Keep both checkout scanners active.</span>
                </div>

            </div>

        </div>

        {{-- 5. DEEP ANALYTICAL INSIGHTS: TOP CIRCULATED TITLES & PATRON DEMOGRAPHICS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- TOP 5 MOST CIRCULATED & HIGH DEMAND BOOKS (8 COLS) --}}
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Top Circulated & High-Demand Titles</h2>
                        <p class="text-xs text-[#64748B] mt-0.5">Most borrowed catalog items during the selected period</p>
                    </div>
                    <a href="{{ route(request()->is('librarian*') ? 'librarian.book-management.index' : 'admin.book-management.index') }}" class="text-xs font-bold text-[#102B70] hover:underline inline-flex items-center gap-1" wire:navigate>
                        View Full Catalog
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 mt-2">
                    @foreach ($topBooks as $book)
                        <div class="py-3.5 flex items-center justify-between gap-4 hover:bg-slate-50/60 rounded-xl px-2 transition-colors">
                            
                            {{-- Rank Badge & Title Info --}}
                            <div class="flex items-center gap-3.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-xs shrink-0 {{ $book['rank'] === 1 ? 'bg-[#FCC719] text-[#071943] shadow-sm' : ($book['rank'] === 2 ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-600') }}">
                                    #{{ $book['rank'] }}
                                </span>

                                <div class="min-w-0">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate" title="{{ $book['title'] }}">
                                        {{ $book['title'] }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500 mt-0.5">
                                        <span>{{ $book['author'] }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[10px]">
                                            {{ $book['call_number'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Borrows & Status --}}
                            <div class="flex items-center gap-4 shrink-0 text-right">
                                <div>
                                    <span class="text-sm font-extrabold text-slate-900 block tabular-nums">{{ $book['borrows'] }} loans</span>
                                    <span class="text-[10px] font-semibold text-emerald-600">{{ $book['trend'] }} velocity</span>
                                </div>

                                <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold {{ $book['status'] === 'High Demand' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]' }}">
                                    {{ $book['status'] }}
                                </span>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

            {{-- PATRON DEMOGRAPHICS & CATALOG HEALTH (4 COLS) --}}
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-5 lg:p-6 space-y-5">
                
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Patron Segmentation</h2>
                    <p class="text-xs text-[#64748B] mt-0.5">Circulation consumption by user category</p>
                </div>

                {{-- Demographic Percentage Bars --}}
                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>College Undergraduates</span>
                            <span class="font-bold text-[#102B70]">72%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-[#102B70] rounded-full" style="width: 72%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Senior High School</span>
                            <span class="font-bold text-[#3B82F6]">18%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-[#3B82F6] rounded-full" style="width: 18%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Faculty & Instructors</span>
                            <span class="font-bold text-amber-600">7%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-[#FCC719] rounded-full" style="width: 7%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Administrative Staff</span>
                            <span class="font-bold text-slate-600">3%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-slate-400 rounded-full" style="width: 3%"></div>
                        </div>
                    </div>
                </div>

                {{-- Operational Health Highlights --}}
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Acquisition Intelligence</span>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 space-y-1.5 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Unmet OPAC Search Queries
                        </div>
                        <p class="text-[11px] text-slate-500">
                            High query counts recorded for <em>"Artificial Intelligence Ethics"</em> and <em>"Forensic Ballistics Manual"</em> with zero current physical copies.
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
