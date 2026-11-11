<div wire:key="return-workstation" class="return-workstation-root">
<div class="relative w-full min-w-0 flex flex-col items-center justify-start p-3 sm:p-4 lg:p-6 font-sans bg-[#F8FAFC] check-in-workstation circulation-workstation">

    @include('livewire.components.circulation.workstation-styles')

    <!-- Lock page layout scroll -->
    <style>
        @keyframes loading-pulse {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(330%); }
        }
        .animate-loading-pulse {
            animation: loading-pulse 1.5s infinite linear;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite !important;
            transform-origin: center !important;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="workstation-content w-full min-w-0 flex flex-col">
        <!-- Header Area -->
        <div class="workstation-header w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">

            <!-- Title Row -->
            <div class="flex flex-col md:flex-row pt-2 justify-between items-start md:items-center">
                <div>
                    <h1 class="text-[1.5rem] font-bold text-[#102B70] tracking-tight leading-tight">
                        Return Items
                    </h1>
                    <p class="mt-0.5 text-sm text-[#64748B] font-medium">
                        Scan or enter a member ID or item barcode to process returns.
                    </p>
                </div>
            </div>

            <!-- Flash Success Notification -->
            @if(session()->has('success_message'))
                <div x-data="{ show: true }" x-show="show" x-cloak class="mt-3 bg-[#F0FDF4] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-sm animate-fade-in">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success_message') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-[#15803D] hover:text-[#166534] p-1 leading-none text-base font-bold focus:outline-none">&times;</button>
                </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        @if($showConfirmChangeMember)
            <livewire:components.circulation.top-notification-banner
                type="warning"
                title="Unsaved Returns Session"
                :message="'You have already scanned ' . count($returnedBooks) . ' item(s) in this session. Switching student to <b>' . $pendingStudentName . '</b> will clear this return session. Proceed?'"
                confirm-action="confirmChangeMember"
                confirm-label="Yes, Switch Student"
                cancel-action="cancelChangeMember"
                cancel-label="Cancel"
            />
        @endif

        <div class="workstation-grid grid w-full min-w-0 grid-cols-1 gap-4 lg:grid-cols-[minmax(210px,1fr)_minmax(0,2fr)_minmax(240px,300px)] lg:grid-rows-[auto_1fr]">
            <div class="workstation-search lg:col-span-2 lg:col-start-2 lg:row-start-1 relative overflow-hidden shrink-0 bg-white rounded-xl border border-[#E2E8F0] p-4">
                <!-- Indeterminate Top Progress Bar -->
                <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                    <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                </div>

                <livewire:components.circulation.qr-search-bar
                    label="Member ID / Code"
                    placeholder="Enter or scan member ID or item code"
                />
                @if($errorMessage)
                    @php
                        $isWarning = str_contains(strtolower($errorMessage), 'warning');
                    @endphp
                    <div wire:key="checkin-alert-{{ md5($errorMessage) }}" x-data="{ open: true }" x-init="setTimeout(() => open = false, 6000)" x-show="open" x-transition class="mt-3 flex items-start justify-between gap-3 rounded-lg border p-3 text-sm font-medium {{ $isWarning ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                        <span>{{ $errorMessage }}</span>
                        <button type="button" @click="open = false" class="shrink-0 font-semibold underline underline-offset-2">Dismiss</button>
                    </div>
                @endif
            </div>

            <div class="workstation-scanner lg:col-start-1 lg:row-start-1 lg:row-span-2 min-w-0 flex flex-col gap-4">
                <!-- Scanner Card -->
                <div class="workstation-camera bg-white rounded-xl border border-[#E2E8F0] p-4 sm:p-5 shrink-0">
                    <livewire:components.circulation.live-camera />
                </div>

                <!-- Reusable Scanned Item Details Preview Component -->
                <livewire:components.widgets.circulation-book-preview
                    :book="$lastReturnedBook"
                    mode="check-in"
                    :returned-books="$returnedBooks"
                    :checkout-books="[]"
                />
            </div>

            <livewire:components.circulation.table
                :borrowed-books="$borrowedBooks"
                :scanned-member="$scannedMember"
                :selected-accession="$lastReturnedBook['accession'] ?? null"
                mode="check-in"
                title="Borrowed Items on Account"
                class="workstation-table lg:col-start-2 lg:row-start-2 min-w-0 min-h-[320px]"
            />

            <livewire:components.circulation.summary-panel
                :stats="$stats"
                :scanned-member="$scannedMember"
            />
        </div>
        @if($lostLoans->isNotEmpty())
            <section class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="lost-cases-heading">
                <h2 id="lost-cases-heading" class="font-bold text-slate-900">Lost-book cases</h2>
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach($lostLoans as $loan)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3" wire:key="lost-case-{{ $loan->id }}">
                            <div class="min-w-0 text-sm"><p class="font-semibold">{{ $loan->book?->bookDetail?->bookData?->book_title ?? 'Book' }}</p><p class="text-slate-500">{{ $loan->book?->accession_number }} · {{ $loan->return_date ? 'Recovered' : ($loan->lost_resolved_at ? 'Resolved; copy missing' : 'Unresolved') }}</p></div>
                            <div class="flex flex-wrap gap-2">
                                @if(!$loan->return_date)<button type="button" wire:click="openLostCase({{ $loan->id }})" class="rounded-lg bg-[#102B70] px-3 py-2 text-sm font-semibold text-white">{{ $loan->lost_resolved_at ? 'Record recovery' : 'Resolve case' }}</button>@endif
                                <button type="button" wire:click="reopenWaitlist({{ $loan->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-[#102B70]">Reopen waitlist</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    <!-- Search Entity Modal Component -->
    <livewire:components.circulation.search-entity-modal service="check-in" />
    @include('livewire.pages.dashboard.partials.lost-book-modal')
</div>
</div>
