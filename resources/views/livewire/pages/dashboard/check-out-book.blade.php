<div class="relative w-full min-w-0 flex flex-col items-center justify-start p-3 sm:p-4 lg:p-6 font-sans bg-[#F8FAFC] check-out-workstation circulation-workstation"
     x-data="completedModalHandler()"
     @checkout-completed.window="triggerModal($event.detail)"
     @checkout-failed.window="triggerErrorModal($event.detail)">

    @include('livewire.components.circulation.workstation-styles')
    <!-- Workstation loading indicators -->
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
                    <h1 class="text-xl font-bold text-[#102B70] tracking-tight leading-tight">
                        Borrow Items
                    </h1>
                    <p class="mt-0.5 text-sm text-[#64748B] font-medium">
                        Scan or enter a member ID first, then scan item barcodes to process loans.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="workstation-grid w-full grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(260px,320px)] gap-4 lg:gap-6 relative z-10 mb-2 items-start">

            <!-- Left Section: Column 1 + Column 2 (col-span-9) -->
            <div class="workstation-main min-w-0 flex flex-col gap-4">

                <!-- Switch Student Confirmation Banner -->
                @if($showConfirmChangeMember)
                    <livewire:components.circulation.top-notification-banner
                        type="warning"
                        title="Unsaved Borrow Session"
                        :message="'You have already queued ' . count($checkoutBooks) . ' item(s) in this session. Switching student to <b>' . $pendingStudentName . '</b> will clear this borrow list. Proceed?'"
                        confirm-action="confirmChangeMember"
                        confirm-label="Yes, Switch Student"
                        cancel-action="cancelChangeMember"
                        cancel-label="Cancel"
                    />
                @endif

                <div class="workstation-search relative overflow-hidden shrink-0 bg-white rounded-xl border border-[#E2E8F0] p-4 ">
                    <label for="loan-location" class="block mb-2 text-sm font-semibold text-[#334155]">Loan type</label>
                    <select id="loan-location" wire:model.live="loanLocation" class="mb-4 w-full rounded-lg border border-slate-300 p-2 text-sm">
                        <option value="take_home">Take home</option>
                        <option value="inside_library">Inside library — return by 7:00 PM today</option>
                    </select>
                    <!-- Indeterminate Top Progress Bar -->
                    <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                        <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                    </div>

                    <livewire:components.circulation.qr-search-bar
                        label="Member ID, book code, or reservation reference"
                        placeholder="Scan member ID, book code, or #RES-00001"
                    />
                    @if($errorMessage)
                        @php
                            $isWarning = str_contains(strtolower($errorMessage), 'warning');
                        @endphp
                        <div wire:key="checkout-alert-{{ md5($errorMessage) }}" x-data="{ open: true }" x-init="setTimeout(() => open = false, 6000)" x-show="open" x-transition class="mt-3 flex items-start justify-between gap-3 rounded-lg border p-3 text-sm font-medium {{ $isWarning ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                            <span>{{ $errorMessage }}</span>
                            <button type="button" @click="open = false" class="shrink-0 font-semibold underline underline-offset-2">Dismiss</button>
                        </div>
                    @endif

                    @if($noticeMessage)
                        <div wire:key="checkout-notice-{{ md5($noticeMessage) }}" x-data="{ open: true }" x-init="setTimeout(() => open = false, 5000)" x-show="open" x-transition class="mt-3 flex items-start justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                            <span>{{ $noticeMessage }}</span>
                            <button type="button" @click="open = false" class="shrink-0 font-semibold underline underline-offset-2">Dismiss</button>
                        </div>
                    @endif
                </div>

                <!-- Sub Grid: Camera Scanner & Reusable Table side-by-side -->
                <div class="workstation-details grid grid-cols-1 md:grid-cols-[minmax(220px,1fr)_minmax(0,2fr)] gap-4 min-w-0">

                    <!-- COLUMN 1: Scanner + Scanned Book Details (md:col-span-4) -->
                    <div class="workstation-scanner min-w-0 flex flex-col gap-4">
                        <!-- Scanner Card -->
                        <div class="workstation-camera bg-white rounded-xl border border-[#E2E8F0] p-4 sm:p-5 shrink-0">
                            <livewire:components.circulation.live-camera />
                        </div>

                        <!-- Reusable Scanned Item Details Preview Component -->
                        <livewire:components.widgets.circulation-book-preview
                            :book="$lastScannedBook"
                            mode="check-out"
                            :checkout-books="$checkoutBooks"
                            :returned-books="[]"
                        />
                    </div>

                    <!-- COLUMN 2: Reusable Table Component (md:col-span-8) -->
                    <livewire:components.circulation.table
                        :borrowed-books="$checkoutBooks"
                        :scanned-member="$scannedMember"
                        :selected-accession="$lastScannedBook['accession'] ?? null"
                        mode="check-out"
                        title="Items to Borrow"
                        class="workstation-table min-w-0 min-h-[320px]"
                    />

                </div>
            </div>

            <!-- Right Section: Column 3 (col-span-3 Student Card + Checkout Summary Panel) -->
            <div class="workstation-sidebar min-w-0 w-full flex flex-col gap-4">
                <!-- Student Card -->
                <div wire:loading.class="opacity-65 transition-opacity duration-300" class="workstation-student bg-white rounded-xl border border-[#E2E8F0] p-5 shrink-0 min-h-[220px] flex flex-col">
                    <div class="flex flex-col gap-3 flex-1">
                        <span class="block text-base font-semibold text-[#64748B]">Student</span>

                        @if($scannedMember)
                            @if($scannedMember['reservations_blocked'] ?? false)
                                <div class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">Reservation blacklist: this student cannot reserve items. Circulation remains available.</div>
                            @endif
                            @php
                                $initials = '';
                                if (!empty($scannedMember['name'])) {
                                    $words = explode(' ', $scannedMember['name']);
                                    $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
                                }
                                $isStatusActive = strtolower($scannedMember['status'] ?? 'active') === 'active';
                            @endphp
                            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 flex flex-col gap-4  flex-1 justify-between">
                                <div class="flex items-center gap-3.5">
                                    <!-- Avatar -->
                                    <div class="w-12 h-12 rounded-full bg-[#102B70] text-[#FFFFFF] border-2 flex items-center justify-center shrink-0  text-sm font-bold tracking-wider select-none">
                                        {{ $initials ?: 'S' }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[#0F172A] font-bold text-base leading-tight truncate max-w-[130px] xl:max-w-none" title="{{ $scannedMember['name'] }}">{{ $scannedMember['name'] }}</h3>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-base font-semibold border bg-slate-100 text-slate-700 border-slate-200">
                                                {{ $scannedMember['status'] }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-[#64748B] font-semibold mt-0.5">
                                            <span>{{ $scannedMember['school_id'] }}</span>
                                            <span class="mx-1 text-slate-300">&bull;</span>
                                            <span class="text-sm font-medium">{{ $scannedMember['course'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Change Student Actions -->
                                <div class="flex justify-end" x-data="{ confirmChange: false }">
                                    <button x-show="!confirmChange"
                                            type="button"
                                            @click="confirmChange = true"
                                            class="h-8 px-3.5 border border-[#102B70] bg-white text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-base font-semibold transition-colors flex items-center gap-1.5 ">
                                        Change Student
                                    </button>

                                    <div x-show="confirmChange" class="flex items-center gap-1.5" style="display: none;">
                                        <span class="text-sm font-bold text-[#EF4444] mr-1 select-none">Sure?</span>
                                        <button type="button"
                                                @click="confirmChange = false"
                                                class="h-8 px-2.5 border border-[#CBD5E1] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A] rounded-xl text-base font-semibold transition-colors ">
                                            Cancel
                                        </button>
                                        <button type="button"
                                                wire:click="clearMember"
                                                @click="confirmChange = false"
                                                class="h-8 px-3 bg-[#EF4444] hover:bg-[#B91C1C] text-white rounded-xl text-base font-semibold transition-colors ">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-[#F8FAFC] border border-dashed border-[#CBD5E1] rounded-xl p-6 flex flex-col items-center justify-center text-center gap-2.5 flex-1 mt-1 hover:border-[#102B70] transition-colors duration-200">
                                <div class="w-12 h-12 rounded-full bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shrink-0 ">
                                </div>
                                <span class="text-base font-bold text-[#334155]">No Student Profile</span>
                                <p class="text-sm text-[#64748B] max-w-[190px] leading-relaxed">Scan or enter member ID number to check out.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Checkout Summary Card -->
                <livewire:components.dashboard.check-out-summary
                    :stats="$stats"
                    :scanned-member="$scannedMember"
                    :checkout-books="$checkoutBooks"
                />
            </div>

        </div>
    </div>

    <!-- TRANSACTION SUCCESS COMPLETED COUNTDOWN MODAL -->
    <div x-show="isOpen"
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

         <div class="bg-white rounded-xl p-8 max-w-sm w-full  flex flex-col items-center text-center gap-5 border border-slate-100 relative"
              x-show="isOpen"
              x-transition:enter="transition ease-out duration-300 transform"
              x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave="transition ease-in duration-200 transform"
              x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

              <!-- Success Icon -->

              <!-- Content -->
              <div class="flex flex-col gap-1.5">
                  <h3 class="text-xl font-bold text-[#102B70]">Items Borrowed Successfully</h3>
                  <p class="text-base text-[#64748B] font-semibold leading-relaxed px-4">
                      All items have been successfully borrowed and due date records logged.
                  </p>
              </div>

              <!-- Circular Progress and Timer -->
              <div class="flex flex-col items-center justify-center relative mt-2 gap-2">
                  <div class="text-center">
                      <span class="text-lg font-semibold text-[#102B70]" x-text="countdown">3</span>
                  </div>
                  <span class="text-base text-slate-500 mt-1">Redirecting to start...</span>
              </div>
         </div>
    </div>

    <!-- TRANSACTION FAILURE ERROR MODAL -->
    <div x-show="isErrorOpen"
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

         <div class="bg-white rounded-xl p-8 max-w-sm w-full  flex flex-col items-center text-center gap-5 border border-slate-100 relative"
              x-show="isErrorOpen"
              x-transition:enter="transition ease-out duration-300 transform"
              x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave="transition ease-in duration-200 transform"
              x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

              <!-- Error Icon -->

              <!-- Content -->
              <div class="flex flex-col gap-1.5">
                  <h3 class="text-xl font-semibold text-[#102B70]">Borrow Failed</h3>
                  <p class="text-base text-[#64748B] font-semibold leading-relaxed px-4" x-text="errorMessage">
                      An error occurred during submission. Please check conditions and try again.
                  </p>
              </div>

              <button type="button"
                      @click="isErrorOpen = false"
                      class="h-11 w-full rounded-xl border border-[#CBD5E1] bg-white text-base font-medium text-slate-700 hover:bg-[#F8FAFC]">
                  Dismiss
              </button>
         </div>
    </div>

    <!-- Search Entity Modal component -->
    <livewire:components.circulation.search-entity-modal service="check-out" />

    <script>
        function completedModalHandler() {
            return {
                isOpen: false,
                isErrorOpen: false,
                errorMessage: '',
                countdown: 3,
                timeRemaining: 3.0,
                dashOffset: 0,
                redirectUrl: '',
                timer: null,

                triggerModal(detail) {
                    this.redirectUrl = detail.redirectUrl;
                    this.isOpen = true;
                    this.countdown = 3;
                    this.timeRemaining = 3.0;
                    this.dashOffset = 0;

                    if (this.timer) clearInterval(this.timer);

                    const intervalMs = 100;
                    const totalDuration = 3000;
                    let elapsed = 0;

                    this.timer = setInterval(() => {
                        elapsed += intervalMs;
                        this.timeRemaining = Math.max(0, (totalDuration - elapsed) / 1000);
                        this.countdown = Math.ceil(this.timeRemaining);
                        this.dashOffset = (elapsed / totalDuration) * 214;

                        if (elapsed >= totalDuration) {
                            clearInterval(this.timer);
                            if (window.Livewire) {
                                window.Livewire.navigate(this.redirectUrl);
                            } else {
                                window.location.href = this.redirectUrl;
                            }
                        }
                    }, intervalMs);
                },

                triggerErrorModal(detail) {
                    this.errorMessage = detail.message || 'An unexpected error occurred during check-out.';
                    this.isErrorOpen = true;
                }
            }
        }

        // Global network interceptor for client/server connection dropouts (supports wire:navigate)
        function registerLivewireHooks() {
            const hookCallback = ({ fail }) => {
                fail(({ status, content, preventDefault }) => {
                    window.dispatchEvent(new CustomEvent('checkout-failed', {
                        detail: { message: 'A server connection or database error occurred (Status: ' + status + '). Please verify network status and try again.' }
                    }));
                    preventDefault();
                });
            };

            if (window.Livewire) {
                Livewire.hook('request', hookCallback);
            } else {
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('request', hookCallback);
                });
            }
        }
        registerLivewireHooks();
    </script>
</div>
