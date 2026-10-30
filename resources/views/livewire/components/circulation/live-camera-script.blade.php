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
                loaderInterval: null,
                loaderTimeout: null,
                inactivityDelay: 60000, // 60 seconds before pausing automatically

                state: wire ? wire.entangle('state') : null,
                message: wire ? wire.entangle('message') : null,
                detail: wire ? wire.entangle('detail') : null,

                async init() {
                    // Use active polling to reliably detect Html5Qrcode module regardless of SPA navigation/load timings
                    if (window.Html5Qrcode) {
                        this.startScanner();
                    } else {
                        this.loaderInterval = setInterval(() => {
                            if (window.Html5Qrcode) {
                                clearInterval(this.loaderInterval);
                                clearTimeout(this.loaderTimeout);
                                this.startScanner();
                            }
                        }, 50);
                        // Clear interval after 6 seconds to prevent infinite execution if script fails
                        this.loaderTimeout = setTimeout(() => clearInterval(this.loaderInterval), 6000);
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
                    clearInterval(this.loaderInterval);
                    clearTimeout(this.loaderTimeout);
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

    })();
</script>
