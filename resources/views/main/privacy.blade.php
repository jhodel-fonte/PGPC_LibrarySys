<x-layouts.home :title="'Data Privacy Policy'">
    <div class="min-h-screen bg-[#F8FAFC] py-8 sm:py-12 select-text">
        <div class="mx-auto max-w-[1280px] px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-[#64748B]">
                <a href="{{ url('/') }}" class="hover:text-[#102B70] transition-colors">Home</a>
                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#334155]">Legal & Policies</span>
                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[#102B70] font-bold">Data Privacy Policy</span>
            </nav>

            <!-- Hero Banner Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#102B70] via-[#0B225E] to-[#071943] text-white p-6 sm:p-10 shadow-xl border border-white/10">
                <!-- Decorative background elements -->
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-20 h-48 w-48 rounded-full bg-[#FCC719]/10 blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[#FCC719] text-xs font-bold uppercase tracking-wider border border-white/10">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>RA 10173 Compliance</span>
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            Institutional Data Privacy Policy
                        </h1>
                        <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed font-normal">
                            How patron profiles, student information, and borrowing transactions are safeguarded and processed under the Data Privacy Act of 2012 at Padre Garcia Polytechnic College.
                        </p>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap sm:flex-col items-start sm:items-end gap-3 shrink-0">
                        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/') }}'" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/15 transition-all shadow-sm cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back to Previous Page</span>
                        </button>
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FCC719] hover:bg-[#eab308] text-[#102B70] text-xs font-bold transition-all shadow-md cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Policy Document</span>
                        </button>
                    </div>
                </div>

                <!-- Policy Metadata Footer -->
                <div class="mt-8 pt-6 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-blue-100">
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Governing Law</span>
                        <span class="font-semibold text-white mt-0.5 block">Republic Act No. 10173</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Revision Version</span>
                        <span class="font-semibold text-white mt-0.5 block">Version 1.0 (Official)</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Scope</span>
                        <span class="font-semibold text-white mt-0.5 block">All Patrons & Borrowers</span>
                    </div>
                    <div>
                        <span class="text-blue-300 block text-[11px] uppercase font-bold tracking-wider">Effective Term</span>
                        <span class="font-semibold text-white mt-0.5 block">Academic Year {{ date('Y') }}–{{ date('Y') + 1 }}</span>
                    </div>
                </div>
            </div>

            <!-- Policy Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                <a href="{{ route('terms') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-[#334155] font-semibold text-xs border border-[#E2E8F0] transition-colors">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Terms & Conditions</span>
                </a>
                <a href="{{ route('privacy') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#102B70] text-white font-bold text-xs shadow-sm">
                    <svg class="h-4 w-4 text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Data Privacy Policy</span>
                </a>
            </div>

            <!-- Privacy Commitments Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-[#102B70]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Data Encryption</span>
                            <span class="text-sm font-bold text-[#102B70]">Encrypted At Rest & Transit</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center text-[#102B70]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Access Control</span>
                            <span class="text-sm font-bold text-[#102B70]">Role-Based Authorized Staff</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-[#64748B] font-medium block">Third-Party Policy</span>
                            <span class="text-sm font-bold text-emerald-800">Zero Selling or Commercial Sharing</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Sticky Quick Nav -->
                <aside class="hidden lg:block lg:col-span-4 sticky top-24 space-y-5">
                    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#102B70] mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span>Privacy Articles</span>
                        </h3>
                        <ul class="space-y-1 text-xs">
                            <li><a href="#privacy-1" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">1. Scope & Legal Framework</a></li>
                            <li><a href="#privacy-2" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">2. Categories of Information Collected</a></li>
                            <li><a href="#privacy-3" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">3. Purpose of Processing</a></li>
                            <li><a href="#privacy-4" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">4. Data Security & Storage Controls</a></li>
                            <li><a href="#privacy-5" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">5. Non-Disclosure & Confidentiality</a></li>
                            <li><a href="#privacy-6" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">6. Data Retention & Archival</a></li>
                            <li><a href="#privacy-7" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">7. Patron Rights Under RA 10173</a></li>
                            <li><a href="#privacy-8" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">8. Cookie & Session Policy</a></li>
                            <li><a href="#privacy-9" class="block py-1.5 px-2.5 rounded-lg text-[#334155] hover:bg-[#EFF6FF] hover:text-[#102B70] font-semibold transition-colors">9. Data Protection Officer Contact</a></li>
                        </ul>
                    </div>

                    <!-- DPO Card -->
                    <div class="bg-gradient-to-br from-[#102B70] to-[#0B225E] rounded-2xl p-5 text-white shadow-sm border border-white/10 space-y-3">
                        <h4 class="text-sm font-bold text-[#FCC719]">Data Protection Officer</h4>
                        <p class="text-xs text-blue-100 leading-relaxed">
                            For privacy inquiries, rights enforcement, or record corrections, you may contact the designated PGPC Data Protection Office.
                        </p>
                        <div class="pt-2 border-t border-white/10 text-[11px] text-blue-200 space-y-1">
                            <p><strong>Email:</strong> dpo@padregarcia.gov.ph</p>
                            <p><strong>Office:</strong> Administration Building, Padre Garcia Polytechnic College</p>
                        </div>
                    </div>
                </aside>

                <!-- Right Column: Articles -->
                <main class="lg:col-span-8 bg-white rounded-3xl border border-[#E2E8F0] p-6 sm:p-10 shadow-sm space-y-10 leading-relaxed text-[#334155]">

                    <!-- Section 1 -->
                    <section id="privacy-1" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">1</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Scope & Legal Framework</h2>
                        </div>
                        <p class="text-sm">
                            Padre Garcia Polytechnic College Library is committed to respecting and protecting the personal data of its students, faculty members, and researchers. This Privacy Policy is formulated in strict compliance with Republic Act No. 10173, otherwise known as the Data Privacy Act of 2012 (DPA), its Implementing Rules and Regulations (IRR), and relevant issuances of the National Privacy Commission (NPC).
                        </p>
                    </section>

                    <!-- Section 2 -->
                    <section id="privacy-2" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">2</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Categories of Information Collected</h2>
                        </div>
                        <p class="text-sm">
                            When enrolling in the library system or using our circulation and OPAC facilities, we collect and process the following information:
                        </p>
                        <ul class="list-disc pl-5 space-y-2 text-sm text-slate-600">
                            <li><strong>Identity & Academic Profile:</strong> Official Student/Employee ID Number, Full Name, Academic Program/Course, Year Level, and Enrollment Status.</li>
                            <li><strong>Contact Details:</strong> Official college email address, personal contact email, and mobile phone number for automated loan reminders and account notices.</li>
                            <li><strong>Circulation & Transaction History:</strong> Borrowed titles, checkout dates, return timestamps, renewal history, active reservations, and accumulated fine assessments.</li>
                            <li><strong>System Log Information:</strong> Timestamped authentication sessions, IP addresses, and barcode/RFID scanning records at the circulation desk.</li>
                        </ul>
                    </section>

                    <!-- Section 3 -->
                    <section id="privacy-3" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">3</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Purpose of Data Processing</h2>
                        </div>
                        <p class="text-sm">
                            Personal data collected is utilized strictly for academic, administrative, and circulation management purposes:
                        </p>
                        <ol class="list-decimal pl-5 space-y-1.5 text-sm text-slate-600">
                            <li>Validating patron identity and verifying active enrollment status.</li>
                            <li>Facilitating book borrowing, check-in, renewal, and reserve queues.</li>
                            <li>Dispatching automated notification alerts for upcoming due dates and overdue fines.</li>
                            <li>Validating semester academic clearances and graduation clearance sign-offs.</li>
                            <li>Generating anonymized statistical analytics for institutional accreditation (CHED / ALCUCOA) without publishing personally identifiable details.</li>
                        </ol>
                    </section>

                    <!-- Section 4 -->
                    <section id="privacy-4" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">4</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Data Security & Storage Controls</h2>
                        </div>
                        <p class="text-sm">
                            PGPC implements organizational, physical, and technical security safeguards to prevent unauthorized disclosure, tampering, or loss of patron records:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-600">
                            <li>All user passwords and credential tokens are securely hashed using modern cryptographic algorithms.</li>
                            <li>Access to student circulation history is restricted to designated librarians and library administrators through strict role-based permission policies.</li>
                            <li>Database servers and automated backups are secured within encrypted institutional environments.</li>
                        </ul>
                    </section>

                    <!-- Section 5 -->
                    <section id="privacy-5" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">5</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Non-Disclosure & Confidentiality Commitment</h2>
                        </div>
                        <p class="text-sm">
                            Padre Garcia Polytechnic College Library does not sell, lease, or commercialize patron information under any circumstances. Personal information is never shared with third parties, except in instances where:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-600">
                            <li>Explicit consent has been provided by the patron.</li>
                            <li>Mandated by lawful order from a competent court, legislative authority, or government regulatory agency.</li>
                            <li>Required for official college registrar clearance validation and academic verification.</li>
                        </ul>
                    </section>

                    <!-- Section 6 -->
                    <section id="privacy-6" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">6</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Data Retention & Archival Policy</h2>
                        </div>
                        <p class="text-sm">
                            Student patron records are maintained for the duration of the student's academic residency at Padre Garcia Polytechnic College and for a period of up to five (5) academic years following graduation or transfer, for clearance verification and historical auditing. Upon expiration of the retention schedule, records are securely anonymized or permanently purged from active systems.
                        </p>
                    </section>

                    <!-- Section 7 -->
                    <section id="privacy-7" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">7</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Patron Rights Under RA 10173</h2>
                        </div>
                        <p class="text-sm">
                            Under the Data Privacy Act of 2012, all patrons hold the following rights:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-sm text-slate-600">
                            <li><strong>Right to be Informed:</strong> To understand how their data is gathered, processed, and stored.</li>
                            <li><strong>Right to Access:</strong> To view their student profile and active borrowing/circulation records.</li>
                            <li><strong>Right to Rectification:</strong> To request dispute or correction of inaccurate personal contact details.</li>
                            <li><strong>Right to File a Complaint:</strong> To escalate any privacy concerns to the PGPC Data Protection Office or the National Privacy Commission.</li>
                        </ul>
                    </section>

                    <!-- Section 8 -->
                    <section id="privacy-8" class="scroll-mt-28 space-y-3 border-b border-[#E2E8F0] pb-8">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">8</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Cookie & Session Storage Policy</h2>
                        </div>
                        <p class="text-sm">
                            The PGPC Library portal uses strictly necessary session cookies and tokens to authenticate user logins, protect against cross-site request forgery (CSRF), and maintain OPAC search filtering preferences. We do not deploy third-party advertising cookies or cross-site tracking beacons.
                        </p>
                    </section>

                    <!-- Section 9 -->
                    <section id="privacy-9" class="scroll-mt-28 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-[#EFF6FF] text-[#102B70] text-xs font-bold flex items-center justify-center border border-[#BFDBFE]">9</span>
                            <h2 class="text-lg font-bold text-[#102B70]">Contact the Data Protection Office</h2>
                        </div>
                        <p class="text-sm">
                            If you have inquiries regarding this privacy statement or wish to exercise your data rights, please contact:
                        </p>
                        <div class="rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] p-4 text-xs space-y-1.5 text-slate-700">
                            <p><strong>Data Protection Officer:</strong> Padre Garcia Polytechnic College</p>
                            <p><strong>Campus Address:</strong> {{ config('settings.college.address', 'Brgy. San Roque, Padre Garcia, Batangas, Philippines') }}</p>
                            <p><strong>Institutional Email:</strong> dpo@padregarcia.gov.ph / library@padregarcia.gov.ph</p>
                            <p><strong>Phone:</strong> (043) 515 7722</p>
                        </div>
                    </section>

                </main>
            </div>

        </div>
    </div>
</x-layouts.home>
