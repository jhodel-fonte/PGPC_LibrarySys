<div class="relative w-full h-auto lg:h-[calc(100vh-70px)] flex flex-col items-center justify-start pt-4 px-3 md:px-6 xl:px-12 pb-5 font-sans bg-[#F8FAFC] overflow-y-auto lg:overflow-hidden check-in-workstation">

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
        .animate-spin-custom {
            animation: spin 1s linear infinite;
            transform-origin: center;
        }
    </style>

    <div class="w-full max-w-[1460px] flex flex-col h-auto lg:h-full lg:overflow-hidden">
        <!-- Header Area (Shrink-0 to keep fixed height) -->
        <div class="w-full flex flex-col mb-4 md:mt-2 relative z-10 shrink-0">

            <!-- Top Row: Back Button & Tabs -->
            @include('livewire.components.circulation.circulation-tab')

            <!-- Title Row -->
            <div class="flex flex-col md:flex-row pt-2 justify-between items-start md:items-center">
                <div>
                    <h1 class="text-[1.35rem] font-bold text-[#102B70] tracking-tight leading-tight">
                        Check-In / Return
                    </h1>
                    <p class="mt-0.5 text-xs text-[#64748B] font-medium">
                        Scan or enter member ID or book barcode to process returns.
                    </p>
                </div>

            </div>
        </div>

        <!-- Main Content Grid (Three Column Workstation Grid Layout) -->
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

                <div class="relative overflow-hidden shrink-0 bg-white rounded-3xl border border-[#E2E8F0] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <!-- Indeterminate Top Progress Bar -->
                    <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                        <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
                    </div>

                    <livewire:components.circulation.qr-search-bar
                        label="Member ID / Code"
                        placeholder="Enter or scan member ID or book code"
                    />

                    @if($errorMessage)
                        <div x-data="{ showErr: true }" x-show="showErr" class="mt-3 p-3 bg-[#FEF2F2] border border-[#FCA5A5] rounded-xl flex items-center justify-between text-[11px] font-bold text-[#B91C1C] transition-all animate-fade-in">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#EF4444] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>{{ $errorMessage }}</span>
                            </div>
                            <button type="button" @click="showErr = false" class="text-[#EF4444] hover:text-[#B91C1C] transition-colors focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Sub Grid: Column 1 & Column 2 side-by-side (flex-1 overflow-hidden) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 flex-1 lg:overflow-hidden h-auto lg:h-full">

                    <!-- COLUMN 1: Scanner + Scanned Book Details (md:col-span-4) -->
                    <div class="md:col-span-4 flex flex-col gap-4 h-auto lg:h-full lg:overflow-hidden">
                        <!-- Scanner Card -->
                        <div class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0">
                            <livewire:components.circulation.live-camera />
                        </div>

                        <!-- Scanned Book Details Card (Flex-1) -->
                        <div wire:loading.class="opacity-65 transition-opacity duration-300" class="bg-white rounded-3xl border border-[#E2E8F0] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] min-h-[320px] lg:flex-1 flex flex-col lg:overflow-hidden">
                            <!-- Header -->
                            <div class="flex justify-between items-center shrink-0 mb-3">
                                <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Scanned Book</h3>
                                {{-- @if($lastReturnedBook)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                        Returned
                                    </span>
                                @endif --}}
                            </div>

                            @if($lastReturnedBook)
                                <!-- Content -->
                                <div class="flex-1 overflow-y-auto flex flex-col gap-3 pr-1">
                                    <div class="flex gap-4">
                                        <!-- Book Cover -->
                                        <div class="w-12 h-16 bg-gradient-to-br from-[#102B70] to-[#F59E0B] rounded shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                            <span class="text-[9px] text-white/50 font-bold uppercase tracking-wider text-center px-1">{{ $lastReturnedBook['code'] }}</span>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <h4 class="font-extrabold text-[14px] leading-tight text-[#0F172A] truncate">{{ $lastReturnedBook['title'] }}</h4>
                                            <span class="text-[11px] text-[#64748B] font-semibold mt-0.5 truncate">{{ $lastReturnedBook['author'] }}</span>
                                            <span class="text-[10px] text-[#94A3B8] font-medium mt-1 truncate">{{ $lastReturnedBook['call_number'] }}</span>
                                        </div>
                                    </div>

                                    <!-- Metadata list -->
                                    <div class="flex flex-col gap-1.5 mt-1.5 text-xs font-semibold">
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[#64748B]">Accession No.</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['accession'] }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[#64748B]">Borrowed On</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['borrowed_on'] }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1.5">
                                            <span class="text-[#64748B]">Due Date</span>
                                            <span class="text-[#0F172A]">{{ $lastReturnedBook['due_date'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Undo button (shrink-0) -->
                                <div class="mt-auto pt-3 shrink-0">
                                    <button wire:click="undoReturn('{{ $lastReturnedBook['accession'] }}')" class="w-full h-10 border border-[#FECACA] hover:bg-[#FEF2F2] text-[#EF4444] rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Undo Return
                                    </button>
                                </div>
                            @else
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-4 gap-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">No Book Scanned</span>
                                    <p class="text-[10px] text-[#94A3B8] max-w-[180px] leading-normal">Scan a book code to mark it as returned in this session.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- COLUMN 2: Borrowed Books (md:col-span-8) -->
                    <livewire:components.circulation.table
                        :borrowed-books="$borrowedBooks"
                        :scanned-member="$scannedMember"
                    />

                </div>
            </div>

            <!-- Right Section: Column 3 (col-span-3 Return Summary Panel) -->
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
                                    <!-- Branded Initials Avatar -->
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

                                <!-- Aligned Action Button -->
                                <div class="flex justify-end" x-data="{ confirmChange: false }">
                                    <!-- Normal State: Change Student -->
                                    <button x-show="!confirmChange"
                                            type="button"
                                            @click="confirmChange = true"
                                            class="h-8 px-3.5 border border-[#102B70] bg-white text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-[11px] font-bold transition-all flex items-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Change Student
                                    </button>

                                    <!-- Confirmation choices (Inline) -->
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
                                <span class="text-[13px] font-bold text-[#334155]">No Student Profile Loaded</span>
                                <p class="text-[10.5px] text-[#64748B] max-w-[190px] leading-relaxed">Scan or enter a member ID number to load their profile and books.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <livewire:components.circulation.summary-panel :stats="$stats" :scanned-member="$scannedMember" />
            </div>

    </div>

    <!-- Search Entity Modal -->
    <livewire:components.circulation.search-entity-modal service="check-in" />
</div>


