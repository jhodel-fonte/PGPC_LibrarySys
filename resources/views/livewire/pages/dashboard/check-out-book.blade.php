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
        <div class="workstation-header w-full flex flex-col mb-4 relative z-10 shrink-0">
            <!-- Title Row -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
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

        <div class="workstation-grid grid w-full min-w-0 grid-cols-1 gap-4 lg:grid-cols-[minmax(210px,1fr)_minmax(0,2fr)_minmax(240px,300px)] lg:grid-rows-[auto_1fr]">
            <div class="workstation-search lg:col-span-2 lg:col-start-2 lg:row-start-1 relative overflow-hidden shrink-0 bg-white rounded-xl border border-[#E2E8F0] p-4">
                <!-- Indeterminate Top Progress Bar -->
                <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                    <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-[minmax(180px,240px)_minmax(0,1fr)]">
                    <div class="flex min-w-0 flex-col gap-1.5">
                        <label for="loan-location" class="text-xs font-bold uppercase tracking-wider text-[#334155]">Loan type</label>
                        <select id="loan-location" wire:model="loanLocation" class="h-14 w-full rounded-xl border border-slate-300 px-3 text-sm">
                            <option value="take_home">Take home</option>
                            <option value="inside_library">Inside library — return by 7:00 PM today</option>
                        </select>
                    </div>
                    <livewire:components.circulation.qr-search-bar
                        label="Member ID, book code, or reservation reference"
                        placeholder="Scan member ID, book code, or #RES-00001"
                    />
                </div>
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

            <div class="workstation-scanner lg:col-start-1 lg:row-start-1 lg:row-span-2 min-w-0 flex flex-col gap-4">
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

            <livewire:components.circulation.table
                :borrowed-books="$checkoutBooks"
                :scanned-member="$scannedMember"
                :selected-accession="$lastScannedBook['accession'] ?? null"
                mode="check-out"
                title="Items to Borrow"
                class="workstation-table lg:col-start-2 lg:row-start-2 min-w-0 min-h-[320px]"
            />

            <livewire:components.dashboard.check-out-summary
                :stats="$stats"
                :scanned-member="$scannedMember"
                :checkout-books="$checkoutBooks"
                :due-date="$dueDate"
            />
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
