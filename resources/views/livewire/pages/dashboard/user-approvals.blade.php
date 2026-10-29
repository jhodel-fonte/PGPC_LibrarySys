<div class="bg-[#F8FAFC] flex flex-col min-h-0 flex-1 overflow-hidden font-sans text-[#0F172A]">
<div
    x-data="{
        selected: @entangle('selectedIds').live,

        isSelected(id) {
            const sId = String(id);
            return Array.isArray(this.selected) && this.selected.some(x => String(x) === sId);
        },

        toggle(id) {
            const sId = String(id);
            if (!Array.isArray(this.selected)) this.selected = [];
            const idx = this.selected.findIndex(x => String(x) === sId);
            if (idx > -1) {
                this.selected.splice(idx, 1);
            } else {
                this.selected.push(sId);
            }
        },

        toggleCurrentPage(ids) {
            if (!Array.isArray(this.selected)) this.selected = [];
            const strIds = ids.map(x => String(x));
            const allSelected = strIds.length > 0 && strIds.every(id => this.isSelected(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !strIds.includes(String(id)));
            } else {
                const toAdd = strIds.filter(id => !this.isSelected(id));
                this.selected = [...this.selected, ...toAdd];
            }
        },

        isAllCurrentPageSelected(ids) {
            if (!Array.isArray(this.selected)) return false;
            const strIds = ids.map(x => String(x));
            return strIds.length > 0 && strIds.every(id => this.isSelected(id));
        },

        isSomeCurrentPageSelected(ids) {
            if (!Array.isArray(this.selected)) return false;
            const strIds = ids.map(x => String(x));
            return strIds.some(id => this.isSelected(id)) && !this.isAllCurrentPageSelected(ids);
        },

        clearAll() {
            this.selected = [];
        }
    }"
    wire:poll.15s
    class="bg-[#F8FAFC] flex flex-col min-h-0 flex-1 overflow-hidden font-sans text-[#0F172A]"
>
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-4 sm:gap-5 min-h-0 flex-1">
        <!-- 1. Page Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between shrink-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Account Registration Approvals</h1>
                <p class="mt-0.5 text-sm sm:text-base text-[#64748B]">
                    Review uploaded School IDs and registration documents to verify and activate new student accounts.
                </p>
            </div>
        </div>

        <!-- 2. Summary Cards -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4 shrink-0">
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4">
                <p class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-[#64748B]">Pending Review</p>
                <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#102B70]">{{ $counts['Pending'] }}</p>
                <p class="mt-0.5 text-xs sm:text-sm text-[#64748B]">Awaiting librarian or admin verification</p>
            </div>

            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4">
                <p class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-[#64748B]">Needs Resubmission</p>
                <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#B91C1C]">{{ $counts['Not Approved'] }}</p>
                <p class="mt-0.5 text-xs sm:text-sm text-[#64748B]">Declined or requested clearer document</p>
            </div>

            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4">
                <p class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-[#64748B]">Approved Accounts</p>
                <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#0F172A]">{{ $counts['Approved'] }}</p>
                <p class="mt-0.5 text-xs sm:text-sm text-[#64748B]">Verified and granted full library access</p>
            </div>
        </div>

        <!-- 3. Filter Tabs, Search, Scrollable Table & Pagination Card -->
        <div class="flex flex-col min-h-0 flex-1 overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-2xs">
            <!-- Toolbar -->
            <div class="flex flex-col gap-3 border-b border-[#E2E8F0] p-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between shrink-0">
                <!-- Tabs -->
                <div class="flex flex-wrap items-center gap-2">
                    @foreach(['Pending', 'Not Approved', 'Approved', 'All'] as $tab)
                        <button
                            type="button"
                            wire:click="setTab('{{ $tab }}')"
                            class="rounded-xl px-4 py-2 text-xs sm:text-sm font-bold transition cursor-pointer {{ $activeTab === $tab ? 'bg-[#102B70] text-white' : 'bg-[#F8FAFC] text-[#334155] hover:bg-slate-100' }}"
                        >
                            {{ $tab }} ({{ $counts[$tab] ?? 0 }})
                        </button>
                    @endforeach
                </div>

                <!-- Search Input -->
                <div class="w-full lg:w-80">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search name, Student ID, or program..."
                        class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 text-sm font-semibold text-[#0F172A] outline-none transition focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF]"
                    >
                </div>
            </div>

            <!-- Bulk Action Bar -->
            @if(count($selectedIds) > 0)
                <!-- Bulk Action Bar (Alpine-persisted across pagination) -->
            <div
                x-show="Array.isArray(selected) && selected.length > 0"
                x-cloak
                class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between border-b border-[#E2E8F0] bg-[#EFF6FF] px-4 sm:px-5 py-3 text-sm shrink-0"
            >
                <span class="font-bold text-[#102B70]">
                    <span x-text="Array.isArray(selected) ? selected.length : 0"></span> account(s) selected (retained across pages)
                </span>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="bulkApprove"
                        class="rounded-xl bg-[#102B70] px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-[#0B225E] cursor-pointer"
                    >
                        Approve Selected
                    </button>
                    <button
                        type="button"
                        wire:click="bulkReject"
                        class="rounded-xl border border-[#FECACA] bg-[#FEF2F2] px-4 py-2 text-xs sm:text-sm font-bold text-[#B91C1C] hover:bg-rose-100 cursor-pointer"
                    >
                        Reject Selected
                    </button>
                    <button
                        type="button"
                        @click="clearAll()"
                        wire:click="$set('selectedIds', [])"
                        class="rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-[#334155] hover:bg-[#F8FAFC] cursor-pointer"
                    >
                        Clear
                    </button>
                </div>
            </div>
            @endif

            <!-- Scrollable Table Container -->
            @php
                $visibleIds = $filteredApplicants->pluck('id')->all();
                $allVisibleSelected = count($visibleIds) > 0 && count(array_diff(array_map('strval', $visibleIds), array_map('strval', $selectedIds))) === 0;
            @endphp
            <div class="w-full relative flex-1 min-h-0 overflow-auto">
                <table class="min-w-full text-left text-sm sm:text-base border-collapse">
                    <thead class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] text-xs sm:text-sm font-bold uppercase tracking-wider text-[#334155]">
                        <tr>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] w-12 px-4 sm:px-5 py-3.5">
                                @if(count($visibleIds) > 0)
                                    <input
                                        type="checkbox"
                                        wire:click="toggleSelectAll(@js($visibleIds))"
                                        @checked($allVisibleSelected)
                                        aria-label="Select all visible accounts on this page"
                                        class="h-4 w-4 rounded border-[#CBD5E1] text-[#102B70] focus:ring-[#102B70]/20 cursor-pointer"
                                    >
                                @endif
                            </th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Student</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Student ID</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Program & Year</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Submitted Document</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Submitted At</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 whitespace-nowrap">Status</th>
                            <th class="sticky top-0 z-20 bg-[#F8FAFC] shadow-[inset_0_-1px_0_#E2E8F0] px-4 sm:px-5 py-3.5 text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] bg-white">
                        @forelse($filteredApplicants as $applicant)
                            <tr
                                wire:key="approval-row-{{ $applicant['id'] }}"
                                :class="isSelected('{{ $applicant['id'] }}') ? 'bg-[#EFF6FF]/60' : 'hover:bg-[#F8FAFC]'"
                                class="transition-colors"
                            >
                                <td class="px-4 sm:px-5 py-4 align-middle">
                                    <input
                                        type="checkbox"
                                        value="{{ (string) $applicant['id'] }}"
                                        wire:model.live="selectedIds"
                                        class="h-4 w-4 rounded border-[#CBD5E1] text-[#102B70] focus:ring-[#102B70]/20 cursor-pointer"
                                    >
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle whitespace-nowrap">
                                    <p class="text-sm sm:text-base font-bold text-[#0F172A]">{{ $applicant['name'] }}</p>
                                    <p class="text-xs sm:text-sm text-[#64748B]">{{ $applicant['email'] }}</p>
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle font-mono text-sm sm:text-base font-semibold text-[#334155] whitespace-nowrap">
                                    {{ $applicant['student_id'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle text-sm sm:text-base font-semibold text-[#334155] whitespace-nowrap">
                                    {{ $applicant['program'] }} · {{ $applicant['year_level'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openReview({{ $applicant['id'] }})"
                                        class="text-left group cursor-pointer"
                                    >
                                        <span class="block text-sm sm:text-base font-bold text-[#102B70] group-hover:underline">
                                            {{ $applicant['document_type'] }}
                                        </span>
                                        <span class="block text-xs sm:text-sm text-[#64748B]">
                                            {{ $applicant['document_file'] }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle text-sm text-[#64748B] whitespace-nowrap">
                                    {{ $applicant['submitted_at'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle whitespace-nowrap">
                                    @if($applicant['status'] === 'Pending')
                                        <span class="inline-flex rounded-full bg-[#EFF6FF] px-3 py-1 text-xs sm:text-sm font-bold text-[#102B70]">
                                            Pending
                                        </span>
                                    @elseif($applicant['status'] === 'Not Approved')
                                        <span class="inline-flex rounded-full border border-[#FECACA] bg-[#FEF2F2] px-3 py-1 text-xs sm:text-sm font-bold text-[#B91C1C]">
                                            Not Approved
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs sm:text-sm font-bold text-emerald-700">
                                            Approved
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-4 align-middle text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openReview({{ $applicant['id'] }})"
                                        class="rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-xs sm:text-sm font-bold text-[#102B70] hover:bg-[#F8FAFC] cursor-pointer"
                                    >
                                        Review Document
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm sm:text-base text-[#64748B]">
                                    No registration requests found in this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-5 py-3.5 border-t border-[#E2E8F0] bg-white flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-[#64748B] font-medium shrink-0">
                <div>
                    @if($paginator->total() > 0)
                        Showing <span class="font-bold text-[#0F172A]">{{ $paginator->firstItem() }}</span> to <span class="font-bold text-[#0F172A]">{{ $paginator->lastItem() }}</span> of <span class="font-bold text-[#0F172A]">{{ number_format($paginator->total()) }}</span> {{ $paginator->total() === 1 ? 'result' : 'results' }}
                    @else
                        Showing <span class="font-bold text-[#0F172A]">0</span> results
                    @endif
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    @if($paginator->hasPages())
                        <div class="flex items-center">
                            {{ $paginator->links('components.widget.pagination') }}
                        </div>
                    @endif

                    <div class="relative">
                        <select
                            wire:model.live="perPage"
                            class="h-8 pl-3 pr-7 rounded-lg border border-[#CBD5E1] bg-white text-xs font-semibold text-[#334155] outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] transition-colors cursor-pointer"
                        >
                            @foreach([5, 10, 25, 50] as $option)
                                <option value="{{ $option }}">{{ $option }} / page</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Review Document Modal -->
    @if($reviewingApplicant)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="w-full max-w-5xl xl:max-w-6xl max-h-[92dvh] overflow-y-auto rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-7 lg:p-8 shadow-xl space-y-6 my-auto">
                <div class="flex items-start justify-between gap-3 border-b border-[#E2E8F0] pb-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-[#0F172A]">Review Student Verification Document</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-[#64748B]">
                            Verify that the uploaded document matches the student's registered details.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeReview"
                        class="shrink-0 rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-1.5 text-sm font-bold text-[#334155] hover:bg-[#F8FAFC] hover:text-[#0F172A] cursor-pointer"
                    >
                        Close
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
                    <!-- Left: Uploaded Document Preview Box (Wider 7 cols on desktop) -->
                    <div class="lg:col-span-7 flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-5 space-y-4">
                        <div>
                            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#64748B]">
                                {{ $reviewingApplicant['document_type'] }}
                            </p>
                            <p class="text-xs sm:text-sm text-[#64748B] mt-0.5 break-all">{{ $reviewingApplicant['document_file'] }}</p>
                        </div>

                        <div class="flex h-64 sm:h-80 lg:h-96 flex-col items-center justify-center rounded-xl border border-dashed border-[#CBD5E1] bg-white p-6 text-center">
                            @if($reviewingApplicant['document_url'])
                                @if($reviewingApplicant['is_pdf'])
                                    <iframe
                                        src="{{ $reviewingApplicant['document_url'] }}"
                                        title="Verification document for {{ $reviewingApplicant['name'] }}"
                                        class="h-full min-h-60 w-full rounded-lg"
                                    ></iframe>
                                @else
                                    <img
                                        src="{{ $reviewingApplicant['document_url'] }}"
                                        alt="Verification document for {{ $reviewingApplicant['name'] }}"
                                        class="max-h-full max-w-full rounded-lg object-contain"
                                    >
                                @endif
                            @else
                                <p class="text-sm sm:text-base font-bold text-[#0F172A]">No document uploaded</p>
                                <p class="mt-1 text-xs sm:text-sm text-[#64748B]">
                                    The student must upload a verification document before approval.
                                </p>
                            @endif
                        </div>

                        <p class="text-xs sm:text-sm text-[#64748B]">
                            Submitted: {{ $reviewingApplicant['submitted_at'] }}
                        </p>
                        @if($reviewingApplicant['document_url'])
                            <a
                                href="{{ $reviewingApplicant['document_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-bold text-[#102B70] underline underline-offset-2"
                            >
                                Open document in a new tab
                            </a>
                        @endif
                    </div>

                    <!-- Right: Registered Details & Remarks (5 cols on desktop) -->
                    <div class="lg:col-span-5 flex flex-col justify-between space-y-5">
                        <div class="rounded-2xl border border-[#E2E8F0] p-5 space-y-3 text-sm sm:text-base">
                            <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#64748B]">Registered Student Info</p>

                            <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5 pt-1">
                                <span class="text-[#64748B]">Full Name:</span>
                                <span class="font-bold text-[#0F172A]">{{ $reviewingApplicant['name'] }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5">
                                <span class="text-[#64748B]">Student ID:</span>
                                <span class="font-mono font-bold text-[#0F172A]">{{ $reviewingApplicant['student_id'] }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5">
                                <span class="text-[#64748B]">Program & Year:</span>
                                <span class="font-semibold text-[#0F172A]">{{ $reviewingApplicant['program'] }} · {{ $reviewingApplicant['year_level'] }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5">
                                <span class="text-[#64748B]">Email:</span>
                                <span class="font-semibold text-[#0F172A] break-all">{{ $reviewingApplicant['email'] }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-0.5">
                                <span class="text-[#64748B]">Contact:</span>
                                <span class="font-semibold text-[#0F172A]">{{ $reviewingApplicant['contact'] }}</span>
                            </div>
                        </div>

                        <!-- Rejection / Resubmission Reason Input -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#334155]">
                                Remarks / Reason (if not approved)
                            </label>
                            <textarea
                                rows="4"
                                wire:model="rejectionRemarks"
                                placeholder="e.g., ID photo is blurry. Please retake a clear photo of your School ID."
                                class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3.5 text-sm sm:text-base text-[#0F172A] outline-none focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF]"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col-reverse gap-2.5 border-t border-[#E2E8F0] pt-4 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="button"
                        wire:click="closeReview"
                        class="h-11 sm:h-12 rounded-xl border border-[#CBD5E1] bg-white px-5 text-sm sm:text-base font-bold text-[#334155] hover:bg-[#F8FAFC] cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="blockApplicant({{ $reviewingApplicant['id'] }})"
                        wire:confirm="Reject this registration and block this email from registering for {{ config('settings.registration.email_block_days', 3) }} days?"
                        class="h-11 sm:h-12 rounded-xl border border-[#7F1D1D] bg-[#7F1D1D] px-5 text-sm sm:text-base font-bold text-white hover:bg-[#991B1B] cursor-pointer"
                    >
                        Reject Registration
                    </button>
                    <button
                        type="button"
                        wire:click="rejectApplicant({{ $reviewingApplicant['id'] }})"
                        class="h-11 sm:h-12 rounded-xl border border-[#FECACA] bg-[#FEF2F2] px-5 text-sm sm:text-base font-bold text-[#B91C1C] hover:bg-rose-100 cursor-pointer"
                    >
                        Not Approve (Request Resubmission)
                    </button>
                    <button
                        type="button"
                        wire:click="approveApplicant({{ $reviewingApplicant['id'] }})"
                        class="h-11 sm:h-12 rounded-xl bg-[#102B70] px-6 text-sm sm:text-base font-bold text-white hover:bg-[#0B225E] cursor-pointer"
                    >
                        Approve & Activate Account
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
