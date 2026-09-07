<div class="w-full min-h-full flex flex-col font-sans bg-[#F8FAFC] pb-8 px-4 sm:px-6 lg:px-8"
     x-data="{
        cameraOpen: false,
        toggleCamera() {
            this.cameraOpen = !this.cameraOpen;
            if (this.cameraOpen) {
                this.$dispatch('start-camera');
            } else {
                this.$dispatch('collapse-camera');
            }
        }
     }"
     @start-camera.window="cameraOpen = true"
     @collapse-camera.window="cameraOpen = false"
>
    <div class="w-full max-w-[1460px] mx-auto flex flex-col gap-5">

        {{-- Top Navigation & Header --}}
        <div class="flex flex-col gap-2 pt-2 shrink-0">
            @include('livewire.components.circulation.circulation-tab')

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-[#102B70] tracking-tight">
                        Check-In / Return Books
                    </h1>
                    <p class="text-xs text-[#64748B] font-medium mt-0.5">
                        Scan or enter a member ID or book barcode to process returns.
                    </p>
                </div>

                {{-- Camera Drawer Toggle Button (Secondary Action) --}}
                <button type="button"
                        @click="toggleCamera()"
                        class="self-start sm:self-auto inline-flex items-center gap-2 h-10 px-4 bg-white border border-[#E2E8F0] hover:border-[#102B70] text-[#334155] hover:text-[#102B70] rounded-xl text-xs font-bold transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#102B70]/20"
                        :class="cameraOpen ? 'border-[#102B70] bg-[#EFF6FF] text-[#102B70]' : ''"
                        aria-label="Toggle camera scanner drawer">
                    <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span x-text="cameraOpen ? 'Hide Camera' : 'Camera Scanner'">Camera Scanner</span>
                </button>
            </div>
        </div>

        {{-- Switch Student Confirmation Banner --}}
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

        {{-- Primary Barcode Scanner / Search Bar --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-[#E2E8F0] p-4 shadow-xs shrink-0">
            <!-- Indeterminate Loading Progress Bar -->
            <div wire:loading class="absolute top-0 left-0 right-0 h-1 bg-[#EFF6FF] overflow-hidden">
                <div class="h-full w-1/3 bg-[#FCC719] animate-loading-pulse rounded-full"></div>
            </div>

            <livewire:components.circulation.qr-search-bar
                label="Member ID / Code"
                placeholder="Enter or scan member ID or book code"
            />

            <!-- Operational Warning & Error Alerts -->
            @if($errorMessage)
                @php
                    $isWarning = str_contains(strtolower($errorMessage), 'warning');
                @endphp
                <div x-data="{ showErr: true }" x-show="showErr"
                     class="mt-3 p-3.5 border rounded-xl flex items-center justify-between text-xs font-bold transition-all shadow-2xs
                            {{ $isWarning ? 'bg-[#FFFBEB] border-[#FDE68A] text-[#D97706]' : 'bg-[#FEF2F2] border-[#FECACA] text-[#B91C1C]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0 {{ $isWarning ? 'text-[#D97706]' : 'text-[#B91C1C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="leading-normal">{{ $errorMessage }}</span>
                    </div>
                    <button type="button" @click="showErr = false" class="p-1 rounded-lg transition-colors focus:outline-none {{ $isWarning ? 'text-[#D97706] hover:bg-[#FEF3C7]' : 'text-[#B91C1C] hover:bg-[#FEE2E2]' }}" aria-label="Dismiss alert">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        {{-- Workstation Grid (8:4 layout on Desktop >=1280px, fluid protected on 1024-1279px, stacked on <1024px) --}}
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT REGION (8 Cols on xl+, 7 Cols on lg) --}}
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-5 min-w-0">

                {{-- Optional Camera Drawer (Webcam Scanner) --}}
                <div x-show="cameraOpen"
                     x-collapse
                     x-cloak
                     class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col gap-3 transition-all">
                    <div class="flex items-center justify-between pb-2 border-b border-[#E2E8F0]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#102B70]"></span>
                            <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Webcam Scanner Viewport</h3>
                        </div>
                        <button type="button"
                                @click="toggleCamera()"
                                class="text-xs font-bold text-[#64748B] hover:text-[#B91C1C] transition-colors flex items-center gap-1 focus:outline-none"
                                aria-label="Close camera scanner">
                            <span>Close Camera</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <livewire:components.circulation.live-camera />
                </div>

                {{-- Last Scanned / Returned Book Card (Tactile Feedback Strip) --}}
                @if($lastReturnedBook)
                    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-xs flex flex-col gap-3 animate-fade-in">
                        <div class="flex items-center justify-between pb-2.5 border-b border-[#E2E8F0]">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Returned in Session
                                </span>
                                <h3 class="text-xs font-bold text-[#334155] uppercase tracking-wider">Last Scanned Item</h3>
                            </div>

                            <!-- Undo Return Action Button -->
                            <button type="button"
                                    wire:click="undoReturn('{{ $lastReturnedBook['accession'] }}')"
                                    class="h-8 px-3 border border-[#FECACA] bg-white hover:bg-[#FEF2F2] text-[#B91C1C] rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                Undo Return
                            </button>
                        </div>

                        <div class="flex items-start gap-4">
                            <!-- Book Icon Tile -->
                            <div class="w-10 h-14 rounded-lg bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-[#0F172A] leading-snug truncate" title="{{ $lastReturnedBook['title'] }}">
                                    {{ $lastReturnedBook['title'] }}
                                </h4>
                                <p class="text-xs text-[#475569] mt-0.5 truncate">{{ $lastReturnedBook['author'] }}</p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-[#64748B] tabular-nums flex-wrap">
                                    <span>Acc. No: <span class="font-mono text-[#0F172A] font-semibold">{{ $lastReturnedBook['accession'] }}</span></span>
                                    <span class="text-slate-300" aria-hidden="true">&bull;</span>
                                    <span>Borrowed: <span class="font-semibold text-[#334155]">{{ $lastReturnedBook['borrowed_on'] }}</span></span>
                                    <span class="text-slate-300" aria-hidden="true">&bull;</span>
                                    <span>Due: <span class="font-semibold text-[#334155]">{{ $lastReturnedBook['due_date'] }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Canonical Borrowed Books Table --}}
                <livewire:components.circulation.table
                    :borrowed-books="$borrowedBooks"
                    :scanned-member="$scannedMember"
                    mode="check-in"
                    title="Borrowed Books on Account"
                    class="w-full min-h-[380px]"
                />
            </div>

            {{-- RIGHT RAIL (4 Cols on xl+, 5 Cols on lg) --}}
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-5 min-w-0">

                {{-- Member Profile Card (Canonical Component) --}}
                <livewire:components.circulation.member-scan-result
                    :member="$scannedMember"
                />

                {{-- Return Summary Panel (Canonical Component) --}}
                <livewire:components.circulation.summary-panel
                    :stats="$stats"
                    :scanned-member="$scannedMember"
                />
            </div>

        </div>

    </div>

    {{-- Search Entity Modal Component --}}
    <livewire:components.circulation.search-entity-modal service="check-in" />
</div>
