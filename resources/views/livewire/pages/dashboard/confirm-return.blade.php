<div class="admin-page-table relative w-full h-auto xl:h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-4 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-y-auto xl:overflow-hidden check-in-workstation"
     x-data="completedModalHandler()"
     @return-completed.window="triggerModal($event.detail)"
     @return-failed.window="triggerErrorModal($event.detail)">
    <style>
        @media (min-width: 1280px) {
            @media (min-height: 800px) {
                main {
                    overflow: hidden !important;
                    display: flex;
                    flex-direction: column;
                    height: calc(100vh - 70px) !important;
                }
            }
        }

        /* Zoom and Low Height Viewport Overrides */
        @media (max-height: 799px) {
            .check-in-workstation,
            .check-in-workstation > div {
                height: auto !important;
                overflow: visible !important;
            }
            main {
                overflow: auto !important;
                height: auto !important;
            }
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

    <div class="w-full max-w-[1460px] flex flex-col h-auto xl:h-full xl:overflow-hidden">

        <!-- Header -->
        <div class="w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">
            <!-- Back Link -->
            <div class="flex items-center gap-2 mb-3">
                <a href="{{ $returnUrl }}" wire:navigate class="inline-flex items-center gap-2 text-[#64748B] hover:text-[#102B70] font-semibold text-[13px] transition-colors group">
                    <svg class="w-4 h-4 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Workstation
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-[#64748B] font-semibold">Review returns</span>
            </div>

            <div class="flex flex-col">
                <h1 class="text-[1.35rem] font-bold text-[#102B70] tracking-tight leading-tight">Review Book Returns</h1>
                <p class="text-xs text-[#64748B] font-medium leading-relaxed mt-0.5">Carefully review the list of scanned books and overdue fine assessments before confirming the transaction.</p>
            </div>
        </div>

        <!-- Inline Error Banner -->
        @if($errorMessage)
            <div class="w-full mb-4 bg-[#FEF2F2] border border-[#FECACA] rounded-2xl p-4 flex items-start justify-between gap-3 text-[#B91C1C] shadow-sm shrink-0">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#EF4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex flex-col">
                        <span class="font-bold text-sm leading-tight">Return Processing Error</span>
                        <span class="text-xs text-[#B91C1C] mt-0.5">{{ $errorMessage }}</span>
                    </div>
                </div>
                <button type="button" wire:click="$set('errorMessage', null)" class="text-[#EF4444] hover:text-[#991B1B] text-lg font-bold leading-none p-1 focus:outline-none">
                    &times;
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 xl:gap-6 flex-1 min-h-0 xl:overflow-hidden mb-2 items-stretch">

            <!-- Left Column: Return Items -->
            <section class="xl:col-span-7 flex min-w-0 flex-col xl:h-full xl:overflow-hidden">
                <div class="xl:flex-1 xl:min-h-0 flex flex-col bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-6 shadow-sm xl:overflow-hidden">
                    <h2 class="mb-4 shrink-0 text-base font-bold uppercase tracking-wider text-[#102B70]">Items to be Returned ({{ count($transactionsData) }})</h2>

                    <div class="divide-y divide-[#E2E8F0] rounded-xl border border-[#E2E8F0] bg-white xl:flex-1 xl:overflow-y-auto">
                        @foreach($transactionsData as $item)
                            <article class="p-4 sm:p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="font-bold leading-snug text-[#0F172A]">{{ $item['title'] }}</h3>
                                        <p class="mt-1 text-sm text-[#64748B]">{{ $item['author'] }}</p>
                                        <p class="mt-0.5 text-xs text-[#94A3B8]">Accession No. {{ $item['accession'] }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs sm:min-w-[230px] sm:text-right">
                                        <div>
                                            <p class="text-[#64748B]">Borrowed</p>
                                            <p class="mt-0.5 font-semibold text-[#334155]">{{ $item['issued_date'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[#64748B]">Due</p>
                                            <p class="mt-0.5 font-semibold text-[#334155]">{{ $item['due_date'] }}</p>
                                            @if($item['due_time'])<p class="text-[#64748B]">{{ $item['due_time'] }}</p>@endif
                                        </div>
                                        <div class="col-span-2 sm:justify-self-end">
                                            @if($item['fine_amount'] > 0)
                                                <span class="inline-flex flex-wrap items-center gap-x-2 rounded-md border border-[#FECACA] bg-[#FEF2F2] px-2 py-1 font-bold text-[#B91C1C]">
                                                    {{ round($item['overdue_days']) }} {{ Str::plural('day', round($item['overdue_days'])) }} overdue
                                                    <span>₱{{ number_format($item['fine_amount'], 2) }}</span>
                                                </span>
                                            @else
                                                <span class="font-semibold text-[#15803D]">On time</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-4 flex shrink-0 items-center gap-2.5 rounded-xl bg-[#F0FDF4] p-4 text-[#15803D]">
                        <svg class="h-5 w-5 shrink-0 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-semibold">All items are ready to be returned.</span>
                    </div>
                </div>
            </section>

            <!-- Right Column: Student Info, Return Summary & Actions -->
            <div class="xl:col-span-5 flex flex-col min-w-0 xl:h-full xl:overflow-hidden">
                <div class="xl:flex-1 flex flex-col xl:justify-between xl:overflow-y-auto bg-white border border-[#E2E8F0] rounded-3xl p-4 sm:p-6 shadow-sm custom-scrollbar">
                    <div class="flex flex-col gap-6">

                        <!-- Student Information Section -->
                        <div>
                            @if($student)
                                @php
                                    $initials = '';
                                    $fullName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                                    if (!empty($fullName)) {
                                        $words = explode(' ', $fullName);
                                        $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
                                    }
                                    $isStatusActive = strtolower($student->libraryStatus->status ?? 'active') === 'active';
                                @endphp
                                <div class="flex flex-col gap-5">
                                    <!-- Avatar, Name & School ID / Active Badge -->
                                    <div class="flex items-center gap-3.5 pb-4">
                                        <!-- Branded Initials Avatar -->
                                        <div class="w-12 h-12 rounded-full bg-[#102B70] text-white flex items-center justify-center shrink-0 shadow-sm text-sm font-bold tracking-wider select-none">
                                            {{ $initials ?: 'S' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-[17px] text-[#0F172A] leading-tight truncate" title="{{ $fullName }}">{{ $fullName }}</h4>
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                <span class="text-sm font-semibold text-[#64748B]">{{ $student->school_id_number }}</span>
                                                <span class="text-slate-300 text-[10px] select-none">&bull;</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9.5px] font-bold border {{ $isStatusActive ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
                                                    {{ $student->libraryStatus->status ?? 'Active' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Compact 2-Column Student Details Grid -->
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-3.5">
                                        <!-- Program / Course -->
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Program / Course</span>
                                            <span class="text-[13.5px] font-semibold text-[#0F172A] truncate" title="{{ $student->program }}">{{ $student->program }}</span>
                                        </div>
                                        <!-- Year Level -->
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Year Level</span>
                                            <span class="text-[13.5px] font-semibold text-[#0F172A]">{{ $student->year_level }}</span>
                                        </div>
                                        <!-- Email Address -->
                                        <div class="flex flex-col gap-0.5 col-span-2">
                                            <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Email Address</span>
                                            <span class="text-[13.5px] font-semibold text-[#0F172A] truncate" title="{{ $student->account->email ?? 'N/A' }}">{{ $student->account->email ?? 'N/A' }}</span>
                                        </div>
                                        <!-- Contact Number -->
                                        <div class="flex flex-col gap-0.5 col-span-2">
                                            <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Contact Number</span>
                                            <span class="text-[13.5px] font-semibold text-[#0F172A] truncate" title="{{ $student->contact_num ?? 'N/A' }}">{{ $student->contact_num ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr class="border-[#E2E8F0]">

                        <!-- Return Summary -->
                        <div>
                            <span class="block text-base font-bold text-[#102B70] uppercase tracking-wider mb-4">Return Summary</span>

                            @php
                                $totalBooks = count($transactionsData);
                            @endphp

                            <div class="flex justify-between items-center">
                                <span class="text-[#64748B] font-bold text-sm">Scanned Books</span>
                                <span class="text-[#0F172A] font-bold text-base">{{ $totalBooks }} {{ Str::plural('book', $totalBooks) }}</span>
                            </div>

                            @if($totalFine > 0)
                                <!-- Consolidated fine warning -->
                                <div class="mt-4 bg-[#FEF2F2] rounded-xl p-4 flex gap-3 items-start border border-[#FCA5A5]">
                                    <svg class="w-5 h-5 text-[#EF4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="flex flex-col gap-1 w-full">
                                        <span class="text-sm font-extrabold text-[#B91C1C]">Overdue Fine: ₱{{ number_format($totalFine, 2) }}</span>
                                        <span class="text-xs font-bold text-[#B91C1C] leading-relaxed">This amount will be added to the student's pending balance.</span>
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-[#64748B] font-bold text-sm">Overdue Fines</span>
                                    <span class="font-extrabold text-base text-[#10B981]">₱0.00</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 border-t border-[#E2E8F0] pt-5 flex flex-col gap-3">
                        <button type="button"
                                wire:click="confirmReturn"
                                wire:loading.attr="disabled"
                                wire:target="confirmReturn"
                                class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2.5 shadow-sm hover:shadow-md outline-none disabled:opacity-65 disabled:cursor-not-allowed group">
                            <span wire:loading.remove wire:target="confirmReturn" class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs uppercase tracking-wider">Confirm Return</span>
                            </span>
                            <span wire:loading.flex wire:target="confirmReturn" class="text-xs uppercase tracking-wider items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Completing Return...
                            </span>
                        </button>

                        <a href="{{ $returnUrl }}" wire:navigate
                           class="w-full py-2 hover:text-[#102B70] text-[#64748B] text-xs font-bold transition-all flex items-center justify-center gap-1.5 focus:outline-none hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel & Go Back
                        </a>

                        <span class="block text-center text-[10px] text-[#94A3B8] font-medium mt-0.5">This action will update records in the system.</span>
                    </div>
                </div>
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

         <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl flex flex-col items-center text-center gap-5 border border-slate-100 relative"
              x-show="isOpen"
              x-transition:enter="transition ease-out duration-300 transform"
              x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave="transition ease-in duration-200 transform"
              x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

              <!-- Success Icon -->
              <div class="w-16 h-16 rounded-full bg-emerald-50 border-2 border-emerald-500 text-emerald-500 flex items-center justify-center shrink-0 shadow-md">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                  </svg>
              </div>

              <!-- Content -->
              <div class="flex flex-col gap-1.5">
                  <h3 class="text-xl font-bold text-[#102B70]">Books Returned Successfully</h3>
                  <p class="text-xs text-[#64748B] font-semibold leading-relaxed px-4">
                      All books have been marked as returned and inventory status updated.
                  </p>
              </div>

              <!-- Circular Progress and Timer -->
              <div class="flex flex-col items-center justify-center relative mt-2 gap-2">
                  <div class="relative w-20 h-20 flex items-center justify-center">
                      <svg class="w-20 h-20 transform -rotate-90">
                          <circle cx="40" cy="40" r="34" class="stroke-slate-100" stroke-width="4.5" fill="transparent" />
                          <circle cx="40" cy="40" r="34"
                                  class="stroke-[#102B70]"
                                  stroke-width="4.5"
                                  fill="transparent"
                                  stroke-dasharray="214"
                                  :stroke-dashoffset="dashOffset"
                                  stroke-linecap="round"
                                  style="transition: stroke-dashoffset 0.1s linear;" />
                      </svg>
                      <span class="absolute text-lg font-extrabold text-[#102B70]" x-text="countdown">3</span>
                  </div>
                  <span class="text-[11px] text-[#94A3B8] font-bold uppercase tracking-widest mt-1">Redirecting to workstation...</span>
              </div>

              <!-- Instant Redirect Button -->
              <button type="button"
                      @click="navigateNow()"
                      class="w-full h-11 bg-[#102B70] hover:bg-[#0B225E] text-white font-bold rounded-2xl text-xs uppercase tracking-wider transition-colors focus:outline-none shadow-sm flex items-center justify-center gap-2">
                  <span>Return to Workstation Now</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                  </svg>
              </button>
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

         <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl flex flex-col items-center text-center gap-5 border border-slate-100 relative"
              x-show="isErrorOpen"
              x-transition:enter="transition ease-out duration-300 transform"
              x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave="transition ease-in duration-200 transform"
              x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

              <!-- Error Icon -->
              <div class="w-16 h-16 rounded-full bg-red-50 border-2 border-red-500 text-red-500 flex items-center justify-center shrink-0 shadow-md">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                  </svg>
              </div>

              <!-- Content -->
              <div class="flex flex-col gap-1.5">
                  <h3 class="text-xl font-bold text-[#B91C1C]">Return Processing Failed</h3>
                  <p class="text-xs text-[#64748B] font-semibold leading-relaxed px-4 break-words" x-text="errorMessage">
                      An error occurred during submission. Please check conditions and try again.
                  </p>
              </div>

              <button type="button"
                      @click="isErrorOpen = false"
                      class="w-full h-11 bg-slate-100 hover:bg-slate-200 text-[#64748B] font-bold rounded-2xl text-xs uppercase tracking-wider transition-colors focus:outline-none">
                  Dismiss
              </button>
         </div>
    </div>

    <script>
        function completedModalHandler() {
            return {
                isOpen: false,
                isErrorOpen: false,
                errorMessage: '',
                countdown: 3,
                timeRemaining: 3.0,
                dashOffset: 0,
                redirectUrl: '{{ $returnUrl }}',
                timer: null,

                triggerModal(detail) {
                    this.redirectUrl = detail.redirectUrl || '{{ $returnUrl }}';
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
                            this.navigateNow();
                        }
                    }, intervalMs);
                },

                navigateNow() {
                    if (this.timer) clearInterval(this.timer);
                    if (window.Livewire) {
                        window.Livewire.navigate(this.redirectUrl);
                    } else {
                        window.location.href = this.redirectUrl;
                    }
                },

                triggerErrorModal(detail) {
                    this.errorMessage = detail.message || 'An unexpected error occurred during the return process.';
                    this.isErrorOpen = true;
                }
            }
        }

        // Global network interceptor for client/server connection dropouts (supports wire:navigate)
        function registerLivewireHooks() {
            const hookCallback = ({ fail }) => {
                fail(({ status, content, preventDefault }) => {
                    window.dispatchEvent(new CustomEvent('return-failed', {
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
