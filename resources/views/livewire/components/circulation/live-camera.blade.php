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
class="flex flex-col gap-3">

    <!-- Scanner Header -->
    <div class="flex items-center justify-between shrink-0">
        <div class="flex items-center gap-1.5">
            <!-- Collapse Toggle Caret Button -->
            <button @click="isCollapsed = !isCollapsed" class="text-[#102B70] hover:bg-slate-100 p-1 rounded-md transition-colors" title="Toggle Scanner Viewport">
                <svg :class="isCollapsed ? '-rotate-90' : ''" class="w-3.5 h-3.5 transform transition-transform duration-200 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <h3 class="text-xs font-bold text-[#102B70] uppercase tracking-wider">Scanner</h3>
        </div>

        <div class="flex items-center gap-2">
            <!-- Dynamic Status Badge inside Header -->
            @if($state === 'ready')
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#64748B]"></span>
                    <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider">Paused</span>
                </div>
            @elseif($state === 'scanning')
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse"></span>
                    <span class="text-[10px] font-bold text-[#10B981] uppercase tracking-wider">Scanning</span>
                </div>
            @elseif($state === 'success_member' || $state === 'success_book')
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FCC719]"></span>
                    <span class="text-[10px] font-bold text-[#FCC719] uppercase tracking-wider">Detected</span>
                </div>
            @elseif($state === 'error')
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                    <span class="text-[10px] font-bold text-[#EF4444] uppercase tracking-wider">Blocked</span>
                </div>
            @endif
            
            <!-- Tiny Pause/Play Camera link -->
            <button x-show="!permissionError" @click="toggleScanner()" class="text-[10px] text-[#64748B] hover:text-[#102B70] font-semibold transition-colors flex items-center gap-0.5 border-l border-slate-200 pl-2">
                <svg class="w-3 h-3 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path></svg>
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
                <div class="flex items-center justify-center rounded-full w-9 h-9 bg-red-500/10 text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                
                <div class="px-2">
                    <template x-if="!isSecure">
                        <div>
                            <h4 class="text-[11px] font-bold text-white uppercase tracking-wider">HTTPS Secure Required</h4>
                            <p class="text-[9px] text-slate-400 mt-1 leading-normal max-w-[210px] mx-auto">Webcam scanning is blocked because this page is served over an insecure HTTP connection. Browsers restrict camera access to HTTPS (or localhost).</p>
                        </div>
                    </template>
                    <template x-if="isSecure">
                        <div>
                            <h4 class="text-[11px] font-bold text-white uppercase tracking-wider">Camera Access Blocked</h4>
                            <p class="text-[9px] text-slate-400 mt-1 leading-normal max-w-[210px] mx-auto">Unable to start video feed. Please ensure a physical webcam is connected and allowed in browser permissions.</p>
                        </div>
                    </template>
                </div>

                <button x-show="isSecure" @click="startScanner()" :disabled="isRequesting" class="h-7 px-3 mt-1 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-lg text-[9px] font-bold transition-all shadow-md flex items-center justify-center gap-1 disabled:opacity-50" x-cloak>
                    <svg x-show="isRequesting" class="w-3 h-3 text-white animate-spin" fill="none" viewBox="0 0 24 24" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isRequesting ? 'Reloading...' : 'Reload'">Reload</span>
                </button>
            </div>

            <!-- Camera Paused Overlay -->
            <div x-show="isPaused" class="absolute inset-0 z-30 bg-[#0F172A]/95 flex flex-col items-center justify-center p-4 text-center gap-2" x-cloak>
                <div class="flex items-center justify-center rounded-full w-9 h-9 bg-slate-500/10 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="px-2">
                    <h4 class="text-[11px] font-bold text-white uppercase tracking-wider">Camera Paused</h4>
                    <p class="text-[9px] text-slate-400 mt-1 leading-normal max-w-[210px] mx-auto">Click "Resume" or the search bar icon to wake the camera up.</p>
                </div>
                <button @click="toggleScanner()" class="h-7 px-3 mt-1 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-lg text-[9px] font-bold transition-all shadow-md flex items-center justify-center gap-1">
                    Resume Scanner
                </button>
            </div>

            <!-- RESTORED: Viewfinder Corner Alignment Brackets -->
            <div :class="hasActiveScan ? 'border-[#FCC719] scale-110' : 'border-white'" class="absolute top-3.5 left-3.5 w-6 h-6 border-t-2 border-l-2 rounded-tl z-20 pointer-events-none transition-all duration-300 origin-top-left"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719] scale-110' : 'border-white'" class="absolute top-3.5 right-3.5 w-6 h-6 border-t-2 border-r-2 rounded-tr z-20 pointer-events-none transition-all duration-300 origin-top-right"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719] scale-110' : 'border-white'" class="absolute bottom-3.5 left-3.5 w-6 h-6 border-b-2 border-l-2 rounded-bl z-20 pointer-events-none transition-all duration-300 origin-bottom-left"></div>
            <div :class="hasActiveScan ? 'border-[#FCC719] scale-110' : 'border-white'" class="absolute bottom-3.5 right-3.5 w-6 h-6 border-b-2 border-r-2 rounded-br z-20 pointer-events-none transition-all duration-300 origin-bottom-right"></div>

            <!-- Simulated dynamic code outline box overlay (Traces QR location) -->
            <div x-show="hasActiveScan" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="absolute z-20 pointer-events-none w-28 h-28 border-[3.5px] border-dashed border-[#FCC719] rounded-2xl bg-[#FCC719]/10 shadow-[0_0_15px_rgba(252,199,25,0.4)] flex items-center justify-center">
                <div class="absolute border rounded-lg inset-2 border-[#FCC719]/30"></div>
            </div>

            <!-- Floating subtitle feedback overlay (Alpine-reactive inside wire:ignore) -->
            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 z-20 py-2 px-6 bg-black/60 backdrop-blur-sm rounded-full shadow-lg border border-white/10 flex items-center justify-center text-center max-w-[90%] transition-all duration-300">
                <p x-text="state === 'success_member' || state === 'success_book' ? detail : message" 
                   :class="state === 'success_member' || state === 'success_book' ? 'text-[#FCC719] font-bold' : (state === 'error' ? 'text-[#EF4444] font-bold' : 'text-white font-medium')"
                   class="text-[12px] drop-shadow-md tracking-wider leading-normal truncate"></p>
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