<div
    wire:ignore.self
    x-data="liveCameraScanner($wire)"
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
                    <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Paused</span>
                </div>
            @elseif($state === 'scanning')
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold text-[#15803D] uppercase tracking-wider">Scanning</span>
                </div>
            @elseif($state === 'success_member' || $state === 'success_book')
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-[#15803D] uppercase tracking-wider">Recognized</span>
                </div>
            @elseif($state === 'error')
                <div class="flex items-center gap-1.5">
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

<script>
    (function () {
        function liveCameraScanner(wire) {
            return {
                scanner: null,
                isPaused: false,
                isCollapsed: false,
                hasPermission: true,
                permissionError: false,
                isRequesting: false,
                isSecure: window.isSecureContext,
                cameraErrorTitle: 'Camera Unavailable',
                cameraErrorMsg: 'Camera permission denied or blocked',
                hasActiveScan: false,
                scanTimeout: null,
                resetTimeout: null,
                inactivityTimeout: null,
                inactivityDelay: 60000, // 60 seconds before pausing automatically

                state: wire ? wire.entangle('state') : null,
                message: wire ? wire.entangle('message') : null,
                detail: wire ? wire.entangle('detail') : null,

                async init() {
                    // Use active polling to reliably detect Html5Qrcode module regardless of SPA navigation/load timings
                    if (window.Html5Qrcode) {
                        this.startScanner();
                    } else {
                        const interval = setInterval(() => {
                            if (window.Html5Qrcode) {
                                clearInterval(interval);
                                this.startScanner();
                            }
                        }, 50);
                        // Clear interval after 6 seconds to prevent infinite execution if script fails
                        setTimeout(() => clearInterval(interval), 6000);
                    }
                },
                async cleanScannerInstance() {
                    if (this.scanner) {
                        try {
                            if (this.scanner.isScanning) {
                                await this.scanner.stop();
                            }
                            await this.scanner.clear();
                        } catch (_) {}
                        this.scanner = null;
                    }

                    // Release any unclosed media tracks from any video tag in the viewfinder
                    try {
                        const existingVideos = document.querySelectorAll('#reader-viewfinder video');
                        existingVideos.forEach(v => {
                            if (v.srcObject && v.srcObject.getTracks) {
                                v.srcObject.getTracks().forEach(t => t.stop());
                                v.srcObject = null;
                            }
                        });
                        const container = document.getElementById('reader-viewfinder');
                        if (container) {
                            container.innerHTML = '';
                        }
                    } catch (_) {}
                },
                async startScanner() {
                    if (this.isRequesting) return;
                    if (this.scanner && this.scanner.isScanning) {
                        this.hasPermission = true;
                        this.permissionError = false;
                        this.isPaused = false;
                        return;
                    }

                    this.isRequesting = true;
                    this.permissionError = false;

                    // Fully release prior instance and tracks
                    await this.cleanScannerInstance();

                    const config = {
                        fps: 20,
                        aspectRatio: 1.0,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    };

                    const onScanSuccess = (decodedText, decodedResult) => {
                        if (decodedText && !this.hasActiveScan) {
                            this.hasActiveScan = true;
                            if (wire) wire.handleScan(decodedText);
                            this.startInactivityTimer();

                            clearTimeout(this.scanTimeout);
                            clearTimeout(this.resetTimeout);

                            this.scanTimeout = setTimeout(() => {
                                this.hasActiveScan = false;
                            }, 1500);

                            this.resetTimeout = setTimeout(() => {
                                if (!this.isPaused && !this.permissionError && wire) {
                                    wire.setScanState('scanning', 'Searching for barcode or QR code...');
                                }
                            }, 2500);
                        }
                    };

                    const onScanFailure = (errorMessage) => {
                        // Frame decode error; suppress to keep console clean
                    };

                    try {
                        let started = false;

                        // 1. First attempt: Query detected cameras
                        let devices = [];
                        try {
                            devices = await Html5Qrcode.getCameras();
                        } catch (e) {
                            console.warn('Could not list cameras:', e);
                        }

                        if (devices && devices.length > 0) {
                            const backCamera = devices.find(d =>
                                d.label.toLowerCase().includes('back') ||
                                d.label.toLowerCase().includes('environment') ||
                                d.label.toLowerCase().includes('rear')
                            );
                            const targetId = backCamera ? backCamera.id : devices[0].id;

                            try {
                                this.scanner = new Html5Qrcode('reader-viewfinder');
                                await this.scanner.start(targetId, config, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (devErr) {
                                console.warn('Starting with camera ID failed, attempting generic fallback:', devErr);
                                await this.cleanScannerInstance();
                            }
                        }

                        // 2. Second attempt: FacingMode environment (back camera)
                        if (!started) {
                            try {
                                this.scanner = new Html5Qrcode('reader-viewfinder');
                                await this.scanner.start({ facingMode: 'environment' }, config, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (envErr) {
                                console.warn('Starting with facingMode environment failed, attempting user webcam:', envErr);
                                await this.cleanScannerInstance();
                            }
                        }

                        // 3. Third attempt: FacingMode user (standard webcam)
                        if (!started) {
                            this.scanner = new Html5Qrcode('reader-viewfinder');
                            await this.scanner.start({ facingMode: 'user' }, config, onScanSuccess, onScanFailure);
                            started = true;
                        }

                        this.hasPermission = true;
                        this.permissionError = false;
                        this.isPaused = false;
                        this.isRequesting = false;
                        if (wire) wire.setScanState('scanning', 'Searching for barcode or QR code...');
                        this.startInactivityTimer();
                    } catch (err) {
                        console.warn('Failed to start html5-qrcode feed:', err);
                        this.hasPermission = false;
                        this.permissionError = true;
                        this.isRequesting = false;

                        const errStr = (err?.message || err?.name || err?.toString() || '').toLowerCase();
                        if (errStr.includes('notreadableerror') || errStr.includes('could not start video source')) {
                            this.cameraErrorTitle = 'Camera In Use / Locked';
                            this.cameraErrorMsg = 'The webcam is currently in use by another application (Zoom, Teams, Camera app) or another browser tab. Please close other apps and click Retry.';
                        } else if (errStr.includes('notallowederror') || errStr.includes('permission denied')) {
                            this.cameraErrorTitle = 'Camera Permission Blocked';
                            this.cameraErrorMsg = 'Camera access was blocked. Please allow camera permissions in your browser address bar and click Retry.';
                        } else if (errStr.includes('notfounderror') || errStr.includes('no camera') || errStr.includes('devicenotfound')) {
                            this.cameraErrorTitle = 'No Webcam Detected';
                            this.cameraErrorMsg = 'No camera device was detected. Please plug in or enable your webcam and click Retry.';
                        } else {
                            this.cameraErrorTitle = 'Camera Unavailable';
                            this.cameraErrorMsg = err.message || err.toString();
                        }

                        if (wire) wire.setScanState('error', this.cameraErrorTitle);
                    }
                },
                toggleScanner() {
                    if (this.isPaused) {
                        this.startScanner();
                    } else if (this.scanner && this.scanner.isScanning) {
                        this.scanner.stop().then(() => {
                            this.isPaused = true;
                            this.clearInactivityTimer();
                            if (wire) wire.setScanState('ready', 'Camera is currently paused');
                        }).catch(err => {
                            console.warn('Failed to stop html5-qrcode scanner:', err);
                        });
                    } else {
                        this.isPaused = true;
                        this.clearInactivityTimer();
                        if (wire) wire.setScanState('ready', 'Camera is currently paused');
                    }
                },
                destroy() {
                    this.cleanScannerInstance();
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
                            if (wire) wire.setScanState('ready', 'Camera paused due to inactivity');
                        }).catch(err => {
                            console.warn('Failed to stop scanner on inactivity:', err);
                        });
                    }
                }
            };
        }

        window.liveCameraScanner = liveCameraScanner;
        if (window.Alpine) {
            window.Alpine.data('liveCameraScanner', liveCameraScanner);
        } else {
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('liveCameraScanner', liveCameraScanner);
            });
        }
    })();
</script>
