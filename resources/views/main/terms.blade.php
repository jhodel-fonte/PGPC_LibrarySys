<x-layouts.home :title="'Terms and Conditions of Library Service'">
    <div class="min-h-screen bg-[#F8FAFC] py-8 sm:py-12 select-text">
        <div class="mx-auto max-w-[1280px] px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-[#64748B]">
                <a href="{{ url('/') }}" class="hover:text-[#102B70] transition-colors">Home</a>
                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#334155]">Legal & Policies</span>
                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#102B70] font-bold">Terms and Conditions</span>
            </nav>

            <!-- Hero Banner Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#102B70] via-[#0B225E] to-[#071943] text-white p-6 sm:p-10 shadow-xl border border-white/10">
                <!-- Decorative background elements -->
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-20 h-48 w-48 rounded-full bg-[#FCC719]/10 blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl space-y-3">
                        <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            Terms and Conditions of Library Service
                        </h1>
                        <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed font-normal">
                            Rules, borrowing privileges, patron liabilities, and ethical conduct governing the use of physical collections and catalog systems at Padre Garcia Polytechnic College Library.
                        </p>
                    </div>

                </div>

                <!-- Policy Metadata Footer -->
                <div class="mt-8 pt-6 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-blue-100">
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Institution</span>
                        <span class="font-semibold text-white mt-0.5 block">{{ config('settings.college.name', 'Padre Garcia Polytechnic College') }}</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Revision Version</span>
                        <span class="font-semibold text-white mt-0.5 block">Version 2.0 (Official)</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Effective Term</span>
                        <span class="font-semibold text-white mt-0.5 block">Academic Year {{ date('Y') }}–{{ date('Y') + 1 }}</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Circulation Desk</span>
                        <span class="font-semibold text-white mt-0.5 block">{{ config('settings.library_config.location') ?? config('settings.library_location') }}</span>
                    </div>
                </div>
            </div>

            <!-- Policy Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                <a href="{{ route('terms') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#102B70] text-white font-bold text-xs shadow-sm">
                    <svg class="h-4 w-4 text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Terms & Conditions</span>
                </a>
                <a href="{{ route('privacy') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-[#334155] font-semibold text-xs border border-[#E2E8F0] transition-colors">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Data Privacy Policy</span>
                </a>
            </div>

            <!-- Policy Key Parameters Metric Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-[#102B70]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Standard Loan Period</span>
                            <span class="text-lg font-bold text-[#102B70]">{{ config('settings.library_config.loan_period', 5) }} Days</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-[#102B70]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Max Simultaneous Loans</span>
                            <span class="text-lg font-bold text-[#102B70]">{{ config('settings.library_config.max_loan_limit', 3) }} Books</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700">
                            <span class="font-bold text-base">₱</span>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Daily Overdue Penalty</span>
                            <span class="text-lg font-bold text-amber-700">₱{{ number_format(config('settings.library_config.penalty_fee', 50), 2) }} / day</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-[#102B70]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Desk Hours ({{ config('settings.library_config.operating_days', 'Mon - Fri') }})</span>
                            <span class="text-sm font-bold text-[#102B70]">{{ config('settings.library_config.desk_hours', '8:00 AM – 5:00 PM') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Layout: Table of Contents & Main Legal Document -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Sticky Quick Nav -->
                <aside class="hidden lg:block lg:col-span-4 sticky top-24 space-y-5">
                    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#102B70] mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span>Table of Contents</span>
                        </h3>
                        <ul class="space-y-1 text-xs">
                            <li><a href="#article-1" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">1. Authority & Patron Eligibility</a></li>
                            <li><a href="#article-2" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">2. Library ID & Credentials</a></li>
                            <li><a href="#article-3" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">3. Circulation Quotas & Loan Rules</a></li>
                            <li><a href="#article-4" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">4. Renewals & Reservations</a></li>
                            <li><a href="#article-5" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">5. Overdue Fines & Penalties</a></li>
                            <li><a href="#article-6" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">6. Lost & Damaged Materials</a></li>
                            <li><a href="#article-7" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">7. Facility Code of Conduct</a></li>
                            <li><a href="#article-8" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">8. Digital OPAC & Fair Use</a></li>
                            <li><a href="#article-9" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">9. Clearance Holds & Sanctions</a></li>
                            <li><a href="#article-10" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">10. Contact & Official Desk Inquiries</a></li>
                        </ul>
                    </div>

                    <!-- Assistance Box -->
                    <div class="bg-gradient-to-br from-[#102B70] to-[#0B225E] rounded-2xl p-5 text-white shadow-sm border border-white/10 space-y-3">
                        <h4 class="text-sm font-bold text-[#FCC719]">Have Policy Questions?</h4>
                        <p class="text-xs text-blue-100 leading-relaxed">
                            Our Head Librarian and Circulation Staff are available to assist with loan inquiries, lost book evaluations, and account clearances.
                        </p>
                        <div class="pt-2 border-t border-white/10 text-[11px] text-blue-200 space-y-1">
                            <p><strong>Location:</strong> {{ config('settings.library_config.location') ?? config('settings.library_location') }}</p>
                            <p><strong>Hours:</strong> {{ config('settings.library_config.operating_days', 'Mon - Fri') }} &bull; {{ config('settings.library_config.desk_hours', '8:00 AM – 5:00 PM') }}</p>
                        </div>
                    </div>
                </aside>

                <!-- Right Column: Articles -->
                <main class="lg:col-span-8 bg-white rounded-3xl border border-[#E2E8F0] p-6 sm:p-10 shadow-sm space-y-10 leading-relaxed text-[#334155]">

                    <!-- Article 1 -->
                    <section id="article-1" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">1</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Institutional Authority & Eligible Patrons</h2>
                        </div>
                        <p class="text-sm">
                            The Padre Garcia Polytechnic College (PGPC) Library operates as the central academic information repository of the institution. All registered students, faculty members, academic heads, and administrative personnel are recognized patrons entitled to library privileges in accordance with their enrolled status.
                        </p>
                        <p class="text-sm">
                            Registration in the PGPC Library System occurs upon official enrollment or employment verification. By entering library facilities, activating an online account, or using the Online Public Access Catalog (OPAC), all patrons agree to be bound by these Terms and Conditions.
                        </p>
                    </section>

                    <!-- Article 2 -->
                    <section id="article-2" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">2</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Library Identification & Account Credentials</h2>
                        </div>
                        <p class="text-sm">
                            Every patron must present their official PGPC Student ID or Employee ID containing a readable barcode or RFID chip when borrowing, renewing, or reserving books at the Circulation Desk.
                        </p>
                        <ul class="list-disc pl-5 space-y-2 text-sm text-slate-600">
                            <li><strong>Non-Transferability:</strong> Library cards and account credentials are strictly personal. Borrowing books on behalf of another individual or using someone else's credentials is prohibited.</li>
                            <li><strong>Account Security:</strong> Patrons must maintain confidentiality of their digital student portal and OPAC login passwords. The library assumes transactions made under an authenticated account are authorized by the account holder.</li>
                        </ul>
                    </section>

                    <!-- Article 3 -->
                    <section id="article-3" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">3</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Circulation Quotas & Loan Rules</h2>
                        </div>
                        <p class="text-sm">
                            Library collections are categorized by circulation classification. General circulation materials may be loaned out for home use under the following institutional quotas:
                        </p>

                        <div class="rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] p-4 my-3 space-y-2">
                            <div class="flex items-center justify-between text-xs py-1 border-b border-[#E2E8F0]">
                                <span class="font-bold text-[#102B70]">Maximum Active Loans</span>
                                <span class="font-bold text-[#0F172A]">{{ config('settings.library_config.max_loan_limit', 3) }} books simultaneously</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-[#E2E8F0]">
                                <span class="font-bold text-[#102B70]">General Circulation Loan Duration</span>
                                <span class="font-bold text-[#0F172A]">{{ config('settings.library_config.loan_period', 5) }} calendar days</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1">
                                <span class="font-bold text-[#102B70]">Reference, Periodicals & Theses</span>
                                <span class="font-bold text-[#B91C1C]">Room-use only (Non-circulating)</span>
                            </div>
                        </div>

                        <p class="text-sm">
                            All loaned items must be physically presented at the Circulation Desk on or before their due date for return confirmation.
                        </p>
                    </section>

                    <!-- Article 4 -->
                    <section id="article-4" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">4</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Book Renewals & Reservations</h2>
                        </div>
                        <p class="text-sm">
                            Borrowed books may be renewed once for an additional {{ config('settings.library_config.loan_period', 5) }}-day cycle, provided that:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-600">
                            <li>The book is not currently overdue.</li>
                            <li>No other patron has placed an active reservation on the book.</li>
                            <li>The borrower does not have unpaid penalties or administrative holds.</li>
                        </ul>
                        <p class="text-sm mt-2">
                            Reserved books will be held at the Circulation Desk for <strong>3 school days</strong> after notification. If unclaimed within this period, the reservation automatically lapses and the item is made available to other students.
                        </p>
                    </section>

                    <!-- Article 5 -->
                    <section id="article-5" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">5</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Overdue Fines, Penalties & Suspensions</h2>
                        </div>
                        <p class="text-sm">
                            Prompt return of library items ensures fair access for all members of the academic community. Late returns are subject to the standard college penalty schedule:
                        </p>

                        <div class="rounded-2xl bg-amber-50/70 border border-amber-200/80 p-4 my-2 text-xs text-amber-900 space-y-2">
                            <p class="font-bold text-sm flex items-center gap-2 text-amber-900">
                                <span>Late Fine Assessment:</span>
                                <span class="text-base text-red-700">₱{{ number_format(config('settings.library_config.penalty_fee', 50), 2) }}</span>
                                <span class="font-normal text-amber-800">per calendar day per overdue title.</span>
                            </p>
                            <p class="text-amber-800 leading-relaxed">
                                Fines accumulate continuously starting from the immediate day following the due date until the physical copy is officially scanned in by the librarian.
                            </p>
                        </div>

                        <p class="text-sm">
                            Patrons with accumulated fines exceeding ₱150.00 will experience automatic suspension of borrowing and reservation capabilities until outstanding dues are settled with the cashier or circulation staff.
                        </p>
                    </section>

                    <!-- Article 6 -->
                    <section id="article-6" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">6</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Lost & Damaged Materials Settlement</h2>
                        </div>
                        <p class="text-sm">
                            Borrowers are strictly liable for the condition of borrowed books. In the event an item is lost or returned with severe damage (water stains, torn pages, highlighted passages, or broken spines), the borrower must:
                        </p>
                        <ol class="list-decimal pl-5 space-y-1.5 text-sm text-slate-600">
                            <li>Replace the book with an exact identical copy (same ISBN, title, and author in new condition), <strong>OR</strong></li>
                            <li>Reimburse the library for the current replacement cost of the book plus a standard cataloging/processing fee.</li>
                        </ol>
                    </section>

                    <!-- Article 7 -->
                    <section id="article-7" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">7</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Facility Code of Conduct</h2>
                        </div>
                        <p class="text-sm">
                            The PGPC Library is dedicated to scholarly study and research. Patrons must abide by the following facility standards:
                        </p>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-slate-600">
                            <li>Maintain a quiet study atmosphere. Mobile phones must be switched to silent mode.</li>
                            <li>Food and colored beverages are strictly prohibited near book stacks and computer stations.</li>
                            <li>Treat staff and fellow students with courtesy and academic respect.</li>
                            <li>Refrain from marking, tearing, or folding pages of library books.</li>
                        </ul>
                    </section>

                    <!-- Article 8 -->
                    <section id="article-8" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">8</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Digital OPAC & Fair Use Compliance</h2>
                        </div>
                        <p class="text-sm">
                            All materials in the library catalog and digital subscriptions are protected under Philippine and international copyright laws (Intellectual Property Code of the Philippines, RA 8293). Photocopying or digital reproduction is permissible only within fair use provisions for private educational research.
                        </p>
                        <p class="text-sm">
                            Automated data harvesting, scraping, or probing the online portal infrastructure is strictly prohibited and subject to institutional administrative sanction.
                        </p>
                    </section>

                    <!-- Article 9 -->
                    <section id="article-9" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">9</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Clearance Holds & Institutional Sanctions</h2>
                        </div>
                        <p class="text-sm">
                            Unreturned library books or unsettled penalty fees result in an immediate library hold. Such holds prevent:
                        </p>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-slate-600">
                            <li>Signing of end-of-semester collegiate clearance.</li>
                            <li>Issuance of official transcripts of records (OTR) and certificates of graduation.</li>
                            <li>Subsequent semester enrollment validation.</li>
                        </ul>
                    </section>

                    <!-- Article 10 -->
                    <section id="article-10" class="scroll-mt-28 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">10</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Contact & Official Desk Inquiries</h2>
                        </div>
                        <p class="text-sm">
                            For appeals concerning fines, reported lost items, or general borrowing inquiries, please visit or reach out to the Circulation Desk:
                        </p>
                        <div class="rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] p-4 text-xs space-y-1.5 text-slate-700">
                            <p><strong>Desk Location:</strong> {{ config('settings.library_config.location') ?? config('settings.library_location') }}</p>
                            <p><strong>Campus Address:</strong> {{ config('settings.college.address', 'Brgy. San Roque, Padre Garcia, Batangas, Philippines') }}</p>
                            <p><strong>Operating Schedule:</strong> {{ config('settings.library_config.operating_days', 'Mon - Fri') }} ({{ config('settings.library_config.desk_hours', '8:00 AM – 5:00 PM') }})</p>
                            <p><strong>Inquiries & Clearances:</strong> library@padregarcia.gov.ph</p>
                        </div>
                    </section>

                </main>
            </div>

        </div>
    </div>
</x-layouts.home>
