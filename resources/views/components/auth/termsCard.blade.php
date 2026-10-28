<div class="p-6 sm:p-8 space-y-4 text-slate-700 select-text">
    <div class="border-b border-slate-200 pb-4">
        <h3 class="text-xl font-bold text-[#102B70]">PGPC Library Terms of Service</h3>
        <p class="text-xs text-slate-500 mt-1">Official Institutional Policy &bull; Last updated: {{ date('F Y') }}</p>
    </div>

    <div class="space-y-3.5 text-sm leading-relaxed">
        <div>
            <h4 class="font-bold text-slate-900">1. Account Responsibility & Identification</h4>
            <p class="mt-0.5 text-slate-600">As an enrolled student of Padre Garcia Polytechnic College, you are responsible for all library transactions conducted through your student account. Your student ID and credentials must remain confidential and cannot be transferred to other individuals.</p>
        </div>

        <div>
            <h4 class="font-bold text-slate-900">2. Borrowing & Circulation Policies</h4>
            <p class="mt-0.5 text-slate-600">Eligible patrons may borrow up to <strong>{{ config('settings.library_config.max_loan_limit', 3) }} books</strong> simultaneously for a standard loan period of <strong>{{ config('settings.library_config.loan_period', 5) }} days</strong>. All materials must be returned on or before their specified due date at the Circulation Desk ({{ config('settings.library_config.location') ?? config('settings.library_location') }}).</p>
        </div>

        <div>
            <h4 class="font-bold text-slate-900">3. Overdue Fines & Penalties</h4>
            <p class="mt-0.5 text-slate-600">Failure to return loaned items on time incurs an overdue penalty of <strong>₱{{ number_format(config('settings.library_config.penalty_fee', 50), 2) }} per calendar day</strong> per book. Outstanding balances will result in temporary suspension of borrowing privileges and clearance holds.</p>
        </div>

        <div>
            <h4 class="font-bold text-slate-900">4. Care of Library Materials & Loss Assessment</h4>
            <p class="mt-0.5 text-slate-600">Borrowers are strictly accountable for maintaining borrowed resources in good condition. Damaged, defaced, or lost items must be reported immediately and settled through replacement with an identical copy or reimbursement of the replacement cost.</p>
        </div>

        <div>
            <h4 class="font-bold text-slate-900">5. Code of Conduct & Fair Use</h4>
            <p class="mt-0.5 text-slate-600">Respectful, quiet academic conduct is mandatory across all library facilities, study zones, and online OPAC search services.</p>
        </div>
    </div>
</div>

