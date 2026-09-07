<div x-data="{
    scanner: null,
    isPaused: false,
    isCollapsed: false,
    hasPermission: true,
    permissionError: false,
    isRequesting: false,
    isSecure: window.isSecureContext,
    cameraErrorMsg: 'Camera permission denied or blocked',
    hasActiveScan: false,
    scanTimeout: null,
    resetTimeout: null,
    inactivityTimeout: null,
    inactivityDelay: 60000, // 60 seconds before pausing automatically

    state: $wire.entangle('state'),
    message: $wire.entangle('message'),
    detail: $wire.entangle('detail'),

    async init() {
        // Use active polling to reliably detect Html5Qrcode module regardless of SPA navigation/load timings
        if (window.Html5Qrcode) {
            this.initScanner();
        } else {
            const interval = setInterval(() => {
                if (window.Html5Qrcode) {
                    clearInterval(interval);
                    this.initScanner();
                }
            }, 50);
            // Clear interval after 6 seconds to prevent infinite execution if script fails
            setTimeout(() => clearInterval(interval), 6000);
        }
    },
    initScanner() {
        try {
            // Instantiate the Html5Qrcode with the target div ID
            this.scanner = new Html5Qrcode('reader-viewfinder');
            this.startScanner();
        } catch (e) {
            console.error('Failed to initialize Html5Qrcode:', e);
            this.hasPermission = false;
            this.permissionError = true;
            this.cameraErrorMsg = e.message || 'Failed to initialize scanner';
            $wire.setScanState('error', this.cameraErrorMsg);
        }
    },
    startScanner() {
        if (!this.scanner) {
            this.initScanner();
            return;
        }
        this.isRequesting = true;

        // OPTIMIZATION: High FPS and native barcode detection for maximum speed at standard resolutions
        const config = {
            fps: 25,
            aspectRatio: 1.0,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        // Query available camera devices to select a robust source without constraint errors
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length > 0) {
                // Find back/environment camera if possible, otherwise use the first webcam available
                let cameraId = devices[0].id;
                const backCamera = devices.find(device =>
                    device.label.toLowerCase().includes('back') ||
                    device.label.toLowerCase().includes('environment') ||
                    device.label.toLowerCase().includes('rear')
                );
                if (backCamera) {
                    cameraId = backCamera.id;
                }

                return this.scanner.start(
                    cameraId, // Using standard camera ID without forcing HD constraints
                    config,
                    (decodedText, decodedResult) => {
                        // The !this.hasActiveScan lock acts as a debounce to prevent flooding your Livewire backend
                        if (decodedText && !this.hasActiveScan) {
                            this.hasActiveScan = true; // Engage visual and request lock

                            // Send code to Livewire backend controller exactly once
                            $wire.handleScan(decodedText);

                            // Reset the inactivity timer on active scanner activity
                            this.startInactivityTimer();

                            // Clear prior timeouts
                            clearTimeout(this.scanTimeout);
                            clearTimeout(this.resetTimeout);

                            // Keep tracking outline box and corners visible for 1.5 seconds
                            this.scanTimeout = setTimeout(() => {
                                this.hasActiveScan = false;
                            }, 1500);

                            // Reset descriptive status back to scanning after 2.5 seconds
                            this.resetTimeout = setTimeout(() => {
                                if (!this.isPaused && !this.permissionError) {
                                    $wire.setScanState('scanning', 'Searching for barcode or QR code...');
                                }
                            }, 2500);
                        }
                    },
                    (errorMessage) => {
                        // Suppress constant frame decode failures to keep console clean
                    }
                );
            } else {
                throw new Error('No camera devices detected.');
            }
        }).then(() => {
            this.hasPermission = true;
            this.permissionError = false;
            this.isPaused = false;
            this.isRequesting = false;
            $wire.setScanState('scanning', 'Searching for barcode or QR code...');

            // Start inactivity auto-pause timer
            this.startInactivityTimer();
        }).catch(err => {
            console.warn('Failed to start html5-qrcode feed:', err);
            this.hasPermission = false;
            this.permissionError = true;
            this.isRequesting = false;
            this.cameraErrorMsg = err.message || err.toString();
            $wire.setScanState('error', this.cameraErrorMsg);
        });
    },
    toggleScanner() {
        if (!this.scanner) return;
        if (this.isPaused) {
            this.startScanner();
        } else {
            this.scanner.stop().then(() => {
                this.isPaused = true;
                this.clearInactivityTimer();
                $wire.setScanState('ready', 'Camera is currently paused');
            }).catch(err => {
                console.warn('Failed to stop html5-qrcode scanner:', err);
            });
        }
    },
    destroy() {
        if (this.scanner && this.scanner.isScanning) {
            this.scanner.stop().catch(err => console.warn(err));
        }
        clearTimeout(this.scanTimeout);
        clearTimeout(this.resetTimeout);
        this.clearInactivityTimer();
    },
    startInactivityTimer() {
        this.clearInactivityTimer();
        this.inactivityTimeout = setTimeout(() => {
            this.handleInactivityPause();
        }, this.inactivityDelay);
    },
    clearInactivityTimer() {
        if (this.inactivityTimeout) {
            clearTimeout(this.inactivityTimeout);
            this.inactivityTimeout = null;
        }
    },
    handleInactivityPause() {
        if (this.scanner && this.scanner.isScanning && !this.isPaused) {
            this.scanner.stop().then(() => {
                this.isPaused = true;
                $wire.setScanState('ready', 'Camera paused due to inactivity');
            }).catch(err => {
                console.warn('Failed to stop scanner on inactivity:', err);
            });
        }
    }
}"
@start-camera.window="
    isCollapsed = false;
    if (isPaused) {
        toggleScanner();
    } else if (permissionError) {
        startScanner();
    }
"
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
            @if($state === 'ready')
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#64748B]"></span>
                    <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Paused</span>
                </div>
            @elseif($state === 'scanning')
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#15803D] animate-pulse"></span>
                    <span class="text-xs font-semibold text-[#15803D] uppercase tracking-wider">Scanning</span>
                </div>
            @elseif($state === 'success_member' || $state === 'success_book')
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#15803D]"></span>
                    <span class="text-xs font-bold text-[#15803D] uppercase tracking-wider">Recognized</span>
                </div>
            @elseif($state === 'error')
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#B91C1C]"></span>
                    <span class="text-xs font-semibold text-[#B91C1C] uppercase tracking-wider">Blocked</span>
                </div>
            @endif

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
        <div wire:ignore class="relative w-full h-[200px] bg-[#0F172A] rounded-2xl overflow-hidden flex items-center justify-center border border-[#334155] shadow-inner shrink-0">

            <!-- Camera Permission Blocked Overlay -->
            <div x-show="permissionError" class="absolute inset-0 z-30 bg-[#0F172A] flex flex-col items-center justify-center p-4 text-center gap-2">
                <div class="flex items-center justify-center rounded-full w-9 h-9 bg-[#FEF2F2] text-[#B91C1C]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <div class="px-2">
                    <template x-if="!isSecure">
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">HTTPS Connection Required</h4>
                            <p class="text-xs text-slate-300 mt-1 leading-normal max-w-[220px] mx-auto">Webcam scanning requires a secure HTTPS connection or localhost in modern browsers.</p>
                        </div>
                    </template>
                    <template x-if="isSecure">
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Camera Access Blocked</h4>
                            <p class="text-xs text-slate-300 mt-1 leading-normal max-w-[220px] mx-auto">Unable to start video feed. Please verify camera device connection and browser permissions.</p>
                        </div>
                    </template>
                </div>

                <button type="button" x-show="isSecure" @click="startScanner()" :disabled="isRequesting" class="h-8 px-4 mt-1 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-semibold transition-all shadow-xs flex items-center justify-center gap-1.5 disabled:opacity-50" x-cloak>
                    <svg x-show="isRequesting" class="w-3.5 h-3.5 text-white animate-spin" fill="none" viewBox="0 0 24 24" x-cloak aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isRequesting ? 'Reloading...' : 'Retry Connection'">Retry Connection</span>
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
            <div x-show="hasActiveScan" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute z-20 pointer-events-none w-28 h-28 border-2 border-[#15803D] rounded-xl bg-[#15803D]/15 flex items-center justify-center">
                <div class="absolute border border-[#15803D]/40 rounded-lg inset-2"></div>
            </div>

            <!-- Floating Subtitle Status Pill (Accessible aria-live) -->
            <div aria-live="polite" class="absolute bottom-2 left-1/2 transform -translate-x-1/2 z-20 py-1.5 px-4 bg-[#071943]/90 backdrop-blur-xs rounded-lg border border-white/10 flex items-center justify-center text-center max-w-[92%] transition-all duration-200">
                <p x-text="state === 'success_member' || state === 'success_book' ? detail : message"
                   :class="state === 'success_member' || state === 'success_book' ? 'text-[#BBF7D0] font-bold' : (state === 'error' ? 'text-[#FECACA] font-bold' : 'text-white font-medium')"
                   class="text-xs tracking-wider leading-normal truncate"></p>
            </div>

            <!-- Target Div element for HTML5 QR Code Scanner -->
            <div id="reader-viewfinder" class="absolute z-10 w-full h-full overflow-hidden bg-[#0F172A]"></div>
        </div>

    </div>

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
            object-fit: cover !important;
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
