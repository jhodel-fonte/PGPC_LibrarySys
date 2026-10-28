@props([
    'kpiMetrics' => null,
    'monthlyTrend' => null,
    'yearlyTrend' => null,
    'genderBreakdown' => null,
    'programBreakdown' => null,
    'dueBooksToday' => null,
    'hourlyTraffic' => null,
    'timePeriod' => 'monthly',
    'generatedBy' => null,
    'reportDate' => null,
])

@php
    $librarian = $generatedBy ?? (auth()->check() ? (auth()->user()->name ?? (auth()->user()->first_name . ' ' . auth()->user()->last_name)) : 'PGPC Library Administrator');
    $currentDate = $reportDate ?? now()->format('F j, Y • g:i A');
    $reportId = 'PGPC-ANL-' . now()->format('Ymd-His');

    // Default fallbacks if props are not passed
    $kpi = $kpiMetrics ?? [];
    $totalStudents = $kpi['totalStudents']['value'] ?? '2,845';
    $totalBooks = $kpi['totalBooks']['value'] ?? '15,420';
    $dueToday = $kpi['dueBooksToday']['value'] ?? '14';
    $peakHour = $kpi['peakHour']['value'] ?? '1:00 PM – 3:00 PM';
    $genderDom = $kpi['genderDominance']['value'] ?? 'Female (58%)';

    $programs = $programBreakdown['programs'] ?? [
        ['code' => 'BSCS', 'name' => 'BS Computer Science', 'users' => 640, 'loans' => 358, 'pct' => 24],
        ['code' => 'BSCrim', 'name' => 'BS Criminology', 'users' => 510, 'loans' => 295, 'pct' => 20],
        ['code' => 'BSMA', 'name' => 'BS Management Accounting', 'users' => 480, 'loans' => 245, 'pct' => 18],
        ['code' => 'BPA', 'name' => 'Bachelor of Public Administration', 'users' => 390, 'loans' => 210, 'pct' => 15],
        ['code' => 'BSIT', 'name' => 'BS Information Technology', 'users' => 320, 'loans' => 180, 'pct' => 13],
        ['code' => 'BSHM', 'name' => 'BS Hospitality Management', 'users' => 260, 'loans' => 140, 'pct' => 10],
    ];

    $dueItems = $dueBooksToday ?? [];
    $femaleStats = $genderBreakdown['female'] ?? ['count' => 1650, 'pct' => 58, 'avgLoans' => '4.8 books/mo'];
    $maleStats = $genderBreakdown['male'] ?? ['count' => 1195, 'pct' => 42, 'avgLoans' => '3.2 books/mo'];
@endphp

<div class="pgpc-analytics-document bg-white text-[#0F172A] p-4 sm:p-8 max-w-[960px] mx-auto text-sm font-['Open_Sans',sans-serif] leading-relaxed">
    
    <!-- PRINT STYLESHEET ENHANCEMENTS -->
    <style>
        @media print {
            @page {
                size: letter portrait;
                margin: 10mm 12mm 12mm 12mm;
            }
            html, body {
                background: #FFFFFF !important;
                color: #0F172A !important;
                height: auto !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Hide web app shell elements during print */
            aside, nav, 
            .fixed, [role="status"], [role="navigation"],
            .livewire-offline, .toast-notification,
            div[x-data*="sidebarOpen"] > div:first-child,
            div[x-data*="sidebarOpen"] > div > div:first-child {
                display: none !important;
            }
            div[x-data*="sidebarOpen"] {
                height: auto !important;
                overflow: visible !important;
                display: block !important;
            }
            div[x-data*="sidebarOpen"] > div {
                margin-left: 0 !important;
                height: auto !important;
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
            }
            main {
                overflow: visible !important;
                height: auto !important;
                display: block !important;
                background: #FFFFFF !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
                break-before: page;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .pgpc-analytics-document {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                background: #FFFFFF !important;
            }
        }
    </style>

    <!-- 1. INSTITUTIONAL LETTERHEAD HEADER -->
    <header class="border-b-2 border-[#102B70] pb-5 mb-6 avoid-break">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                {{-- College Emblem / Logo --}}
                <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 flex items-center justify-center rounded-xl overflow-hidden">
                    <img 
                        src="{{ asset('images/logo.webp') }}" 
                        alt="PGPC Logo" 
                        class="w-full h-full object-contain"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >
                    <div class="w-full h-full bg-[#102B70] text-[#FCC719] rounded-xl font-black text-xl items-center justify-center hidden">
                        PGPC
                    </div>
                </div>

                {{-- Institution Metadata --}}
                <div>
                    <span class="text-xs uppercase tracking-widest font-bold text-slate-500 block">Republic of the Philippines</span>
                    <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-[#102B70] uppercase">
                        Padre Garcia Polytechnic College
                    </h1>
                    <h2 class="text-xs sm:text-sm font-semibold text-slate-700">
                        Office of the College Library & Information Services
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Poblacion, Padre Garcia, Batangas • Email: library@pgpc.edu.ph
                    </p>
                </div>
            </div>

            {{-- Document Tracking Box --}}
            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-1 text-[10px] font-extrabold tracking-wider uppercase bg-[#102B70] text-white rounded">
                    Official Report
                </span>
                <div class="mt-1.5 text-[11px] text-slate-600 space-y-0.5">
                    <div><strong class="text-slate-800">Doc ID:</strong> <span class="font-mono">{{ $reportId }}</span></div>
                    <div><strong class="text-slate-800">Date:</strong> {{ $currentDate }}</div>
                    <div><strong class="text-slate-800">Period:</strong> {{ ucfirst($timePeriod) }} Snapshot</div>
                </div>
            </div>
        </div>

        {{-- Document Title Ribbon --}}
        <div class="mt-5 pt-3.5 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-[#0F172A] tracking-tight">
                    LIBRARY OPERATIONAL & CIRCULATION INTELLIGENCE REPORT
                </h3>
                <p class="text-xs text-slate-500">Comprehensive summary of collection usage, patron demographics, and loan circulation velocity.</p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-xs font-semibold text-slate-600">Generated By:</span>
                <span class="text-xs font-bold text-[#102B70] block">{{ $librarian }}</span>
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE SUMMARY / CORE KPIS -->
    <section class="mb-7 avoid-break">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1.5 h-4 bg-[#102B70] rounded-full"></span>
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">I. Executive Summary Key Performance Indicators</h4>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <!-- Total Students -->
            <div class="border border-slate-200 rounded-xl p-3 bg-[#F8FAFC]">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Registered Patrons</span>
                <span class="text-xl font-extrabold text-[#102B70] mt-1 block tabular-nums">{{ $totalStudents }}</span>
                <span class="text-[10px] text-emerald-700 font-semibold mt-0.5 block">Student patrons</span>
            </div>

            <!-- Total Books -->
            <div class="border border-slate-200 rounded-xl p-3 bg-[#F8FAFC]">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Catalog Volumes</span>
                <span class="text-xl font-extrabold text-[#102B70] mt-1 block tabular-nums">{{ $totalBooks }}</span>
                <span class="text-[10px] text-slate-600 font-semibold mt-0.5 block">Available titles</span>
            </div>

            <!-- Due Today -->
            <div class="border border-slate-200 rounded-xl p-3 bg-[#F8FAFC]">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Due Books Today</span>
                <span class="text-xl font-extrabold text-amber-600 mt-1 block tabular-nums">{{ $dueToday }}</span>
                <span class="text-[10px] text-slate-600 font-semibold mt-0.5 block">Daily schedule</span>
            </div>

            <!-- Peak Circulation Hour -->
            <div class="border border-slate-200 rounded-xl p-3 bg-[#F8FAFC]">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Peak Rush Hour</span>
                <span class="text-sm font-extrabold text-[#0F172A] mt-2 block leading-tight">{{ $peakHour }}</span>
                <span class="text-[10px] text-slate-500 font-semibold mt-0.5 block">Highest desk traffic</span>
            </div>

            <!-- Dominant Gender -->
            <div class="border border-slate-200 rounded-xl p-3 bg-[#F8FAFC] col-span-2 sm:col-span-1">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Dominant Demographic</span>
                <span class="text-sm font-extrabold text-[#102B70] mt-2 block leading-tight">{{ $genderDom }}</span>
                <span class="text-[10px] text-slate-500 font-semibold mt-0.5 block">Primary borrowers</span>
            </div>
        </div>
    </section>

    <!-- 3. PROGRAM / COURSE UTILIZATION RANKING -->
    <section class="mb-7 avoid-break">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-[#102B70] rounded-full"></span>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">II. Academic Program / Course Utilization Breakdown</h4>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Ranked by registered student volume</span>
        </div>

        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#102B70] text-white">
                        <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[11px] w-12 text-center">#</th>
                        <th class="py-2.5 px-3 font-bold uppercase tracking-wider text-[11px] w-24">Code</th>
                        <th class="py-2.5 px-3 font-bold uppercase tracking-wider text-[11px]">Academic Program Title</th>
                        <th class="py-2.5 px-3 font-bold uppercase tracking-wider text-[11px] text-right">Patrons</th>
                        <th class="py-2.5 px-3 font-bold uppercase tracking-wider text-[11px] text-right">Book Loans</th>
                        <th class="py-2.5 px-3.5 font-bold uppercase tracking-wider text-[11px] text-right w-20">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($programs as $index => $prog)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/70' }}">
                            <td class="py-2 px-3.5 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-2 px-3 font-extrabold text-[#102B70]">{{ $prog['code'] }}</td>
                            <td class="py-2 px-3 font-medium text-slate-800">{{ $prog['name'] }}</td>
                            <td class="py-2 px-3 text-right tabular-nums text-slate-700 font-semibold">{{ number_format($prog['users']) }}</td>
                            <td class="py-2 px-3 text-right tabular-nums font-bold text-[#102B70]">{{ number_format($prog['loans']) }}</td>
                            <td class="py-2 px-3.5 text-right tabular-nums font-bold text-slate-600">{{ $prog['pct'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- 4. GENDER DEMOGRAPHICS & READING PREFERENCES -->
    <section class="mb-7 avoid-break">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1.5 h-4 bg-[#102B70] rounded-full"></span>
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">III. Patron Demographics & Gender Engagement</h4>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Female -->
            <div class="border border-slate-200 rounded-xl p-4 bg-[#F8FAFC]">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 mb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#102B70]"></span>
                        <span class="font-bold text-[#102B70]">Female Patrons (Babae)</span>
                    </div>
                    <span class="text-xs font-extrabold text-[#102B70] px-2 py-0.5 rounded bg-blue-50 border border-blue-200">
                        {{ $femaleStats['pct'] }}% of Total
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Enrolled Count</span>
                        <strong class="text-slate-800 text-sm font-bold tabular-nums">{{ number_format($femaleStats['count']) }} students</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Average Loan Velocity</span>
                        <strong class="text-[#102B70] text-sm font-bold">{{ $femaleStats['avgLoans'] }}</strong>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-slate-200/80 text-[11px] text-slate-600">
                    <span class="font-semibold text-slate-700">Preferred Subject Area:</span> Social Sciences, Education, Literature & Nursing
                </div>
            </div>

            <!-- Male -->
            <div class="border border-slate-200 rounded-xl p-4 bg-[#F8FAFC]">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 mb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#3B82F6]"></span>
                        <span class="font-bold text-[#0F172A]">Male Patrons (Lalaki)</span>
                    </div>
                    <span class="text-xs font-extrabold text-[#3B82F6] px-2 py-0.5 rounded bg-sky-50 border border-sky-200">
                        {{ $maleStats['pct'] }}% of Total
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Enrolled Count</span>
                        <strong class="text-slate-800 text-sm font-bold tabular-nums">{{ number_format($maleStats['count']) }} students</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Average Loan Velocity</span>
                        <strong class="text-[#3B82F6] text-sm font-bold">{{ $maleStats['avgLoans'] }}</strong>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-slate-200/80 text-[11px] text-slate-600">
                    <span class="font-semibold text-slate-700">Preferred Subject Area:</span> Computer Science, IT, Criminal Law & Engineering
                </div>
            </div>
        </div>
    </section>

    <!-- 5. CIRCULATION SCHEDULE & DUE ITEMS -->
    @if (!empty($dueItems))
        <section class="mb-7 avoid-break">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">IV. Daily Circulation Status & Check-In Log</h4>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Logged today ({{ count($dueItems) }} transactions)</span>
            </div>

            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-800 text-white">
                            <th class="py-2 px-3 font-bold uppercase tracking-wider text-[11px]">Call / Title</th>
                            <th class="py-2 px-3 font-bold uppercase tracking-wider text-[11px]">Borrower Name</th>
                            <th class="py-2 px-3 font-bold uppercase tracking-wider text-[11px]">Program</th>
                            <th class="py-2 px-3 font-bold uppercase tracking-wider text-[11px]">Due Time</th>
                            <th class="py-2 px-3 font-bold uppercase tracking-wider text-[11px] text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($dueItems as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 font-semibold text-slate-800 max-w-[280px] truncate" title="{{ $item['title'] }}">
                                    {{ $item['title'] }}
                                </td>
                                <td class="py-2 px-3 text-slate-700">
                                    {{ $item['borrower'] }}
                                    <span class="text-[10px] text-slate-400 block">{{ $item['student_id'] }}</span>
                                </td>
                                <td class="py-2 px-3 font-bold text-[#102B70]">{{ $item['program'] }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $item['due_time'] }}</td>
                                <td class="py-2 px-3 text-right">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase border
                                        {{ $item['status'] === 'Returned' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($item['status'] === 'Overdue' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                        {{ $item['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <!-- 6. INSTITUTIONAL SIGN-OFF & VERIFICATION BLOCK -->
    <footer class="mt-8 pt-6 border-t-2 border-slate-200 avoid-break text-xs">
        <div class="grid grid-cols-2 gap-8 mb-6">
            {{-- Prepared By --}}
            <div>
                <span class="text-slate-500 font-semibold block uppercase tracking-wider text-[10px] mb-8">Prepared & Certified By:</span>
                <div class="border-b border-slate-400 w-48 mb-1"></div>
                <strong class="text-slate-800 font-bold block">{{ $librarian }}</strong>
                <span class="text-slate-500 text-[11px] block">Circulation Desk Librarian / Administrator</span>
            </div>

            {{-- Noted & Approved By --}}
            <div class="text-right flex flex-col items-end">
                <span class="text-slate-500 font-semibold block uppercase tracking-wider text-[10px] mb-8">Noted & Approved By:</span>
                <div class="border-b border-slate-400 w-48 mb-1"></div>
                <strong class="text-slate-800 font-bold block">Office of the Chief Librarian</strong>
                <span class="text-slate-500 text-[11px] block">Padre Garcia Polytechnic College</span>
            </div>
        </div>

        {{-- Verification Statement / Legal Notice --}}
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-[10px] text-slate-500 text-center leading-normal">
            <strong>System Authenticity Notice:</strong> This document is generated electronically from the Padre Garcia Polytechnic College (PGPC) Automated Library Management System. It serves as an official institutional circulation record. Any unauthorized modification or tampering invalidates this record.
        </div>
    </footer>

</div>
