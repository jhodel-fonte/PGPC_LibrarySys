<div class="min-h-full bg-[#eef3f7]">
    {{-- <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-end justify-center opacity-[0.018] z-0 pb-10">
        <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
    </div> --}}

    <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-end justify-center opacity-[0.018] z-0 pb-10">
    </div>

    <div class="admin-dashboard-layout mx-auto w-full max-w-[1600px] max-h-auto p-4 lg:p-6 space-y-5">
        <div>
            <x-widget.greeting-banner staffName="{{ Auth::user()->librarian ? trim(Auth::user()->librarian->first_name . ' ' . Auth::user()->librarian->last_name) : (Auth::user()->name ?? (Auth::user()->username ?? 'User')) }}" />
        </div>

        {{-- KPI Summary Cards --}}
        <div class="dashboard-kpis grid gap-4">
            {{-- KPI 1: Total Number of Students --}}
            <x-widget.data-card 
                title="Total Students" 
                :value="$kpiMetrics['totalStudents']['value']" 
                :change="$kpiMetrics['totalStudents']['change']" 
                :subtext="$kpiMetrics['totalStudents']['subtext']" 
            />

            {{-- KPI 2: Total Number of Books --}}
            <x-widget.data-card 
                title="Total Books" 
                :value="$kpiMetrics['totalBooks']['value']" 
                :change="$kpiMetrics['totalBooks']['change']" 
                :subtext="$kpiMetrics['totalBooks']['subtext']" 
            />

            {{-- KPI 3: Due Books Today --}}
            <x-widget.data-card 
                title="Due Books Today" 
                :value="$kpiMetrics['dueBooksToday']['value']" 
                :change="$kpiMetrics['dueBooksToday']['change']" 
                :subtext="$kpiMetrics['dueBooksToday']['subtext']" 
            />

            {{-- KPI 4: Book Reserved Today --}}
            <x-widget.data-card 
                title="Book Reserved Today" 
                :value="$kpiMetrics['bookReservedToday']['value']" 
                :change="$kpiMetrics['bookReservedToday']['change']" 
                :subtext="$kpiMetrics['bookReservedToday']['subtext']" 
            />
        </div>

        <!-- Middle Row: 3 Diagram Cards (Multiple Series, Donut, Grouped Bars) -->
        <div class="dashboard-charts grid gap-5 items-stretch">
            
            <!-- 1. Multiple Series Line Chart: Borrowing and Returning Trends -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-4 flex flex-col justify-between min-h-[210px] min-w-0">
                <!-- Header -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900 leading-tight">Borrowing and Returning Trends</h2>
                        </div>
                    </div>

                    <div class="relative">
                        <select wire:model.live="trendsFilter" class="text-[13px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-0.5 pr-6 appearance-none cursor-pointer focus:outline-none focus:border-[#0284c7]">
                            <option value="7_days">Last 7 Days</option>
                            <option value="30_days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                        </select>
                    </div>  
                </div>

                <!-- TallStackUI Multiple Series Chart -->
                <x-widget.graph.main 
                    :series="$borrowingTrendsSeries" 
                    :labels="$borrowingTrendsLabels" 
                    line 
                    grid 
                    legend 
                    markers 
                    height="165" 
                    svgWidth="480" 
                />
            </div>

            <!-- 2. Donut Chart: Most Borrowed Book Categories -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-4 flex flex-col min-h-[210px] min-w-0">
                <!-- Header -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <div>
                            <h2 class="text-[16px] font-bold text-slate-900 leading-tight">Most Borrowed Book Categories</h2>
                        </div>
                    </div>

                    <div class="relative">
                        <select wire:model.live="categoryFilter" class="text-[12px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-0.5 pr-6 appearance-none cursor-pointer focus:outline-none focus:border-[#0284c7]">
                            <option value="all_time">All Time</option>
                            <option value="this_month">This Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                </div>

                <!-- TallStackUI Donut Chart -->
                <x-widget.graph.main 
                    :series="$categoryDonutSeries" 
                    :labels="$categoryDonutLabels" 
                    donut 
                    legend 
                    height="165" 
                    :centerLabel="['value' => $categoryDonutTotal, 'label' => 'Total Borrowed']" 
                />
            </div>

            <!-- 3. Requires Attention (Replaces Monthly Library Activity) -->
            <div class="dashboard-attention h-full min-w-0">
                <x-dashboard.requires-attention 
                    :overdueCount="$overdueItems" 
                    :pendingCount="$pendingReservations" 
                />
            </div>

        </div>

        <!-- Bottom Row: Current Borrowers and Most Borrowed -->
        <div class="dashboard-bottom grid gap-5 items-start">
            <!-- Current Borrowers -->
            <div class="min-w-0">
                <div class="dashboard-borrowers">
                    <x-dashboard.current-borrowers :borrowers="$currentBorrowers" />
                </div>
            </div>

            <!-- Most Borrowed Titles -->
            <div class="min-w-0">
                <x-dashboard.most-borrowed-titles :titles="$mostBorrowedTitles" />
            </div>
        </div>
    </div>
</div>
