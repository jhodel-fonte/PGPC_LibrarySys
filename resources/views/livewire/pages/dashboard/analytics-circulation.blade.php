<div class="min-h-full bg-[#F8FAFC] flex flex-col font-sans">
    <div class="mx-auto w-full max-w-[1600px] p-6 lg:p-8 xl:p-10 space-y-8 lg:space-y-10">

        <!-- 1. Page Header (Read-Only Analytics) -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="w-2.5 h-6 rounded-full bg-[#102B70]"></span>
                    <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Circulation Analytics & Intelligence</h1>
                </div>
                <p class="mt-1 text-sm text-slate-500 font-medium">Monitoring circulation velocity, book demand, academic program engagement, and return compliance.</p>
                <p class="mt-1 text-sm text-slate-600">Inside-library loans in selected period: <strong>{{ number_format($insideLibraryCount) }}</strong></p>
            </div>

            <!-- Date Coverage Filter & Export / Quick Print Action -->
            <div class="flex flex-wrap gap-2 sm:shrink-0">
                {{-- Date Coverage Dropdown --}}
                <div class="relative inline-flex">
                    <select 
                        wire:model.live="dateCoverage" 
                        class="h-10 appearance-none rounded-xl bg-white border border-[#CBD5E1] hover:border-[#102B70] text-[#102B70] p-2 text-sm font-bold transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-[#102B70] cursor-pointer"
                        aria-label="Choose date coverage"
                    >
                        <option value="all">Coverage: All Time</option>
                        <optgroup label="Hours & Days">
                            <option value="last_hour">Last Hour</option>
                            <option value="today">Today (Past 24 Hours)</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                        </optgroup>
                        <optgroup label="Months">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </optgroup>
                        <optgroup label="Years">
                            @for ($year = now()->year; $year >= 2024; $year--)
                                @if ($year === now()->year)
                                    <option value="this_year">This Year</option>
                                @else
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endif
                            @endfor
                        </optgroup>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- Print Analytics Report --}}
                <button 
                    type="button" 
                    @click="window.print()" 
                    class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#102B70] hover:bg-[#051537] text-[#ffffff] px-4 text-sm font-bold transition-all shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                        <path d="M6 14h12v8H6z"/>
                    </svg>
                    <span>Print Analytics Report</span>
                </button>
            </div>
        </div>

        <!-- 2. Top KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- KPI 1: Active Loans --}}
            <x-widget.data-card 
                title="Current Borrowed Books" 
                :value="number_format($activeCount)" 
                subtext="Active borrowed book" 
                changeColor="blue" 
            />

            {{-- KPI 2: Overdue Loans --}}
            <x-widget.data-card 
                title="Overdue Items" 
                :value="number_format($overdueCount)" 
                subtext="past assigned due dates" 
                :changeColor="$overdueCount > 0 ? 'amber' : 'emerald'" 
            />

            {{-- KPI 3: Unique Borrowers --}}
            <x-widget.data-card 
                title="Unique Borrowers" 
                :value="number_format($uniqueBorrowersCount)" 
                subtext="Active Students" 
                changeColor="blue" 
            />

            {{-- KPI 4: On-Time Return Rate --}}
            <x-widget.data-card 
                title="Turnaround Compliant" 
                :value="$onTimeReturnRate" 
                subtext="4.2 days avg turnaround" 
            />
        </div>

        <!-- 3. TOP GRAPHS SECTION: Lending Velocity & Loan Status Ratio -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- Weekly Borrowing & Return Velocity (7 cols) --}}
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                                <h2 class="text-base font-bold text-[#0F172A] tracking-tight">7-Day Lending Velocity</h2>
                            </div>
                            <p class="text-sm text-[#64748B] mt-0.5">Daily volume of loans issued vs. returns received</p>
                        </div>
                        <span class="text-sm font-semibold px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                            Past 7 Days
                        </span>
                    </div>

                    <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                        <x-widget.graph.main 
                            :series="$weeklyTrend['series']" 
                            :labels="$weeklyTrend['labels']" 
                            line 
                            grid 
                            legend 
                            markers 
                            height="240" 
                        />
                    </div>
                </div>
            </div>

            {{-- Loan Status Composition Donut (5 cols with generous space & bottom metrics) --}}
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                            <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Loan Status Ratio</h2>
                        </div>
                        <p class="text-sm text-[#64748B] mt-0.5">Distribution of all active & archived loans</p>
                    </div>
                    <span class="text-sm font-semibold px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 border border-slate-200">
                        {{ number_format($statusBreakdown['total']) }} Total
                    </span>
                </div>

                {{-- Vertically centered graph container with generous breathing room --}}
                <div class="my-auto py-5 sm:py-6 flex items-center justify-center w-full" wire:loading.class="opacity-50 transition-opacity">
                    <x-widget.graph.main 
                        :series="$statusBreakdown['series']" 
                        :labels="$statusBreakdown['labels']" 
                        :colors="$statusBreakdown['colors']" 
                        donut 
                        legend 
                        height="240" 
                        :centerLabel="['value' => number_format($statusBreakdown['total']), 'label' => 'Total Records']" 
                    />
                </div>

                {{-- Status Breakdown Pills Footer to fill and balance the card --}}
                <div class="pt-3.5 border-t border-slate-100 grid grid-cols-3 gap-2.5 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50/80 border border-slate-200/80 flex flex-col">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                            <span class="text-xs text-slate-500 font-semibold">Active</span>
                        </div>
                        <span class="text-sm font-bold text-[#102B70] mt-0.5">{{ $statusBreakdown['series'][0] ?? 0 }} loans</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-red-50/60 border border-red-100 flex flex-col">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                            <span class="text-xs text-red-600 font-semibold">Overdue</span>
                        </div>
                        <span class="text-sm font-bold text-red-700 mt-0.5">{{ $statusBreakdown['series'][1] ?? 0 }} loans</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100 flex flex-col">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                            <span class="text-xs text-emerald-600 font-semibold">Returned</span>
                        </div>
                        <span class="text-sm font-bold text-emerald-700 mt-0.5">{{ $statusBreakdown['series'][2] ?? 0 }} records</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 4. BOOK POPULARITY & ACQUISITION DEMAND -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3.5 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                        <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Most Borrowed Books & Acquisition Needs</h2>
                    </div>
                    <p class="text-sm text-[#64748B] mt-0.5">High-demand titles ranked by circulation frequency, highlighting books requiring additional copies.</p>
                </div>
                <span class="text-sm font-semibold px-3 py-1 rounded-lg bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] self-start sm:self-auto">
                    Top Circulating Titles
                </span>
            </div>

            <div class="admin-mobile-table-scroll overflow-x-auto">
                <table class="admin-mobile-table w-full text-left text-sm font-sans">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">Rank</th>
                            <th class="py-3 px-4">Book Title & Author</th>
                            <th class="py-3 px-4">Discipline / Category</th>
                            <th class="py-3 px-4 text-center">Borrow Frequency</th>
                            <th class="py-3 px-4 text-center">Physical Copies</th>
                            <th class="py-3 px-4 text-right">Acquisition Recommendation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($popularBooks as $index => $book)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td data-label="Rank" class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    #{{ $index + 1 }}
                                </td>
                                <td data-label="Book and author" class="py-3.5 px-4 min-w-[260px]">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[#102B70] text-sm leading-tight">{{ $book['title'] }}</span>
                                        <span class="text-sm text-slate-500 font-medium mt-0.5">by {{ $book['author'] }}</span>
                                    </div>
                                </td>
                                <td data-label="Category" class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                                        {{ $book['category'] }}
                                    </span>
                                </td>
                                <td data-label="Borrow frequency" class="py-3.5 px-4 text-center whitespace-nowrap"><span>
                                    <span class="font-bold text-[#0F172A] text-sm">{{ $book['borrows'] }}</span>
                                    <span class="text-slate-500 text-sm ml-1">borrows</span>
                                </span></td>
                                <td data-label="Physical copies" class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 font-semibold text-sm">
                                        <span class="{{ $book['available_copies'] <= 1 ? 'text-amber-600 font-bold' : 'text-slate-700' }}">{{ $book['available_copies'] }} avail</span>
                                        <span class="text-slate-300">/</span>
                                        <span class="text-slate-500">{{ $book['total_copies'] }} total</span>
                                    </div>
                                </td>
                                <td data-label="Recommendation" class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($book['needs_copies'])
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Needs Additional Copies
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Adequate Copies in Stacks
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. BORROWING BY PROGRAM & YEAR LEVEL -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- Program Borrowing Breakdown (7 cols) --}}
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                                <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Borrowing by Academic Degree Program</h2>
                            </div>
                            <p class="text-sm text-[#64748B] mt-0.5">Comparison of active patron reach and total borrowing volume per program</p>
                        </div>
                        <span class="text-sm font-semibold px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE]">
                            Top: {{ $programBreakdown['topProgram'] }}
                        </span>
                    </div>

                    <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                        <x-widget.graph.main 
                            :series="$programBreakdown['series']" 
                            :labels="$programBreakdown['labels']" 
                            :colors="['#102B70', '#3B82F6']"
                            bar 
                            grid 
                            legend 
                            height="220" 
                        />
                    </div>
                </div>

                {{-- Program Details Ranking Table (Same as Main Analytics Page) --}}
                <div class="pt-4 border-t border-slate-100">
                    <div 
                        class="overflow-y-auto divide-y divide-slate-100 text-sm pr-1.5"
                        style="max-height: 200px;"
                    >
                        @foreach ($programBreakdown['programs'] as $prog)
                            <div class="h-10 flex items-center justify-between">
                                <div class="flex items-center gap-4 min-w-0">
                                    <span class="w-12 font-bold text-slate-800">{{ $prog['code'] }}</span>
                                    <span class="text-slate-800 truncate" title="{{ $prog['name'] }}">{{ $prog['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-4 shrink-0 text-right">
                                    <span class="text-slate-600 font-medium">{{ $prog['users'] }} students</span>
                                    <span class="font-bold text-[#102B70] tabular-nums">{{ $prog['loans'] }} borrows</span>
                                    <span class="w-10 text-right text-xs font-bold text-slate-500">{{ $prog['pct'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Year Level Distribution (5 cols) --}}
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Borrowing by Student Year Level</h2>
                            <p class="text-sm text-[#64748B] mt-0.5">Distribution of library loans across academic levels</p>
                        </div>
                    </div>

                    <div class="py-4" wire:loading.class="opacity-50 transition-opacity">
                        <x-widget.graph.main 
                            :series="$yearLevelDist['series']" 
                            :labels="$yearLevelDist['labels']" 
                            :colors="$yearLevelDist['colors']" 
                            donut 
                            legend 
                            height="210" 
                        />
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 space-y-2.5 text-sm">
                    @foreach($yearLevelDist['details'] as $yr)
                        <div class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0">
                            <div class="min-w-0">
                                <span class="font-bold text-[#0F172A]">{{ $yr['year'] }}</span>
                                <span class="text-xs text-slate-500 block truncate">{{ $yr['desc'] }}</span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-bold text-[#102B70]">{{ $yr['loans'] }} loans</span>
                                <span class="text-xs text-slate-500 block">({{ $yr['pct'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- 6. RETURN PERFORMANCE & COLLECTION INVENTORY SNAPSHOT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- Return Performance & Overdue Turnaround (6 cols) --}}
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                                <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Return Performance & Overdue Analysis</h2>
                            </div>
                            <p class="text-sm text-[#64748B] mt-0.5">Borrower punctuality, on-time turnaround, and overdue aging breakdown</p>
                        </div>
                    </div>

                    <!-- Highlight Metrics -->
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">On-Time Return Rate</span>
                            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $returnPerformance['onTimeRate'] }}</div>
                            <span class="text-sm text-emerald-600 font-medium mt-0.5 block">Returned on or before due date</span>
                        </div>
                        <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#102B70]">Avg Turnaround Time</span>
                            <div class="text-2xl font-extrabold text-[#102B70] mt-1">{{ $returnPerformance['avgTurnaround'] }}</div>
                            <span class="text-sm text-slate-600 font-medium mt-0.5 block">From checkout to book return</span>
                        </div>
                    </div>

                    <!-- Overdue Aging Meter -->
                    <div class="space-y-3 pt-2">
                        <span class="text-sm font-bold text-slate-700 block">Overdue Duration Distribution (Late Returns)</span>
                        @foreach($returnPerformance['aging'] as $age)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm font-semibold">
                                    <span class="text-slate-600">{{ $age['range'] }}</span>
                                    <span class="text-slate-800 font-bold">{{ $age['count'] }} loans ({{ $age['pct'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $age['color'] === 'amber' ? 'bg-amber-400' : ($age['color'] === 'orange' ? 'bg-orange-500' : 'bg-red-500') }}" style="width: {{ $age['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Collection Inventory Availability Snapshot (6 cols) --}}
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                                <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Collection & Inventory Availability</h2>
                            </div>
                            <p class="text-sm text-[#64748B] mt-0.5">Physical copy availability, active circulation volume, and shelf capacity</p>
                        </div>
                        <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                            {{ $collectionSnapshot['availableRate'] }}% Available
                        </span>
                    </div>

                    <!-- Multi-segment Availability Bar -->
                    <div class="my-4">
                        <div class="flex items-center justify-between text-sm text-slate-600 mb-2 font-medium">
                            <span>Total Cataloged Collection Copies</span>
                            <span class="font-bold text-[#0F172A] text-sm">{{ $collectionSnapshot['total'] }} copies</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden flex">
                            <div class="bg-emerald-500 h-full" style="width: {{ ($collectionSnapshot['available'] / $collectionSnapshot['total']) * 100 }}%" title="Available: {{ $collectionSnapshot['available'] }}"></div>
                            <div class="bg-[#102B70] h-full" style="width: {{ ($collectionSnapshot['borrowed'] / $collectionSnapshot['total']) * 100 }}%" title="Borrowed: {{ $collectionSnapshot['borrowed'] }}"></div>
                            <div class="bg-amber-400 h-full" style="width: {{ ($collectionSnapshot['maintenance'] / $collectionSnapshot['total']) * 100 }}%" title="Maintenance: {{ $collectionSnapshot['maintenance'] }}"></div>
                        </div>
                    </div>

                    <!-- 4 Category Breakdown Cards -->
                    <div class="grid grid-cols-2 gap-3 text-sm pt-1">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                            <div class="min-w-0">
                                <span class="text-slate-500 text-xs block">Available on Shelves</span>
                                <span class="font-bold text-[#0F172A] text-sm">{{ $collectionSnapshot['available'] }} copies</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-[#102B70] shrink-0"></span>
                            <div class="min-w-0">
                                <span class="font-bold text-[#0F172A] text-sm">{{ $collectionSnapshot['borrowed'] }} copies</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-amber-400 shrink-0"></span>
                            <div class="min-w-0">
                                <span class="text-slate-500 text-xs block">Reserved / Staged</span>
                                <span class="font-bold text-[#0F172A] text-sm">{{ $collectionSnapshot['maintenance'] }} copies</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full bg-slate-300 shrink-0"></span>
                            <div class="min-w-0">
                                <span class="text-slate-500 text-xs block">In Maintenance</span>
                                <span class="font-bold text-[#0F172A] text-sm">0 copies</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 7. BORROWER LIST (TITLE, COLUMNS, ROWS, AND PAGINATION ONLY) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_3px_rgba(15,23,42,0.04)] p-6 lg:p-7 space-y-6">
            
            <!-- List Title Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#102B70]"></span>
                        <h2 class="text-base font-bold text-[#0F172A] tracking-tight">Borrower Log & Lending Records</h2>
                    </div>
                    <p class="text-sm text-[#64748B] mt-0.5">Comprehensive student borrowing records and circulation status</p>
                </div>
                <div class="text-sm font-semibold text-slate-500">
                    Showing {{ $borrowings->firstItem() ?? 0 }} - {{ $borrowings->lastItem() ?? 0 }} of {{ $borrowings->total() }} records
                </div>
            </div>

            <!-- Table -->
            <div class="admin-mobile-table-scroll overflow-x-auto">
                <table class="admin-mobile-table w-full text-left text-sm font-sans">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-6">Borrower / Student</th>
                            <th class="py-3 px-6">Book</th>
                            <th class="py-3 px-6 whitespace-nowrap">Borrowed Date</th>
                            <th class="py-3 px-6 whitespace-nowrap">Due Date</th>
                            <th class="py-3 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($borrowings as $item)
                            @php
                                $student = $item->student;
                                $fullName = $student ? "{$student->first_name} {$student->last_name}" : 'Student Member';
                                $idNumber = $student?->school_id_number ?? '—';
                                $programInfo = $student ? trim(($student->program ?? '') . ($student->year_level ? " - Year {$student->year_level}" : '')) : '';
                                $initials = $student ? strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) : 'ST';

                                $bBook = $item->book;
                                $bDetail = $bBook?->bookDetail;
                                $bData = $bDetail?->bookData;
                                $bTitle = $bData?->book_title ?? 'Untitled Book';
                                $bAccession = $bBook?->accession_number ?? ('ACC-' . str_pad($item->book_id, 4, '0', STR_PAD_LEFT));

                                $isReturned = !empty($item->return_date);
                                $isOverdue = !$isReturned && $item->due_date && $item->due_date->isPast();

                                $issuedByStaff = $item->issuedBy
                                    ? trim($item->issuedBy->first_name . ' ' . $item->issuedBy->last_name)
                                    : null;
                            @endphp

                            <tr class="hover:bg-slate-50/70 transition-colors h-[72px]">
                                <!-- 1. STUDENT / BORROWER INFO -->
                                <td data-label="Borrower" class="px-6 py-4 align-middle">
                                    <div class="flex items-center gap-3.5">
                                        @if($student?->image_url)
                                            <img src="{{ $student->image_url }}" alt="{{ $fullName }}" class="w-10 h-10 rounded-full object-cover border border-[#CBD5E1] shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $initials }}
                                            </div>
                                        @endif

                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-bold text-[#102B70] truncate">
                                                {{ $fullName }}
                                            </span>
                                            <div class="flex items-center gap-1.5 text-sm text-slate-500 font-medium truncate mt-0.5">
                                                <span class="font-mono text-xs">{{ $idNumber }}</span>
                                                @if($programInfo)
                                                    <span>•</span>
                                                    <span class="truncate">{{ $programInfo }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. BOOK DETAILS & ACCESSION -->
                                <td data-label="Book" class="px-6 py-4 align-middle">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-slate-900 leading-tight truncate max-w-[340px]" title="{{ $bTitle }}">
                                            {{ $bTitle }}
                                        </span>
                                        @if($item->isInsideLibrary())<span class="text-xs font-semibold text-blue-700">Inside library</span>@endif
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="font-mono text-xs font-semibold text-[#102B70] px-2 py-0.5 rounded-md bg-[#EFF6FF]">
                                                {{ $bAccession }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- 3. BORROWED DATE -->
                                <td data-label="Borrowed" class="px-6 py-4 align-middle whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-800">
                                            {{ $item->issued_date ? $item->issued_date->format('M d, Y') : '—' }}
                                        </span>
                                        @if($issuedByStaff)
                                            <span class="text-xs text-slate-400 font-medium mt-0.5">
                                                By {{ $issuedByStaff }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- 4. DUE DATE -->
                                <td data-label="Due" class="px-6 py-4 align-middle whitespace-nowrap">
                                    @if($isReturned)
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-500">
                                                {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                            </span>
                                            <span class="text-xs text-emerald-600 font-medium mt-0.5">
                                                Returned {{ $item->return_date ? $item->return_date->format('M d') : '' }}
                                            </span>
                                        </div>
                                    @elseif($isOverdue)
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-red-600">
                                                {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                            </span>
                                            <span class="text-xs text-red-500 font-semibold mt-0.5">
                                                {{ $item->due_date ? $item->due_date->diffForHumans() : 'Overdue' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-700">
                                                {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                            </span>
                                            <span class="text-xs text-slate-400 font-medium mt-0.5">
                                                Due {{ $item->due_date ? $item->due_date->diffForHumans(['parts' => 1]) : '' }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- 5. LOAN STATUS BADGE -->
                                <td data-label="Status" class="px-6 py-4 align-middle">
                                    @if($isReturned)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Returned
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            On Loan
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex max-w-md flex-col items-center">
                                        <h3 class="text-base font-bold text-[#0F172A]">No borrowing records found</h3>
                                        <p class="mt-1 text-xs sm:text-sm text-[#64748B] max-w-sm">
                                            There are no circulation records cataloged in the system yet.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if($borrowings->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="text-sm text-slate-500 font-medium">
                        Showing <span class="font-bold text-[#102B70]">{{ $borrowings->firstItem() }}</span> to <span class="font-bold text-[#102B70]">{{ $borrowings->lastItem() }}</span> of <span class="font-bold text-[#102B70]">{{ $borrowings->total() }}</span> results
                    </div>
                    <div>
                        {{ $borrowings->links('components.widget.pagination') }}
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
