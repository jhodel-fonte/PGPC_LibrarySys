<x-layouts.home :title="'Terms of Service'">
    <main class="mx-auto max-w-3xl px-5 py-10 text-slate-800 sm:px-8 sm:py-14">
        <nav aria-label="Policy navigation" class="mb-8 flex flex-wrap gap-x-5 gap-y-2 text-sm">
            <a href="{{ url('/') }}" class="text-slate-600 underline underline-offset-4 hover:text-[#102B70]">Home</a>
            <a href="{{ route('terms') }}" aria-current="page" class="font-semibold text-[#102B70] underline underline-offset-4">Terms of Service</a>
            <a href="{{ route('privacy') }}" wire:navigate class="text-slate-600 underline underline-offset-4 hover:text-[#102B70]">Privacy Policy</a>
        </nav>

        <header class="mb-10 border-b border-slate-200 pb-6">
            <p class="mb-2 text-sm text-slate-600">Padre Garcia Polytechnic College · Integrated Library Management System</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Terms of Service</h1>
            <p class="mt-3 text-sm text-slate-600">Last updated: October 29, 2026</p>
        </header>

        <article class="space-y-8 text-[15px] leading-7">
            <div class="space-y-4">
                <p>Welcome to the Padre Garcia Polytechnic College Integrated Library Management System ("PGPC ILMS", "we", "our", or "us"). These Terms of Service ("Terms") govern your access to and use of the PGPC ILMS web platform and related services (collectively, the "Service").</p>
                <p>By accessing, browsing, or using the Service, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy. If you do not agree with these Terms, you must not access or use the Service.</p>
            </div>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">1. Eligibility &amp; User Accounts</h2>
                <p><strong>1.1 Authorized Users:</strong> Access to authenticated features of the Service is strictly restricted to currently enrolled students, faculty members, academic personnel, and authorized administrative staff of Padre Garcia Polytechnic College ("PGPC").</p>
                <p><strong>1.2 Account Responsibility:</strong> You are responsible for safeguarding your login credentials. You agree not to disclose your password or access token to any third party and to notify PGPC Library administrators immediately upon becoming aware of any unauthorized use or security breach.</p>
                <p><strong>1.3 Accuracy of Information:</strong> You agree to provide accurate, current, and complete information during registration and to maintain and update such information as necessary.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">2. Permitted Use &amp; Service Scope</h2>
                <p><strong>2.1 Core Scope:</strong> The Service is provided solely to facilitate physical book discovery, shelf localization, borrowing and return tracking, account management, and algorithmic recommendation/prediction services for the PGPC community.</p>
                <p><strong>2.2 Exclusions:</strong> The Service does not provide e-book distribution, digital media streaming, external journal databases, or procurement/acquisition processing.</p>
                <p><strong>2.3 In-System Communications:</strong> Operational notices (due dates, return confirmations, hold statuses) are delivered via in-system alerts and web dashboard banners. Failure to check system notices does not exempt users from overdue fines or campus library disciplinary policies.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">3. Prohibited Activities</h2>
                <p>You agree that you will not:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Use the Service for any unlawful purpose or in violation of PGPC institutional policies or student handbooks.</li>
                    <li>Attempt to reverse-engineer, decompile, crawl, scrape, or extract source code or underlying datasets from the platform or its integrated recommendation microservices.</li>
                    <li>Introduce viruses, malware, trojan horses, or other malicious code designed to disrupt, overload, or compromise system operations or server infrastructure.</li>
                    <li>Impersonate any person or entity, or falsely state or misrepresent your affiliation with PGPC or any student/faculty account.</li>
                    <li>Interfere with or circumvent system security controls, rate limits, or role-based access permissions.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">4. Intellectual Property Rights</h2>
                <p><strong>4.1 Institutional Ownership:</strong> The Service, its underlying source code, database architectures, user interface design, logos, graphics, and documentation are the property of PGPC and its respective developers, protected by Philippine copyright, trademark, and intellectual property laws.</p>
                <p><strong>4.2 Limited License:</strong> PGPC grants you a personal, non-exclusive, non-transferable, revocable license to access and use the platform solely for academic and library-related purposes in accordance with these Terms.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">5. Termination &amp; Suspension</h2>
                <p>We reserve the right to suspend or terminate your account and access to the Service immediately, without prior notice or liability, for any reason, including without limitation:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Breach of these Terms or institutional academic codes of conduct.</li>
                    <li>Loss of active student or faculty status at PGPC.</li>
                    <li>Engaging in fraudulent borrowing activities or tampering with catalog records.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">6. Disclaimer of Warranties</h2>
                <p>The Service is provided on an "AS IS" and "AS AVAILABLE" basis without warranties of any kind, whether express or implied. We do not warrant that:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>The Service will function uninterrupted, secure, or bug-free at all times.</li>
                    <li>The algorithmic recommendations or borrowing demand predictions will be completely accurate or reflect immediate physical shelf availability.</li>
                    <li>Any system errors or defects will be resolved immediately.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">7. Limitation of Liability</h2>
                <p>To the maximum extent permitted by applicable law, PGPC, its administrators, faculty, developers, and personnel shall not be liable for any direct, indirect, incidental, special, consequential, or punitive damages resulting from:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Your access to, use of, or inability to access or use the Service.</li>
                    <li>Any unauthorized access to or alteration of your transmissions or data.</li>
                    <li>Physical book shortages or scheduling conflicts arising from system prediction estimates.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">8. Governing Law &amp; Dispute Resolution</h2>
                <p>These Terms shall be governed and construed in accordance with the laws of the Republic of the Philippines. Any disputes arising out of or relating to these Terms or the Service shall be subject to the exclusive jurisdiction of the competent courts in the Province of Batangas, Philippines, following reasonable efforts to resolve disputes through internal PGPC administrative channels.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">9. Modifications to Terms</h2>
                <p>We reserve the right to modify or replace these Terms at any time. Changes become effective upon posting the updated Terms on the Service. Your continued use of the Service following the posting of any changes constitutes acceptance of those changes.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">10. Contact Us</h2>
                <p>If you have questions regarding these Terms, contact:</p>
                <ul class="list-disc space-y-1 pl-6">
                    <li><strong>Office:</strong> Padre Garcia Polytechnic College - Library Services</li>
                    <li><strong>Location:</strong> Padre Garcia, Batangas, Philippines</li>
                    <li><strong>Email:</strong> <a class="text-[#102B70] underline underline-offset-4" href="mailto:library@pgpc.edu.ph">library@pgpc.edu.ph</a></li>
                </ul>
            </section>
        </article>
    </main>
</x-layouts.home>
