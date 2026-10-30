<x-layouts.home :title="'Privacy Policy'">
    <main class="mx-auto max-w-3xl px-5 py-10 text-slate-800 sm:px-8 sm:py-14">
        <nav aria-label="Policy navigation" class="mb-8 flex flex-wrap gap-x-5 gap-y-2 text-sm">
            <a href="{{ url('/') }}" class="text-slate-600 underline underline-offset-4 hover:text-[#102B70]">Home</a>
            <a href="{{ route('terms') }}" wire:navigate class="text-slate-600 underline underline-offset-4 hover:text-[#102B70]">Terms of Service</a>
            <a href="{{ route('privacy') }}" aria-current="page" class="font-semibold text-[#102B70] underline underline-offset-4">Privacy Policy</a>
        </nav>

        <header class="mb-10 border-b border-slate-200 pb-6">
            <p class="mb-2 text-sm text-slate-600">Padre Garcia Polytechnic College · Integrated Library Management System</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Privacy Policy</h1>
            <p class="mt-3 text-sm text-slate-600">Last updated: October 29, 2026</p>
        </header>

        <article class="space-y-8 text-[15px] leading-7">
            <p>Padre Garcia Polytechnic College ("PGPC", "we", "our", or "us") is dedicated to protecting your personal information. This Privacy Policy details how the PGPC Integrated Library Management System collects, uses, stores, processes, and protects your information in compliance with the Philippine Data Privacy Act of 2012 (Republic Act No. 10173), its Implementing Rules and Regulations (IRR), and National Privacy Commission (NPC) circulars.</p>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">1. Information We Collect</h2>
                <p>We collect only the minimum personal data necessary to operate library services and power catalog features:</p>
                <h3 class="pt-1 font-semibold text-slate-900">A. User Profile &amp; Account Data</h3>
                <ul class="list-disc space-y-2 pl-6">
                    <li><strong>Student/Faculty Identifiers:</strong> Full name, institutional ID/Student Number, academic department, course/program, and institutional email address.</li>
                    <li><strong>Authentication Credentials:</strong> Encrypted passwords and system authorization session tokens.</li>
                </ul>
                <h3 class="pt-1 font-semibold text-slate-900">B. Transaction &amp; Circulation Records</h3>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Borrowing and return histories, issue dates, due dates, renewal counts, and reservation/hold logs.</li>
                    <li>System fines, penalty statuses, and clearance records.</li>
                </ul>
                <h3 class="pt-1 font-semibold text-slate-900">C. Machine Learning &amp; Behavioral Logs</h3>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Search query interactions, book clicks, catalog browsing sequences, and reading genre preferences.</li>
                    <li>Aggregated circulation figures used to train predictive demand models and collaborative filtering recommendation engines.</li>
                </ul>
                <h3 class="pt-1 font-semibold text-slate-900">D. Technical &amp; Device Data</h3>
                <ul class="list-disc pl-6">
                    <li>IP addresses, browser user-agent strings, access timestamps, and error diagnostics collected automatically for network security and session integrity.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">2. Legal Basis &amp; Purpose of Processing</h2>
                <p>In accordance with Section 12 of RA 10173, data processing is carried out pursuant to institutional compliance, contract fulfillment (library clearance and enrollment privileges), and legitimate academic operational interests:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li><strong>Profile &amp; Credentials:</strong> Verifying campus enrollment, issuing library clearance, and managing role-based access control.</li>
                    <li><strong>Circulation Records:</strong> Tracking physical book inventories, preventing resource loss, and calculating overdue charges.</li>
                    <li><strong>Reading &amp; Interaction Logs:</strong> Powering collaborative filtering for personalized recommendations and forecasting book demand.</li>
                    <li><strong>Technical Logs:</strong> Maintaining server security, diagnosing network latency, and preventing unauthorized scraping.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">3. Machine Learning &amp; Automated Processing</h2>
                <ul class="list-disc space-y-2 pl-6">
                    <li><strong>Recommendation Engine:</strong> The platform applies collaborative filtering algorithms to suggest library titles relevant to your study field and past reading patterns. This processing is internal, non-profiling for credit or surveillance purposes, and strictly educational.</li>
                    <li><strong>Borrowing Demand Forecasting:</strong> Transaction figures are aggregated across departments to project shelf availability and assist library staff in purchasing decisions. This data is anonymized prior to aggregate modeling.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">4. Disclosure and Sharing of Information</h2>
                <p>PGPC maintains a strict policy against selling, leasing, or commercializing personal data. Information is disclosed only under the following conditions:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li><strong>Internal Academic Units:</strong> With the PGPC Registrar, Dean's Offices, or student clearance committees to confirm library accountability prior to semester completion or graduation.</li>
                    <li><strong>System Administration:</strong> Authorized IT administrators maintaining the application servers, database clusters, and cloud-hosted environments.</li>
                    <li><strong>Legal Compliance:</strong> When compelled by lawful subpoena, court order, or regulatory mandate issued by a competent Philippine authority or the National Privacy Commission.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">5. Data Storage, Retention, and Security</h2>
                <p><strong>5.1 Storage Location:</strong> Personal data and library transaction logs are housed on secured databases and containerized cloud instances configured with SSL/TLS encryption in transit and standard access controls at rest.</p>
                <p><strong>5.2 Data Retention:</strong></p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>Active student/faculty accounts and transactions are retained for the duration of enrollment/employment.</li>
                    <li>Circulation records are retained for institutional auditing and clearance verification for up to five (5) academic years following graduation or separation, after which records are anonymized or permanently deleted.</li>
                </ul>
                <p><strong>5.3 Security Safeguards:</strong> We employ role-based access control (RBAC), network firewalls, and least-privilege principles to mitigate risks of data breach, accidental loss, or unauthorized tampering.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">6. Your Rights Under the Data Privacy Act (RA 10173)</h2>
                <p>Under Chapter III of the Data Privacy Act of 2012, PGPC system users possess the following statutory rights:</p>
                <ul class="list-disc space-y-2 pl-6">
                    <li><strong>Right to be Informed:</strong> To know what personal data is collected, entered into systems, and how it is processed.</li>
                    <li><strong>Right to Access:</strong> To view and obtain copies of personal circulation records and account information held in the system.</li>
                    <li><strong>Right to Rectification:</strong> To dispute inaccuracies or errors in your personal profile and request corrections.</li>
                    <li><strong>Right to Erasure or Blocking:</strong> To request the suspension, withdrawal, or removal of personal data upon showing valid grounds (e.g., unlawful processing), subject to institutional clearance and audit requirements.</li>
                    <li><strong>Right to Damages:</strong> To be indemnified for damages sustained due to inaccurate, incomplete, outdated, false, unlawfully obtained, or unauthorized use of personal data.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">7. Cookies and Local Storage</h2>
                <p>The system utilizes necessary session cookies and browser local storage strictly to preserve your authentication state, remember interface preferences, and support responsive navigation. We do not deploy third-party advertising cookies or cross-site tracking scripts.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">8. Changes to This Privacy Policy</h2>
                <p>PGPC may update this Privacy Policy to reflect changing institutional practices or regulatory developments. When updates occur, the revision date at the top of this document will be amended. Users are encouraged to periodically review this page for the latest privacy guidance.</p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-950">9. Data Protection Officer (DPO) Contact Information</h2>
                <p>For inquiries, concerns, or requests regarding your rights under the Data Privacy Act of 2012, contact our designated privacy personnel:</p>
                <ul class="list-disc space-y-1 pl-6">
                    <li>Data Protection Officer / Library Administrator</li>
                    <li>Institution: Padre Garcia Polytechnic College</li>
                    <li>Address: Padre Garcia, Batangas, Philippines</li>
                    <li>Email: <a class="text-[#102B70] underline underline-offset-4" href="mailto:dpo@pgpc.edu.ph">dpo@pgpc.edu.ph</a> / <a class="text-[#102B70] underline underline-offset-4" href="mailto:library@pgpc.edu.ph">library@pgpc.edu.ph</a></li>
                </ul>
            </section>
        </article>
    </main>
</x-layouts.home>
