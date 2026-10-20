<div class="relative w-full h-auto lg:h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-4 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-y-auto lg:overflow-hidden check-in-workstation">

    <!-- Lock page layout scroll -->
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
            .check-in-workstation,
            .check-in-workstation .lg\:overflow-hidden,
            .check-in-workstation .lg\:h-full,
            .check-in-workstation .lg\:h-\[calc\(100vh-70px\)\],
            .check-in-workstation .lg\:h-\[calc\(100\%-80px\)\] {
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

            <!-- Title Row -->
            <div class="flex flex-col md:flex-row pt-2 justify-between items-start md:items-center">
                <div>
                    <h1 class="text-[1.5rem] font-bold text-[#102B70] tracking-tight leading-tight">
                        Check-In / Return Books
                    </h1>
                    <p class="mt-0.5 text-sm text-[#64748B] font-medium">
                        Scan or enter a member ID or book barcode to process returns.
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
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10 mb-2 flex-1 lg:overflow-hidden h-auto lg:h-[calc(100%-80px)]">

            <!-- Left Section: Column 1 + Column 2 (col-span-9) -->
            <div class="lg:col-span-9 h-auto lg:h-full flex flex-col gap-4 lg:overflow-hidden">

                <!-- Switch Student Confirmation Banner -->
                @if($showConfirmChangeMember)
                    <livewire:components.circulation.top-notification-banner
                        type="warning"
                        title="Unsaved Returns Session"
                        :message="'You have already scanned ' . count($returnedBooks) . ' book(s) in this session. Switching student to <b>' . $pendingStudentName . '</b> will clear this return session. Proceed?'"
                        confirm-action="confirmChangeMember"
                        confirm-label="Yes, Switch Student"
                        cancel-action="cancelChangeMember"
                        cancel-label="Cancel"
                    />
                @endif

                <!-- QR Search / Input Bar (occupies first 2 columns at top) -->
                <div class="relative overflow-hidden shrink-0 bg-white rounded-3xl border border-[#E2E8F0] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <!-- Indeterminate Top Progress Bar -->
                    <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                        <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                    </div>

                    <livewire:components.circulation.qr-search-bar
                        label="Member ID / Code"
                        placeholder="Enter or scan member ID or book code"
                    />

                    <!-- Warnings / Errors alert box -->
                    @if($errorMessage)
                        @php
                            $isWarning = str_contains(strtolower($errorMessage), 'warning');
                        @endphp
                        <div x-data="{ showErr: true }" x-show="showErr"
                             class="mt-3 p-3 border rounded-xl flex items-center justify-between text-xs font-bold transition-all animate-fade-in
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

                <!-- Sub Grid: Column 1 (Camera + Book Scanned) & Column 2 (Table) side-by-side -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 flex-1 lg:overflow-hidden h-auto lg:h-full">

                    <!-- COLUMN 1: Scanner + Scanned Book Details (md:col-span-4) -->
                    <div class="md:col-span-4 flex flex-col gap-4 h-auto lg:h-full lg:overflow-hidden">
                        <!-- Scanner Card -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0">
                            <livewire:components.circulation.live-camera />
                        </div>

                        <!-- Reusable Scanned Book Details Preview Component -->
                        <livewire:components.widgets.circulation-book-preview
                            :book="$lastReturnedBook"
                            mode="check-in"
                            :returned-books="$returnedBooks"
                        />
                    </div>

                    <!-- COLUMN 2: Reusable Table Component (md:col-span-8) -->
                    <livewire:components.circulation.table
                        :borrowed-books="$borrowedBooks"
                        :scanned-member="$scannedMember"
                        :selected-accession="$lastReturnedBook['accession'] ?? null"
                        mode="check-in"
                        title="Borrowed Books on Account"
                        class="md:col-span-8 h-[400px] md:h-full"
                    />

                </div>
            </div>

            
            <div class="lg:col-span-3 h-auto lg:h-full flex flex-col gap-4 lg:overflow-hidden">
            
                <div wire:loading.class="opacity-65 transition-opacity duration-300" class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0 min-h-[150px] flex flex-col">
                    <div class="flex flex-col gap-3 flex-1">
                        @if($scannedMember)
                            @php
                                if (!empty($scannedMember['name'])) {
                                    $words = explode(' ', $scannedMember['name']);
                                    $initials = strtoupper(substr($words[0], 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
                                }
                                $isStatusActive = strtolower($scannedMember['status'] ?? 'active') === 'active';
                            @endphp
                            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl p-4 flex flex-col flex-1 justify-between">
                                <div class="flex items-center gap-3.5">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[#0F172A] font-bold text-base leading-tight truncate max-w-[130px] xl:max-w-none" title="{{ $scannedMember['name'] }}">{{ $scannedMember['name'] }}</h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $isStatusActive ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
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
                                            class="h-8 px-3.5 border border-[#102B70] bg-white text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">                                 
                                        Change Student
                                    </button>

                                    <div x-show="confirmChange" class="flex items-center gap-1.5" style="display: none;">
                                        <span class="text-xs font-bold text-[#EF4444] mr-1 select-none">Sure?</span>
                                        <button type="button"
                                                @click="confirmChange = false"
                                                class="h-8 px-2.5 border border-[#CBD5E1] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A] rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Cancel
                                        </button>
                                        <button type="button"
                                                wire:click="clearMember"
                                                @click="confirmChange = false"
                                                class="h-8 px-3 bg-[#EF4444] hover:bg-[#B91C1C] text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-[#F8FAFC] border border-dashed border-[#CBD5E1] rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-2.5 flex-1 mt-1">
                                <span class="text-sm font-medium text-[#64748B] max-w-[190px] leading-relaxed">Scan or enter member ID number to process returns.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Return Summary Card -->
                <livewire:components.circulation.summary-panel
                    :stats="$stats"
                    :scanned-member="$scannedMember"
                />
            </div>

        </div>
    </div>

    <!-- Search Entity Modal Component -->
    <livewire:components.circulation.search-entity-modal service="check-in" />
</div>
