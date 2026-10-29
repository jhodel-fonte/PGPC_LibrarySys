<div
    x-data="{ state: @entangle('state') }"
    wire:poll.15s="refreshStatus"
    class="w-full rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-8 shadow-xs font-sans text-[#0F172A] space-y-6"
>
    <!-- ==================================================== -->
    <!-- STATE 1: WAITING FOR APPROVAL (PENDING)              -->
    <!-- ==================================================== -->
    <div x-show="state === 'pending'" class="space-y-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#0F172A]">
                Waiting for Account Approval
            </h1>
            <p class="mt-1.5 text-sm sm:text-base text-[#64748B] leading-relaxed">
                Your registration details and verification document have been submitted. A library administrator or librarian will review your document shortly.
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 sm:p-5 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-2">
                <span class="font-semibold text-[#0F172A]">1. Account Registration</span>
                <span class="text-xs sm:text-sm font-bold text-[#102B70]">Completed</span>
            </div>
            <div class="flex items-center justify-between gap-2 border-t border-[#E2E8F0] pt-3">
                <span class="font-semibold text-[#0F172A]">2. Verification Document Uploaded</span>
                <span class="text-xs sm:text-sm font-bold text-[#102B70]">Submitted</span>
            </div>
            <div class="flex items-center justify-between gap-2 border-t border-[#E2E8F0] pt-3">
                <span class="font-semibold text-[#334155]">3. Librarian / Admin Review</span>
                <span class="text-xs sm:text-sm font-bold text-[#334155]">In Progress</span>
            </div>
        </div>

        <!-- Submitted Details Summary -->
        <div class="rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 space-y-3 text-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Submitted Application Details</p>
            <div class="space-y-2.5 text-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Student Name:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $studentName }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Student ID:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $studentIdNumber }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Program & Year:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $programYear }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Document Type:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $documentTypeLabel }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Submitted On:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $submittedAt }}</span>
                </div>
            </div>
        </div>

        <!-- Access Note -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 text-xs sm:text-sm text-[#64748B] leading-relaxed">
            While your account is awaiting verification, you can still browse and search the online catalog (OPAC). Dashboard access, profile settings, book reservations, and borrowing will unlock automatically once your account is approved.
        </div>

        <!-- Actions (No Replace Uploaded Document button while waiting) -->
        <div class="space-y-3 pt-1">
            <a
                href="{{ route('opac.index') }}"
                wire:navigate
                class="flex h-14 w-full items-center justify-center rounded-2xl bg-[#102B70] text-sm sm:text-base font-bold text-white transition hover:bg-[#0B225E]"
            >
                Browse OPAC Catalog
            </a>
            <div class="flex items-center justify-center pt-1 text-xs sm:text-sm font-semibold text-[#64748B]">
                <a href="{{ route('logout') }}" class="hover:text-[#B91C1C] hover:underline">
                    Sign Out
                </a>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- STATE 2: NOT APPROVED / RESUBMIT REQUIRED (REJECTED) -->
    <!-- ==================================================== -->
    <div x-show="state === 'rejected'" x-cloak class="space-y-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#0F172A]">
                Document Resubmission Required
            </h1>
            <p class="mt-1.5 text-sm sm:text-base text-[#64748B] leading-relaxed">
                The librarian reviewed your submitted verification document and requested a new upload before activating your account.
            </p>
        </div>

        <!-- Reviewer Remarks Box -->
        <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 sm:p-5 space-y-1.5">
            <p class="text-xs font-bold uppercase tracking-wider text-[#B91C1C]">Reason / Librarian Remarks</p>
            <p class="text-sm sm:text-base font-semibold text-[#0F172A]">
                "{{ $remarks }}"
            </p>
            <p class="text-xs sm:text-sm text-[#64748B]">Reviewed on {{ $reviewedAt }}</p>
        </div>

        <!-- Submitted Details Summary -->
        <div class="rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 space-y-3 text-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Account Details</p>
            <div class="space-y-2.5 text-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Student Name:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $studentName }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Student ID:</span>
                    <span class="font-semibold text-[#0F172A]">{{ $studentIdNumber }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                    <span class="text-[#64748B]">Previous Upload:</span>
                    <span class="font-semibold text-[#B91C1C]">{{ $documentTypeLabel }} (Declined)</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-3 pt-1">
            <a
                href="{{ route('verification.upload') }}"
                wire:navigate
                class="flex h-14 w-full items-center justify-center rounded-2xl bg-[#102B70] text-sm sm:text-base font-bold text-white transition hover:bg-[#0B225E]"
            >
                Upload or Capture New Document
            </a>
            <div class="flex items-center justify-between pt-1 text-xs sm:text-sm font-semibold text-[#64748B]">
                <a href="{{ route('opac.index') }}" wire:navigate class="hover:text-[#102B70] hover:underline">
                    Browse OPAC Catalog
                </a>
                <a href="{{ route('logout') }}" class="hover:text-[#B91C1C] hover:underline">
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</div>
