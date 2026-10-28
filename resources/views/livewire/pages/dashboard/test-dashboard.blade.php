<div class="min-h-full bg-[#F8FAFC] pb-12">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 space-y-6">

        <!-- 1. Operational Hero Greeting Banner with Integrated Quick Actions -->
        <div class="bg-[#17357A] rounded-[16px] p-5 sm:p-6 lg:p-7 relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-6 shadow-sm">
            <!-- Background Decoration -->
            <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(105deg, transparent 0%, transparent 60%, rgba(255,255,255,.025) 60%);"></div>

            <!-- Left: Greeting & Date -->
            <div class="relative z-10 flex-1 min-w-0">
                <p class="text-blue-200 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em] mb-2 sm:mb-2.5">
                    {{ strtoupper(now()->format('l, F j, Y')) }}
                </p>
                
                <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-bold text-white leading-tight mb-2">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, 
                    <span class="text-[#FFC61A]">{{ Auth::user()->name ?? 'Admin' }}</span>
                </h1>
                
                <p class="text-blue-100 text-[13px] sm:text-[14px] leading-relaxed max-w-[560px]">
                    Here's an overview of the library today. Process returning items, issue loans, manage holds, or add new catalog titles directly using the quick actions.
                </p>
            </div>

            <!-- Right: Quick Actions Grid -->
            <div class="relative z-10 grid grid-cols-2 gap-2.5 sm:gap-3 w-full lg:w-auto lg:min-w-[440px] shrink-0">
                
                <!-- Action 1: Check In / Return -->
                <a href="{{ url('/admin/circulation-desk/return') }}" wire:navigate 
                   class="group flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white transition-all backdrop-blur-xs">
                    <div class="w-9 h-9 rounded-lg bg-[#05BF8F] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-bold text-white group-hover:text-emerald-300 transition-colors leading-tight truncate">Check In / Return</div>
                        <div class="text-[11px] text-blue-200/80 truncate">Scan return book</div>
                    </div>
                </a>

                <!-- Action 2: Check Out / Borrow -->
                <a href="{{ url('/admin/circulation-desk/borrow') }}" wire:navigate 
                   class="group flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white transition-all backdrop-blur-xs">
                    <div class="w-9 h-9 rounded-lg bg-[#FFC61A] text-[#102B70] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-bold text-white group-hover:text-[#FFC61A] transition-colors leading-tight truncate">Check Out / Borrow</div>
                        <div class="text-[11px] text-blue-200/80 truncate">Issue to patron</div>
                    </div>
                </a>

                <!-- Action 3: Hold Requests -->
                <a href="{{ url('/admin/circulation-desk') }}" wire:navigate 
                   class="group flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white transition-all backdrop-blur-xs">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/80 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-bold text-white group-hover:text-blue-300 transition-colors leading-tight truncate">Hold Requests</div>
                        <div class="text-[11px] text-blue-200/80 truncate">7 pending holds</div>
                    </div>
                </a>

                <!-- Action 4: Add New Book -->
                <a href="{{ url('/admin/book-management/add') }}" wire:navigate 
                   class="group flex items-center gap-3 p-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white transition-all backdrop-blur-xs">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/80 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[13.5px] font-bold text-white group-hover:text-indigo-200 transition-colors leading-tight truncate">Add New Book</div>
                        <div class="text-[11px] text-blue-200/80 truncate">Accession title</div>
                    </div>
                </a>

            </div>
        </div>

        <!-- 2. Statistics & Requires Attention Row (50/50 Split) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
            
            <!-- Left 50%: Four Main Summary Cards (2x2 Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 lg:gap-4">
                
                <!-- Card 1: Total Books -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">
                            {{ $summary['totalBooks']['label'] }}
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 flex items-baseline justify-between gap-2">
                        <span class="text-2xl font-bold text-[#102B70] tracking-tight tabular-nums">
                            {{ $summary['totalBooks']['count'] }}
                        </span>
                        <span class="inline-flex items-center text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md shrink-0">
                            {{ $summary['totalBooks']['badge'] }}
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-500 mt-1 truncate">
                        {{ $summary['totalBooks']['subtext'] }}
                    </p>
                </div>

                <!-- Card 2: Active Loans -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">
                            {{ $summary['activeLoans']['label'] }}
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 flex items-baseline justify-between gap-2">
                        <span class="text-2xl font-bold text-[#102B70] tracking-tight tabular-nums">
                            {{ $summary['activeLoans']['count'] }}
                        </span>
                        <span class="inline-flex items-center text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md shrink-0">
                            {{ $summary['activeLoans']['badge'] }}
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-500 mt-1 truncate">
                        {{ $summary['activeLoans']['subtext'] }}
                    </p>
                </div>

                <!-- Card 3: Overdue Items -->
                <div class="bg-white rounded-2xl border border-red-200/90 p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-600 truncate">
                            {{ $summary['overdueItems']['label'] }}
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 flex items-baseline justify-between gap-2">
                        <span class="text-2xl font-bold text-red-700 tracking-tight tabular-nums">
                            {{ $summary['overdueItems']['count'] }}
                        </span>
                        <span class="inline-flex items-center text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded-md border border-red-200 shrink-0">
                            {{ $summary['overdueItems']['badge'] }}
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-500 mt-1 truncate">
                        {{ $summary['overdueItems']['subtext'] }}
                    </p>
                </div>

                <!-- Card 4: Pending Reservations -->
                <div class="bg-white rounded-2xl border border-amber-200/90 p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex flex-col justify-between min-w-0">
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-700 truncate">
                            {{ $summary['pendingReservations']['label'] }}
                        </span>
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5 flex items-baseline justify-between gap-2">
                        <span class="text-2xl font-bold text-amber-800 tracking-tight tabular-nums">
                            {{ $summary['pendingReservations']['count'] }}
                        </span>
                        <span class="inline-flex items-center text-[11px] font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 shrink-0">
                            {{ $summary['pendingReservations']['badge'] }}
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-500 mt-1 truncate">
                        {{ $summary['pendingReservations']['subtext'] }}
                    </p>
                </div>

            </div>

            <!-- Right 50%: Requires Attention Card (Matching Height) -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col justify-between h-full">
                <div class="mb-3">
                    <h2 class="text-base font-bold text-slate-900">
                        Requires Attention
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Items that may need librarian action
                    </p>
                </div>

                <div class="space-y-2.5 flex-1 flex flex-col justify-between">
                    @foreach($needsAttention as $item)
                        @php
                            $isRed = $item['color'] === 'red';
                            $isAmber = $item['color'] === 'amber';
                            $isBlue = $item['color'] === 'blue';

                            $boxBg = $isRed ? 'border-red-100 bg-red-50/50 hover:bg-red-50' : ($isAmber ? 'border-amber-100 bg-amber-50/50 hover:bg-amber-50' : 'border-blue-100 bg-blue-50/50 hover:bg-blue-50');
                            $iconBg = $isRed ? 'bg-red-100 text-red-600' : ($isAmber ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600');
                            $titleColor = $isRed ? 'text-red-700 group-hover:text-red-800' : ($isAmber ? 'text-amber-700 group-hover:text-amber-800' : 'text-blue-700 group-hover:text-blue-800');
                            $countColor = $isRed ? 'text-red-700' : ($isAmber ? 'text-amber-700' : 'text-blue-700');
                        @endphp

                        <a href="{{ $item['link'] }}" class="group block">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border {{ $boxBg }} px-4 py-3 transition-colors">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $iconBg }}">
                                        @if($item['type'] === 'overdue')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        @elseif($item['type'] === 'reservation')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-sm font-semibold {{ $titleColor }} transition-colors">
                                            {{ $item['title'] }}
                                        </span>
                                        <div class="text-[14px] font-normal text-slate-600 mt-0.5 leading-snug">
                                            {{ $item['detail'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                                    <span class="text-lg font-bold tabular-nums {{ $countColor }}">
                                        {{ $item['count'] }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-400 group-hover:text-slate-700 transition-colors hidden sm:inline">
                                        &rarr;
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

        </div>

        <!-- 5. Middle Grid: 7-Day Velocity Chart + Top 5 Borrowed Titles -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            
            <!-- 7-Day Velocity Chart (Compact line chart) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-5 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[#102B70] shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l6-4 4 4 8-6" />
                                    </svg>
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">Borrowing & Returning Trends</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Daily circulation volume comparison for the last 7 days</p>
                        </div>

                        <!-- Date Range Selector -->
                        <div class="relative shrink-0">
                            <select wire:model.live="trendsFilter" 
                                    class="text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 pr-7 appearance-none cursor-pointer focus:outline-none focus:border-[#102B70]">
                                <option value="7_days">Last 7 Days</option>
                                <option value="14_days">Last 14 Days</option>
                                <option value="this_month">This Month</option>
                            </select>
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                    </div>

                    <!-- SVG / Apex line chart via widget -->
                    <div class="pt-2">
                        <x-widget.graph.main 
                            :series="$trendsSeries" 
                            :labels="$trendsLabels" 
                            line 
                            grid 
                            legend 
                            markers 
                            height="190" 
                        />
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#102B70]"></span>
                            <span class="font-medium text-slate-700">Borrowed (190 total)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#3B82F6]"></span>
                            <span class="font-medium text-slate-700">Returned (162 total)</span>
                        </div>
                    </div>
                    <a href="{{ url('/admin/analytics/circulation') }}" class="font-bold text-[#102B70] hover:underline">
                        Analytics &rarr;
                    </a>
                </div>
            </div>

            <!-- Top 5 Most Borrowed Titles -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-5 flex flex-col justify-between min-w-0">
                <div>
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[#FCC719] shrink-0">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 leading-tight">Most Borrowed Titles</h3>
                                <p class="text-[11px] text-slate-500">Top 5 books circulating this month</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Oct 2026</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($popularBooks as $book)
                            <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Rank Badge -->
                                    <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center shrink-0 {{ $book['rank'] === 1 ? 'bg-[#FCC719] text-[#102B70]' : ($book['rank'] === 2 ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-500') }}">
                                        {{ $book['rank'] }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-[#0F172A] group-hover:text-[#102B70] transition-colors truncate">
                                            {{ $book['title'] }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">
                                            {{ $book['category'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <div class="text-xs font-extrabold text-[#102B70]">
                                        {{ $book['borrows'] }} <span class="text-[10px] font-medium text-slate-400">loans</span>
                                    </div>
                                    <div class="text-[10px] font-medium {{ $book['available'] > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $book['available'] }}/{{ $book['total'] }} avail
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Calculated over 30-day window</span>
                    <a href="{{ url('/admin/analytics') }}" class="font-bold text-[#102B70] hover:underline flex items-center gap-1">
                        View Full Report &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- 6. Recent Circulation Activity Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04)] overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-[#0F172A]">Recent Circulation Activity</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Latest borrower transactions recorded at the front circulation desk</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/admin/circulation-desk/borrowed-books') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-[#102B70] bg-[#EFF6FF] hover:bg-[#DBEAFE] rounded-xl transition-colors">
                        <span>View All Transactions</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-4 lg:px-6">Patron / Student</th>
                            <th class="py-3.5 px-4">Book Title & Accession</th>
                            <th class="py-3.5 px-4 text-center">Transaction</th>
                            <th class="py-3.5 px-4">Timestamp</th>
                            <th class="py-3.5 px-4">Due Date</th>
                            <th class="py-3.5 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($recentActivity as $act)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Patron / Student -->
                                <td class="py-3.5 px-4 lg:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#102B70]/10 text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ $act['initials'] }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-[#0F172A]">{{ $act['student'] }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $act['student_id'] }} • {{ $act['program'] }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Book Title & Accession -->
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900 max-w-xs truncate">{{ $act['book'] }}</div>
                                    <span class="inline-block mt-0.5 text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $act['accession'] }}
                                    </span>
                                </td>

                                <!-- Transaction Type (Borrow vs Return) -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($act['type'] === 'borrow')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" /></svg>
                                            Loan / Borrow
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                                            Return / Check-In
                                        </span>
                                    @endif
                                </td>

                                <!-- Timestamp -->
                                <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                    {{ $act['date'] }}
                                </td>

                                <!-- Due Date -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-mono text-[11px] {{ $act['status_color'] === 'red' ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                    {{ $act['due_date'] }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($act['status_color'] === 'blue')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $act['status'] }}
                                        </span>
                                    @elseif($act['status_color'] === 'emerald')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $act['status'] }}
                                        </span>
                                    @elseif($act['status_color'] === 'red')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-red-50 text-red-700 border border-red-200 animate-pulse">
                                            {{ $act['status'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $act['status'] }}
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 px-4 lg:px-6">
                <span>Showing 6 most recent circulation events</span>
                <a href="{{ url('/admin/circulation-desk/borrowed-books') }}" class="font-bold text-[#102B70] hover:underline">
                    View Complete Circulation Log &rarr;
                </a>
            </div>
        </div>

    </div>
</div>
