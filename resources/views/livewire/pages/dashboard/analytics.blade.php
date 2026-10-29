<div class="min-h-full bg-[#F8FAFC]"
    x-data="{
        scrollToTarget(sec) {
            if (!sec) return;
            const targetMap = {
                'circulation': 'analytics-circulation',
                'programs': 'analytics-programs',
                'gender': 'analytics-demographics',
                'demographics': 'analytics-demographics',
                'operations': 'analytics-operations',
                'overview': 'analytics-overview'
            };
            const id = targetMap[sec] || ('analytics-' + sec);
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        init() {
            const check = () => {
                const params = new URLSearchParams(window.location.search);
                const s = params.get('section');
                if (s) {
                    this.scrollToTarget(s);
                }
            };
            this.$nextTick(() => {
                setTimeout(check, 200);
            });
            window.addEventListener('popstate', check);
            document.addEventListener('livewire:navigated', () => {
                setTimeout(check, 150);
            });
        }
    }"
    @scroll-to-section.window="scrollToTarget($event.detail)"
>

    <div class="relative z-10 mx-auto w-full max-w-[1600px] p-5 sm:p-6 lg:p-8 xl:p-10 space-y-8 lg:space-y-10 print:hidden">

        <!-- 1. Page Header & Primary Period Switcher -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div class="max-w-2xl">
                <h1 class="text-xl sm:text-2xl font-bold tracking-[-0.02em] text-[#102B70]">Analytics</h1>
                <p class="mt-0.5 text-xs sm:text-sm font-medium text-[#64748B]">Circulation velocity, patron engagement, and collection performance metrics.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @php($analyticsPrefix = \App\Services\EmployeeRoleService::getRoutePrefix())
                <a
                    href="{{ route($analyticsPrefix . 'analytics.ai') }}"
                    class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] hover:underline"
                >
                    AI evaluation &rarr;
                </a>

                {{-- Period Selector: Monthly Data vs Yearly Data --}}
                <div class="inline-flex items-center p-1 bg-white border border-[#E2E8F0] rounded-lg shadow-xs text-xs font-semibold text-[#102B70]">
                    <button 
                        type="button" 
                        wire:click="setTimePeriod('monthly')"
                        class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $timePeriod === 'monthly' ? 'bg-[#102B70] text-white shadow-xs' : 'hover:bg-slate-50 text-[#64748B] hover:text-[#0F172A]' }}"
                    >
                        Monthly Data
                    </button>
                    <button 
                        type="button" 
                        wire:click="setTimePeriod('yearly')"
                        class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $timePeriod === 'yearly' ? 'bg-[#102B70] text-white shadow-xs' : 'hover:bg-slate-50 text-[#64748B] hover:text-[#0F172A]' }}"
                    >
                        Yearly Data
                    </button>
                </div>

                {{-- Export Report Action --}}
                <div class="relative" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        type="button" 
                        class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-white border border-[#E2E8F0] hover:border-[#102B70] text-[#102B70] px-3.5 text-xs font-semibold transition-all shadow-xs cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                        </svg>
                        <span>Export Analytics</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div 
                        x-show="open" 
                        @click.away="open = false" 
                        x-cloak 
                        class="absolute right-0 mt-1.5 w-48 bg-white border border-[#E2E8F0] rounded-xl shadow-lg py-1 z-50 text-xs font-semibold text-[#334155]"
                    >
                        <a href="javascript:void(0)" @click="open = false; window.print()" class="flex items-center gap-2 px-3.5 py-2 hover:bg-[#F8FAFC] text-[#0F172A]">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Summary Report
                        </a>
                        <a href="javascript:void(0)" @click="open = false" class="flex items-center gap-2 px-3.5 py-2 hover:bg-[#F8FAFC] text-[#0F172A]">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export to CSV / Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. PRIMARY EXECUTIVE KPI METRICS --}}
        <div id="analytics-overview" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 scroll-mt-24">
            
            {{-- KPI 1: Total Number of Students --}}
            <x-widget.data-card 
                title="Total Students" 
                :value="$kpiMetrics['totalStudents']['value']" 
                :change="$kpiMetrics['totalStudents']['change']" 
                :subtext="$kpiMetrics['totalStudents']['subtext']" 
                changeColor="emerald" 
            />

            {{-- KPI 2: Total Number of Books --}}
            <x-widget.data-card 
                title="Total Books" 
                :value="$kpiMetrics['totalBooks']['value']" 
                :change="$kpiMetrics['totalBooks']['change']" 
                :subtext="$kpiMetrics['totalBooks']['subtext']" 
                changeColor="blue" 
            />

            {{-- KPI 3: Due Books Today --}}
            <x-widget.data-card 
                title="Due Books Today" 
                :value="$kpiMetrics['dueBooksToday']['value']" 
                :change="$kpiMetrics['dueBooksToday']['change']" 
                :subtext="$kpiMetrics['dueBooksToday']['subtext']" 
                changeColor="amber" 
            />

            {{-- KPI 4: Peak Hour --}}
            <x-widget.data-card 
                title="Peak Hour" 
                :value="$kpiMetrics['peakHour']['value']" 
                :subtext="$kpiMetrics['peakHour']['subtext']" 
                changeColor="amber" 
                valueSize="text-xl"
            />

            {{-- KPI 5: Gender Breakdown Dominance --}}
            <x-widget.data-card 
                title="Most Active Gender" 
                :value="$kpiMetrics['genderDominance']['value']" 
                :change="$kpiMetrics['genderDominance']['change']" 
                :subtext="$kpiMetrics['genderDominance']['subtext']" 
                changeColor="emerald" 
                valueSize="text-xl"
            />

        </div>

        {{-- 3. CIRCULATION SECTION --}}
        <div id="analytics-circulation" class="space-y-4 scroll-mt-24">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-3 border-b border-[#E2E8F0]">
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-[#102B70]"></span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#0F172A] tracking-tight">Circulation</h2>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                {{-- MAIN CHART --}}
                <div class="lg:col-span-8 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0">
                    
                    <div>
                        {{-- Minimalist Chart Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#F1F5F9]">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">
                                    {{ $timePeriod === 'monthly' ? ($viewMode === 'users_per_month' ? 'Users Per Month' : 'Circulation Trends') : 'Yearly Progression' }}
                                </h3>
                                <p class="text-xs text-[#64748B] mt-0.5">
                                    {{ $timePeriod === 'monthly' ? 'Monthly overview (Jan – Dec)' : 'Annual comparison (2022 – 2026)' }}
                                </p>
                            </div>

                            {{-- Mode Selector Dropdowns --}}
                            @if ($timePeriod === 'monthly')
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="relative">
                                        <select 
                                            wire:model.live="viewMode" 
                                            class="text-xs font-semibold text-[#102B70] bg-[#F8FAFC] border border-[#CBD5E1] rounded-lg pl-2.5 pr-7 py-1.5 cursor-pointer focus:outline-none focus:border-[#102B70] hover:border-[#102B70] transition-colors shadow-xs"
                                        >
                                            <option value="circulation">Circulation Trends</option>
                                            <option value="users_per_month">Users Per Month</option>
                                        </select>
                                    </div>

                                    @if ($viewMode === 'circulation')
                                        <div class="relative">
                                            <select 
                                                wire:model.live="circulationMetric" 
                                                class="text-xs font-semibold text-[#334155] bg-[#F8FAFC] border border-[#CBD5E1] rounded-lg pl-2.5 pr-7 py-1.5 cursor-pointer focus:outline-none focus:border-[#102B70] hover:border-[#102B70] transition-colors shadow-xs"
                                            >
                                                <option value="all">All (Loans & Returns)</option>
                                                <option value="borrowed">Loans Only</option>
                                                <option value="returned">Returns Only</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="relative">
                                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                                        5-Year Historical View
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Chart Canvas --}}
                        <div class="my-auto py-2" wire:loading.class="opacity-50 transition-opacity">
                            @if ($timePeriod === 'monthly')
                                <x-widget.graph.main 
                                    :series="$monthlyTrend['series']" 
                                    :labels="$monthlyTrend['labels']" 
                                    line 
                                    grid 
                                    legend 
                                    markers 
                                    height="300" 
                                />
                            @else
                                <x-widget.graph.main 
                                    :series="$yearlyTrend['series']" 
                                    :labels="$yearlyTrend['labels']" 
                                    bar 
                                    grid 
                                    legend 
                                    height="300" 
                                />
                            @endif
                        </div>
                    </div>

                </div>

                {{-- CHART SUMMARY CARDS --}}
                <div class="lg:col-span-4 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9]">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">Chart Summary</h3>
                            <p class="text-xs text-[#64748B] mt-0.5">Key circulation & patron metrics</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                            Summary
                        </span>
                    </div>

                    {{-- Summary Sub-Cards --}}
                    <div class="py-3 space-y-3 my-auto">
                        
                        {{-- Card 1: Peak Circulation Month --}}
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-[#EFF6FF] text-[#102B70] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-[#64748B] font-semibold block uppercase tracking-wider">Peak Period</span>
                                <span class="font-bold text-[#0F172A] text-sm mt-0.5 block truncate">
                                    {{ $timePeriod === 'monthly' ? $monthlyTrend['peakMonth'] : '2026 (29,400 loans)' }}
                                </span>
                                <span class="text-xs text-[#64748B] font-medium block truncate">
                                    {{ $timePeriod === 'monthly' ? 'Midterm examination rush' : 'Highest volume recorded' }}
                                </span>
                            </div>
                        </div>

                        {{-- Card 2: Average Monthly Users --}}
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-[#EFF6FF] text-[#3B82F6] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-[#64748B] font-semibold block uppercase tracking-wider">Average Monthly Users</span>
                                <span class="font-bold text-[#0F172A] text-sm mt-0.5 block truncate">{{ number_format($monthlyTrend['avgMonthlyUsers'] ?? 988) }} Patrons / Month</span>
                                <span class="text-xs text-[#64748B] font-medium block truncate">Consistent campus engagement</span>
                            </div>
                        </div>

                        {{-- Card 3: Year-over-Year Growth --}}
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-[#64748B] font-semibold block uppercase tracking-wider">Year-over-Year Growth</span>
                                <span class="font-bold text-[#0F172A] text-sm mt-0.5 block truncate">{{ $yearlyTrend['growthRate'] }}</span>
                                <span class="text-xs text-emerald-700 font-medium block truncate">Expanding student utilization</span>
                            </div>
                        </div>

                        {{-- Card 4: Return Turnaround Compliance --}}
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] text-[#64748B] font-semibold block uppercase tracking-wider">Return Compliance</span>
                                <span class="font-bold text-[#0F172A] text-sm mt-0.5 block truncate">91.4% On-Time</span>
                                <span class="text-xs text-[#64748B] font-medium block truncate">4.2 days avg turnaround</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        {{-- 4. PROGRAMS SECTION --}}
        <div id="analytics-programs" class="space-y-4 scroll-mt-24">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-3 border-b border-[#E2E8F0]">
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-[#102B70]"></span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#0F172A] tracking-tight">Programs &amp; Demographics</h2>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                {{-- PROGRAM / COURSE BREAKDOWN --}}
                <div class="lg:col-span-8 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0">
                    
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#F1F5F9]">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">Program / Course Breakdown</h3>
                                <p class="text-xs text-[#64748B] mt-0.5">
                                    Utilization and borrowing by academic degree
                                </p>
                            </div>
                        </div>

                        {{-- Grouped Bar Chart of Program Usage --}}
                        <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                            <x-widget.graph.main 
                                :series="$programBreakdown['series']" 
                                :labels="$programBreakdown['labels']" 
                                :colors="['#102B70', '#3B82F6']"
                                bar 
                                grid 
                                legend 
                                height="210" 
                            />
                        </div>
                    </div>

                    {{-- Program Details Ranking Table --}}
                    <div class="pt-3 border-t border-[#F1F5F9]">
                        <div 
                            class="overflow-y-auto divide-y divide-[#F1F5F9] text-xs pr-1"
                            style="max-height: 200px;"
                        >
                            @foreach ($programBreakdown['programs'] as $prog)
                                <div class="h-9 flex items-center justify-between">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-12 font-bold text-slate-800">{{ $prog['code'] }}</span>
                                        <span class="text-slate-700 truncate font-medium" title="{{ $prog['name'] }}">{{ $prog['name'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0 text-right">
                                        <span class="text-[#64748B]">{{ $prog['users'] }} students</span>
                                        <span class="font-bold text-[#102B70] tabular-nums">{{ $prog['loans'] }} borrows</span>
                                        <span class="w-9 text-right font-semibold text-[#64748B]">{{ $prog['pct'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                
                {{-- GENDER BREAKDOWN --}}
                <div id="analytics-demographics" class="lg:col-span-4 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0 scroll-mt-24">
                    
                    <div>
                        {{-- Card Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#F1F5F9]">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">Gender Breakdown</h3>
                                <p class="text-xs text-[#64748B] mt-0.5">Borrowing participation by gender</p>
                            </div>
                        </div>

                        {{-- Gender Breakdown Chart --}}
                        <div class="py-4 flex items-center justify-center min-h-[250px]" wire:loading.class="opacity-50 transition-opacity">
                            <x-charts.pie 
                                :series="$genderBreakdown['donutSeries']" 
                                :labels="['Female', 'Male']" 
                                :colors="['#102B70', '#3B82F6']" 
                                :height="250"
                                pie 
                                legend 
                                legend-position="bottom"
                                legend-text-size="text-xs sm:text-sm font-semibold"
                                legend-value-size="text-xs sm:text-sm font-bold"
                                suffix="Students"
                            />
                        </div>
                    </div>

                    {{-- Gender Comparison Breakdown & Details --}}
                    <div class="pt-3 border-t border-[#F1F5F9] divide-y divide-[#F1F5F9] text-xs">
                        <!-- Female -->
                        <div class="py-3 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#102B70] shrink-0"></span>
                                    <span class="font-bold text-[#0F172A]">Female</span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 text-right">
                                    <span class="text-[#64748B] font-medium">{{ number_format($genderBreakdown['female']['count']) }} Students</span>
                                    <span class="font-bold text-[#102B70] tabular-nums">{{ $genderBreakdown['female']['avgLoans'] }}</span>
                                    <span class="w-9 text-right font-semibold text-[#64748B]">{{ $genderBreakdown['female']['pct'] }}%</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-[#102B70] rounded-full transition-all duration-500" style="width: {{ $genderBreakdown['female']['pct'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-[#64748B]">
                                <span class="truncate">Top Categories: <strong class="text-slate-800 font-semibold">{{ $genderBreakdown['female']['preferredGenre'] }}</strong></span>
                            </div>
                        </div>

                        <!-- Male -->
                        <div class="py-3 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6] shrink-0"></span>
                                    <span class="font-bold text-[#0F172A]">Male</span>
                                    @if($genderBreakdown['dominantGender'] === 'Lalaki (Male)' || str_contains($genderBreakdown['dominantGender'], 'Male'))
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">Top User</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 shrink-0 text-right">
                                    <span class="text-[#64748B] font-medium">{{ number_format($genderBreakdown['male']['count']) }} patrons</span>
                                    <span class="font-bold text-[#3B82F6] tabular-nums">{{ $genderBreakdown['male']['avgLoans'] }}</span>
                                    <span class="w-9 text-right font-semibold text-[#64748B]">{{ $genderBreakdown['male']['pct'] }}%</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-[#3B82F6] rounded-full transition-all duration-500" style="width: {{ $genderBreakdown['male']['pct'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-[#64748B]">
                                <span class="truncate">Top Categories: <strong class="text-slate-800 font-semibold">{{ $genderBreakdown['male']['preferredGenre'] }}</strong></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- 5. OPERATIONS SECTION --}}
        <div id="analytics-operations" class="space-y-4 scroll-mt-24">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-3 border-b border-[#E2E8F0]">
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-[#102B70]"></span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#0F172A] tracking-tight">Operations &amp; Schedule</h2>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                {{-- DUE BOOKS TODAY (6 COLS) --}}
                <div class="lg:col-span-6 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0">
                    
                    <div>
                        {{-- Header with Due Books Today Highlight --}}
                        <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9]">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">Due Books Today ({{ count($dueBooksToday) }} Total)</h3>
                                <p class="text-xs text-[#64748B] mt-0.5">{{ $kpiMetrics['dueBooksToday']['change'] }} &bull; {{ $kpiMetrics['dueBooksToday']['subtext'] }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/60">
                                Schedule
                            </span>
                        </div>

                        {{-- Due Books Today Mini List --}}
                        <div class="py-2.5 divide-y divide-[#F1F5F9] text-xs">
                            @foreach ($dueBooksToday as $book)
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-slate-800 truncate" title="{{ $book['title'] }}">
                                            {{ $book['title'] }}
                                        </h4>
                                        <p class="text-[11px] text-[#64748B] mt-0.5">
                                            {{ $book['borrower'] }} ({{ $book['program'] }}) &bull; Due {{ $book['due_time'] }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 rounded text-[11px] font-semibold {{ $book['status'] === 'Returned' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($book['status'] === 'Overdue' ? 'bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA]' : 'bg-amber-50 text-amber-800 border border-amber-200/60') }}">
                                        {{ $book['status'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#F1F5F9] flex items-center justify-between text-xs text-[#64748B]">
                        <span>Automated return reminders dispatched at 8:00 AM</span>
                        <a href="javascript:void(0)" class="text-[#102B70] font-semibold hover:underline">View All Due Items &rarr;</a>
                    </div>

                </div>

                {{-- PEAK CIRCULATION HOURS & HOURLY VELOCITY (6 COLS) --}}
                <div class="lg:col-span-6 bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-5 sm:p-6 flex flex-col justify-between min-w-0">
                    
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-[#F1F5F9]">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] tracking-tight">Circulation Desk Rush Hours</h3>
                                <p class="text-xs text-[#64748B] mt-0.5">Operating schedule traffic profile (8:00 AM – 5:00 PM)</p>
                            </div>
                            <span class="p-1 rounded-lg bg-[#EFF6FF] text-[#102B70]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                        </div>

                        {{-- Hourly Bar Distribution --}}
                        <div class="py-3 space-y-2.5">
                            @foreach ($hourlyTraffic as $slot)
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs font-semibold">
                                        <div class="flex items-center gap-2">
                                            <span class="w-16 text-[#64748B] font-medium text-[11px]">{{ $slot['hour'] }}</span>
                                            @if(isset($slot['tag']))
                                                <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded {{ $slot['isPeak'] ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-100 text-[#334155]' }}">
                                                    {{ $slot['tag'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-slate-700 tabular-nums text-[11px] font-medium">{{ $slot['count'] }} actions</span>
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

                    {{-- Desk Recommendation Alert --}}
                    <div class="bg-[#EFF6FF] border border-[#DBEAFE] p-3 rounded-lg flex items-center gap-2.5 text-xs text-[#102B70] mt-2">
                        <svg class="w-4 h-4 text-[#102B70] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[11px] leading-relaxed"><strong>Peak Activity:</strong> Busiest rush hour is <strong>2:00 PM (72 actions)</strong>. Keep both checkout stations operational.</span>
                    </div>

                </div>

            </div>
        </div>

    </div>
    
    {{-- Print & PDF Official Institutional Template --}}
    <div class="hidden print:block w-full">
        <x-template.analytics 
            :kpiMetrics="$kpiMetrics"
            :monthlyTrend="$monthlyTrend"
            :yearlyTrend="$yearlyTrend"
            :genderBreakdown="$genderBreakdown"
            :programBreakdown="$programBreakdown"
            :dueBooksToday="$dueBooksToday"
            :hourlyTraffic="$hourlyTraffic"
            :timePeriod="$timePeriod"
        />
    </div>
</div>
