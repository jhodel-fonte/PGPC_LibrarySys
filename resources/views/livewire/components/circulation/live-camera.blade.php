<div
    wire:ignore.self
    x-data="liveCameraScanner($wire, @js($autoStart))"
    @start-camera.window="
        isCollapsed = false;
        startScanner();
    "
    x-on:stop-camera.window="
        stopScanner();
    "
    @scan-lookup-completed.window="finishScan($event.detail)"
    @collapse-camera.window="isCollapsed = true"
    @expand-camera.window="isCollapsed = false"
    class="flex flex-col gap-3">

    <!-- Scanner Header -->
    <div class="flex items-center justify-between shrink-0">
        <div class="flex items-center gap-1.5">
            <!-- Collapse Toggle Caret Button -->
            <button type="button" @click="isCollapsed = !isCollapsed" class="text-[#102B70] hover:bg-[#F8FAFC] p-1.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#102B70]/20" aria-label="Toggle scanner viewport" title="Toggle Scanner Viewport">
                <svg :class="isCollapsed ? '-rotate-90' : ''" class="w-3.5 h-3.5 transform transition-transform duration-200 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Scanner</h3>
        </div>

        <div class="flex items-center gap-2">
            <!-- Dynamic Status Badge inside Header -->
            <span class="text-xs font-semibold uppercase tracking-wider" :class="permissionError ? 'text-[#B91C1C]' : (isPaused ? 'text-[#64748B]' : 'text-[#15803D]')" x-text="permissionError ? 'Unavailable' : (isPaused ? 'Paused' : (isRequesting ? 'Starting' : 'Scanning'))"></span>

            <!-- Pause/Resume Camera Action -->
            <button type="button" x-show="!permissionError" @click="toggleScanner()" class="text-xs text-[#475569] hover:text-[#102B70] font-semibold transition-colors flex items-center gap-1 border-l border-[#E2E8F0] pl-2.5 focus:outline-none focus:underline">
                <svg class="w-3.5 h-3.5 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path></svg>
                <span x-text="isPaused ? 'Resume' : 'Pause'">Pause</span>
            </button>
        </div>
    </div>

    <!-- Viewfinder Area & Sub-Text (Collapsible container) -->
    <div x-show="!isCollapsed" x-transition class="flex flex-col gap-3">

        <!-- Viewfinder Outer Box (wire:ignore remains crucial here) -->
        <div wire:ignore class="workstation-camera-view relative mx-auto w-full max-w-[320px] aspect-square bg-[#0F172A] rounded-2xl overflow-hidden flex items-center justify-center border border-[#334155] shadow-inner shrink-0">

            <!-- Camera Permission Blocked Overlay -->
            <div x-show="permissionError" class="absolute inset-0 z-30 bg-[#0F172A] flex flex-col items-center justify-center p-4 text-center gap-2">
                <div class="px-2">
                    <template x-if="!isSecure">
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">HTTPS Connection Required</h4>
                            <p class="text-xs text-slate-300 mt-1 leading-normal max-w-[220px] mx-auto">Webcam scanning requires a secure HTTPS connection or localhost in modern browsers.</p>
                        </div>
                    </template>
                    <template x-if="isSecure">
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider" x-text="cameraErrorTitle">Camera Unavailable</h4>
                            <p class="text-xs text-slate-300 mt-1 leading-normal max-w-[260px] mx-auto" x-text="cameraErrorMsg"></p>
                        </div>
                    </template>
                </div>

                <button type="button" x-show="isSecure" @click="startScanner()" :disabled="isRequesting" class="h-8 px-4 mt-1 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-semibold transition-all shadow-xs flex items-center justify-center gap-1.5 disabled:opacity-50" x-cloak>
                    <svg x-show="isRequesting" class="w-3.5 h-3.5 text-white animate-spin" fill="none" viewBox="0 0 24 24" x-cloak aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isRequesting ? 'Retrying...' : 'Retry Connection'">Retry Connection</span>
                </button>
            </div>

            <!-- Camera Paused Overlay -->
            <div x-show="isPaused" class="absolute inset-0 z-30 bg-[#0F172A]/95 flex flex-col items-center justify-center p-4 text-center gap-2" x-cloak>
                <div class="flex items-center justify-center rounded-full w-9 h-9 bg-slate-800 text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="px-2">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Camera Paused</h4>
                    <p class="text-xs text-slate-300 mt-1 leading-normal max-w-[220px] mx-auto">Click "Resume" or click the search bar scanner icon to resume video scanning.</p>
                </div>
                <button type="button" @click="toggleScanner()" class="h-8 px-4 mt-1 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-semibold transition-all shadow-xs flex items-center justify-center gap-1.5">
                    Resume Scanner
                </button>
            </div>

            <!-- Viewfinder Corner Alignment Brackets (Institutional Restrained) -->
            <div :class="hasActiveScan ? 'border-[#FCC719]' : 'border-white/60'" class="absolute top-3.5 left-3.5 w-5 h-5 border-t-2 border-l-2 rounded-tl z-20 pointer-events-none transition-colors duration-200"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719]' : 'border-white/60'" class="absolute top-3.5 right-3.5 w-5 h-5 border-t-2 border-r-2 rounded-tr z-20 pointer-events-none transition-colors duration-200"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719]' : 'border-white/60'" class="absolute bottom-3.5 left-3.5 w-5 h-5 border-b-2 border-l-2 rounded-bl z-20 pointer-events-none transition-colors duration-200"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719]' : 'border-white/60'" class="absolute bottom-3.5 right-3.5 w-5 h-5 border-b-2 border-r-2 rounded-br z-20 pointer-events-none transition-colors duration-200"></div>

            <!-- Solid Detection Target Frame (Clean institutional detection, no arcade neon glow) -->
            <div x-show="hasActiveScan" class="absolute z-20 pointer-events-none w-28 h-28 border-2 border-[#FCC719] rounded-xl flex items-center justify-center">
            </div>

            <!-- Floating Subtitle Status Pill (Accessible aria-live) -->
            <div aria-live="polite" class="absolute bottom-2 left-1/2 transform -translate-x-1/2 z-20 py-1.5 px-4 bg-[#071943]/90 backdrop-blur-xs rounded-lg border border-white/10 flex items-center justify-center text-center max-w-[92%] transition-all duration-200">
                <p x-text="scanMessage" :class="scanSuccess ? 'text-[#BBF7D0]' : 'text-white'" class="text-xs leading-normal"></p>
            </div>

            <!-- Target Div element for HTML5 QR Code Scanner -->
            <div id="reader-viewfinder" class="absolute z-10 w-full h-full overflow-hidden bg-[#0F172A]"></div>
        </div>

    </div>
    <button type="button" x-show="!isPaused && !permissionError && lastCode && (!hasActiveScan || scanTimedOut)" @click="retryScan()" class="min-h-11 self-center rounded-lg px-3 text-sm font-semibold text-[#102B70] underline" x-cloak>Scan this code again</button>

    <!-- Styles for html5-qrcode generated stream video and canvas nodes -->
    <style>
        #reader-viewfinder {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            background-color: #0F172A !important;
        }
        #reader-viewfinder video {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            transform: scaleX(-1);
            background-color: #0F172A !important;
        }
        #reader-viewfinder canvas {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 12 !important;
            pointer-events: none !important;
        }
    </style>
</div>
