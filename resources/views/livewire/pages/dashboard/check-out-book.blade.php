<div class="relative w-full h-auto lg:h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-4 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-y-auto lg:overflow-hidden check-out-workstation"
     x-data="completedModalHandler()"
     @checkout-completed.window="triggerModal($event.detail)"
     @checkout-failed.window="triggerErrorModal($event.detail)">

    <!-- Lock page layout scroll to prevent entire page scrolling on desktop when screen size permits -->
    <style>
        @media (min-width: 1024px) {
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
            .check-out-workstation,
            .check-out-workstation .lg\:overflow-hidden,
            .check-out-workstation .lg\:h-full,
            .check-out-workstation .lg\:h-\[calc\(100vh-70px\)\],
            .check-out-workstation .lg\:h-\[calc\(100\%-80px\)\] {
                height: auto !important;
                overflow: visible !important;
            }
            main {
                overflow: auto !important;
                height: auto !important;
            }
        }

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

    <div class="w-full max-w-[1460px] flex flex-col h-auto lg:h-full lg:overflow-hidden">
        <!-- Header Area -->
        <div class="w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">
            <!-- Top Row: Back Link -->
            @include('livewire.components.circulation.circulation-tab')

            <!-- Title Row -->
            <div class="flex flex-col md:flex-row pt-2 justify-between items-start md:items-center">
                <div>
                    <h1 class="text-[1.5rem] font-bold text-[#102B70] tracking-tight leading-tight">
                        Check-Out / Issue Books
                    </h1>
                    <p class="mt-0.5 text-xs text-[#64748B] font-medium">
                        Scan or enter member ID first, then scan book barcodes to perform checkout.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10 mb-2 flex-1 lg:overflow-hidden h-auto lg:h-[calc(100%-80px)]">

            <!-- Left Section: Column 1 + Column 2 (col-span-9) -->
            <div class="lg:col-span-9 h-auto lg:h-full flex flex-col gap-4 lg:overflow-hidden">

                <!-- Switch Student Confirmation Banner -->
                @if($showConfirmChangeMember)
                    <livewire:components.circulation.top-notification-banner
                        type="warning"
                        title="Unsaved Checkout Session"
                        :message="'You have already queued ' . count($checkoutBooks) . ' book(s) in this session. Switching student to <b>' . $pendingStudentName . '</b> will clear this checkout list. Proceed?'"
                        confirm-action="confirmChangeMember"
                        confirm-label="Yes, Switch Student"
                        cancel-action="cancelChangeMember"
                        cancel-label="Cancel"
                    />
                @endif

                <div class="relative overflow-hidden shrink-0 bg-white rounded-3xl border border-[#E2E8F0] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <!-- Indeterminate Top Progress Bar -->
                    <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                        <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                    </div>

                    <livewire:components.circulation.qr-search-bar
                        label="Member ID / Barcode"
                        placeholder="Enter or scan member ID or book barcode"
                    />

                    <!-- Warnings / Errors alert box -->
                    @if($errorMessage)
                        @php
                            $isWarning = str_contains(strtolower($errorMessage), 'warning');
                        @endphp
                        <div x-data="{ showErr: true }" x-show="showErr"
                             class="mt-3 p-3 border rounded-xl flex items-center justify-between text-[11px] font-bold transition-all animate-fade-in
                                    {{ $isWarning ? 'bg-[#FFF9E6] border-[#FFEAA7] text-[#D97706]' : 'bg-[#FEF2F2] border-[#FCA5A5] text-[#B91C1C]' }}">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 {{ $isWarning ? 'text-[#FCC719]' : 'text-[#EF4444]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>{{ $errorMessage }}</span>
                            </div>
                            <button type="button" @click="showErr = false" class="transition-colors focus:outline-none {{ $isWarning ? 'text-[#D97706] hover:text-[#B45309]' : 'text-[#EF4444] hover:text-[#B91C1C]' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Sub Grid: Camera Scanner & Reusable Table side-by-side -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 flex-1 lg:overflow-hidden h-auto lg:h-full">

                    <!-- COLUMN 1: Scanner + Scanned Book Details (md:col-span-4) -->
                    <div class="md:col-span-4 flex flex-col gap-4 h-auto lg:h-full lg:overflow-hidden">
                        <!-- Scanner Card -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0">
                            <livewire:components.circulation.live-camera />
                        </div>

                        <!-- Scanned Book Details Card (Empty representation in Checkout mode) -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] min-h-[180px] lg:flex-1 flex flex-col items-center justify-center text-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-[#EFF6FF] text-[#102B70] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <span class="text-[12px] font-bold text-[#334155] uppercase tracking-wider">Checkout Guide</span>
                            <p class="text-[10px] text-[#64748B] max-w-[180px] leading-relaxed">
                                Students may checkout up to 3 books total. Overdue records and pending fines block check-out.
                            </p>
                        </div>
                    </div>

                    <!-- COLUMN 2: Reusable Table Component (md:col-span-8) -->
                    <livewire:components.circulation.table
                        :borrowed-books="$checkoutBooks"
                        :scanned-member="$scannedMember"
                        mode="check-out"
                        title="Books to Check Out"
                        class="md:col-span-8 h-[400px] md:h-full"
                    />

                </div>
            </div>

            <!-- Right Section: Column 3 (col-span-3 Student Card + Checkout Summary Panel) -->
            <div class="lg:col-span-3 h-auto lg:h-full flex flex-col gap-4 lg:overflow-hidden">
                <!-- Student Card -->
                <div wire:loading.class="opacity-65 transition-opacity duration-300" class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0 min-h-[220px] flex flex-col">
                    <div class="flex flex-col gap-3 flex-1">
                        <span class="block text-[11px] font-bold text-[#64748B] uppercase tracking-widest">Student</span>

                        @if($scannedMember)
                            @php
                                $initials = '';
                                if (!empty($scannedMember['name'])) {
                                    $words = explode(' ', $scannedMember['name']);
                                    $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
                                }
                                $isStatusActive = strtolower($scannedMember['status'] ?? 'active') === 'active';
                            @endphp
                            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl p-4 flex flex-col gap-4 shadow-inner flex-1 justify-between">
                                <div class="flex items-center gap-3.5">
                                    <!-- Avatar -->
                                    <div class="w-12 h-12 rounded-full bg-[#102B70] text-[#FFFFFF] border-2 flex items-center justify-center shrink-0 shadow-sm text-sm font-bold tracking-wider select-none">
                                        {{ $initials ?: 'S' }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[#0F172A] font-bold text-[15px] leading-tight truncate max-w-[130px] xl:max-w-none" title="{{ $scannedMember['name'] }}">{{ $scannedMember['name'] }}</h3>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[8.5px] font-bold border {{ $isStatusActive ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
                                                {{ $scannedMember['status'] }}
                                            </span>
                                        </div>
                                        <div class="text-[12px] text-[#64748B] font-semibold mt-0.5">
                                            <span>{{ $scannedMember['school_id'] }}</span>
                                            <span class="mx-1 text-slate-300">&bull;</span>
                                            <span class="text-[11.5px] font-medium">{{ $scannedMember['course'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Change Student Actions -->
                                <div class="flex justify-end" x-data="{ confirmChange: false }">
                                    <button x-show="!confirmChange"
                                            type="button"
                                            @click="confirmChange = true"
                                            class="h-8 px-3.5 border border-[#102B70] bg-white text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-[11px] font-bold transition-all flex items-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Change Student
                                    </button>

                                    <div x-show="confirmChange" class="flex items-center gap-1.5" style="display: none;">
                                        <span class="text-[10px] font-bold text-[#EF4444] mr-1 select-none">Sure?</span>
                                        <button type="button"
                                                @click="confirmChange = false"
                                                class="h-8 px-2.5 border border-[#CBD5E1] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A] rounded-xl text-[10px] font-bold transition-all shadow-sm">
                                            Cancel
                                        </button>
                                        <button type="button"
                                                wire:click="clearMember"
                                                @click="confirmChange = false"
                                                class="h-8 px-3 bg-[#EF4444] hover:bg-[#B91C1C] text-white rounded-xl text-[10px] font-bold transition-all shadow-sm">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-[#F8FAFC] border border-dashed border-[#CBD5E1] rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-2.5 flex-1 mt-1 hover:border-[#102B70] transition-colors duration-200">
                                <div class="w-12 h-12 rounded-full bg-[#EFF6FF] border border-[#DBEAFE] flex items-center justify-center text-[#102B70] shrink-0 shadow-sm">
                                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 .667 4 2V17H5v-1c0-1.333 2.667-2 4-2z"></path>
                                    </svg>
                                </div>
                                <span class="text-[13px] font-bold text-[#334155]">No Student Profile</span>
                                <p class="text-[10.5px] text-[#64748B] max-w-[190px] leading-relaxed">Scan or enter member ID number to check out.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Checkout Summary Card -->
                <div class="bg-white rounded-3xl border border-[#E2E8F0] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex-1 flex flex-col h-auto lg:h-full lg:overflow-hidden">
                    <div class="flex flex-col gap-3 shrink-0">
                        <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Checkout Summary</h3>

                        <div class="flex flex-col gap-0.5 text-sm font-semibold">
                            <!-- Books Count Row -->
                            <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9]">
                                <span class="text-[#64748B]">Total Books to Issue</span>
                                <span class="text-[#0F172A] font-bold">{{ $stats['total'] }}</span>
                            </div>

                            <!-- Remaining Slots Row -->
                            @if($scannedMember)
                                <div class="flex justify-between items-center py-2.5 border-b border-[#F1F5F9] {{ $stats['borrow_limit_reached'] ? 'bg-[#FEF2F2] px-2 -mx-2 rounded-md' : '' }}">
                                    <span class="{{ $stats['borrow_limit_reached'] ? 'text-[#B91C1C]' : 'text-[#64748B]' }}">Remaining Slots</span>
                                    <span class="{{ $stats['borrow_limit_reached'] ? 'text-[#EF4444]' : 'text-[#102B70]' }} font-bold">
                                        {{ $stats['remaining_slots'] }} / 3
                                    </span>
                                </div>

                                <!-- Warnings indicator inside Summary Panel -->
                                @if($stats['overdue_count'] > 0)
                                    <div class="flex justify-between items-center py-2.5 border-b border-[#FEF2F2] bg-[#FEF2F2] px-2 -mx-2 rounded-md transition-all">
                                        <span class="text-[#B91C1C] font-bold">Overdue Books</span>
                                        <span class="text-[#EF4444] font-extrabold">{{ $stats['overdue_count'] }}</span>
                                    </div>
                                @endif

                                @if($stats['unpaid_fines'] > 0)
                                    <div class="flex justify-between items-center py-2.5 border-b border-[#FEF2F2] bg-[#FEF2F2] px-2 -mx-2 rounded-md transition-all">
                                        <span class="text-[#B91C1C] font-bold">Outstanding Fines</span>
                                        <span class="text-[#EF4444] font-extrabold">₱{{ number_format($stats['unpaid_fines'], 2) }}</span>
                                    </div>
                                @endif
                            @endif

                            <!-- Due Date Row -->
                            <div class="flex justify-between items-center py-2.5">
                                <span class="text-[#64748B]">Due Date</span>
                                <div class="flex items-center gap-1.5 text-[#0F172A] font-bold">
                                    <svg class="w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $stats['due_date'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 min-h-[20px]"></div>

                    <!-- Checkout Button block -->
                    <div class="shrink-0 pt-3 border-t border-[#F1F5F9]">
                        @php
                            $cannotCheckout = !$scannedMember || empty($checkoutBooks) ||
                                              ($stats['overdue_count'] ?? 0) > 0 ||
                                              ($stats['unpaid_fines'] ?? 0) > 0 ||
                                              (strtolower($scannedMember['status'] ?? 'active') !== 'active');
                        @endphp
                        <button type="button"
                                wire:click="confirmCheckout"
                                wire:loading.attr="disabled"
                                wire:target="confirmCheckout"
                                @if($cannotCheckout) disabled @endif
                                class="w-full h-12 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl font-bold flex items-center justify-between px-5 transition-all shadow-sm group disabled:opacity-65 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="confirmCheckout" class="text-xs uppercase tracking-wider">Confirm Check-Out</span>
                            <span wire:loading.flex wire:target="confirmCheckout" class="text-xs uppercase tracking-wider items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Completing...
                            </span>
                            <svg wire:loading.remove wire:target="confirmCheckout" class="w-4 h-4 text-white transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
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
                  <h3 class="text-xl font-bold text-[#102B70]">Books Issued Successfully</h3>
                  <p class="text-xs text-[#64748B] font-semibold leading-relaxed px-4">
                      All books have been successfully checked out and due date records logged.
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
                  <span class="text-[11px] text-[#94A3B8] font-bold uppercase tracking-widest mt-1">Redirecting to start...</span>
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
                  <h3 class="text-xl font-bold text-[#B91C1C]">Checkout Failed</h3>
                  <p class="text-xs text-[#64748B] font-semibold leading-relaxed px-4" x-text="errorMessage">
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
