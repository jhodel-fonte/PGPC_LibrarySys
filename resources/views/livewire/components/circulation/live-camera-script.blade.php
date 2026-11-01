<script>
    (function () {
        function liveCameraScanner(wire, autoStart = true) {
            return {
                scanner: null,
                cleanup: null,
                isPaused: !autoStart,
                enabled: autoStart,
                isCollapsed: false,
                permissionError: false,
                isRequesting: false,
                isSecure: window.isSecureContext,
                cameraErrorTitle: 'Camera Unavailable',
                cameraErrorMsg: '',
                hasActiveScan: false,
                scanMessage: 'Point the camera at a QR code or barcode.',
                scanSuccess: false,
                lastCode: '',
                lastSeen: 0,
                scanId: null,
                scanTimer: null,
                scanTimedOut: false,
                generation: 0,
                destroyed: false,

                init() {
                    if (autoStart) this.startScanner();
                },
                async cleanScannerInstance() {
                    const scanner = this.scanner;
                    this.scanner = null;
                    if (!scanner) return;
                    try {
                        if (scanner.isScanning) await scanner.stop();
                        scanner.clear();
                    } catch (error) {
                        console.warn('Could not release camera:', error);
                    }
                },
                async startScanner() {
                    this.enabled = true;
                    if (this.isRequesting || this.destroyed) return;
                    if (this.scanner?.isScanning) { this.isPaused = false; return; }
                    this.isRequesting = true;
                    this.permissionError = false;
                    this.isPaused = false;
                    this.scanMessage = 'Starting camera…';
                    const generation = ++this.generation;
                    try {
                        if (this.cleanup) await this.cleanup;
                        await this.cleanScannerInstance();
                        for (let i = 0; i < 60 && !window.Html5Qrcode && generation === this.generation && !this.destroyed; i++) {
                            await new Promise(resolve => setTimeout(resolve, 100));
                        }
                        if (generation !== this.generation || !this.enabled || this.destroyed) return;
                        if (!window.Html5Qrcode) throw new Error('Scanner library is unavailable. Reload the page and try again.');
                        const config = { fps: 20, aspectRatio: 1.0 };
                        const onSuccess = code => this.detectCode(code);
                        const onFailure = () => {
                            if (this.lastCode && Date.now() - this.lastSeen > 1000) this.lastCode = '';
                        };
                        let devices = [];
                        try { devices = await window.Html5Qrcode.getCameras(); } catch (_) {}
                        if (generation !== this.generation || !this.enabled || this.destroyed) return;
                        const back = devices.find(device => /back|rear|environment/i.test(device.label));
                        const candidates = [back?.id || devices[0]?.id, { facingMode: 'environment' }, { facingMode: 'user' }].filter(Boolean);
                        let lastError;
                        for (const candidate of candidates) {
                            if (generation !== this.generation || !this.enabled || this.destroyed) return;
                            this.scanner = new window.Html5Qrcode('reader-viewfinder');
                            try {
                                await this.scanner.start(candidate, config, onSuccess, onFailure);
                                lastError = null;
                                break;
                            } catch (error) {
                                lastError = error;
                                await this.cleanScannerInstance();
                            }
                        }
                        if (generation !== this.generation || !this.enabled || this.destroyed) return;
                        if (lastError) throw lastError;
                        this.scanMessage = 'Point the camera at a QR code or barcode.';
                    } catch (error) {
                        if (generation !== this.generation || !this.enabled || this.destroyed) return;
                        this.permissionError = true;
                        this.cameraErrorTitle = 'Camera unavailable';
                        const description = String(error?.message || error?.name || error);
                        if (/notallowed|permission denied/i.test(description)) {
                            this.cameraErrorTitle = 'Camera permission blocked';
                            this.cameraErrorMsg = 'Allow camera access in your browser settings, then retry.';
                        } else if (/notfound|no camera|devicenotfound/i.test(description)) {
                            this.cameraErrorTitle = 'No camera detected';
                            this.cameraErrorMsg = 'Connect or enable a camera, then retry.';
                        } else if (/notreadable|video source/i.test(description)) {
                            this.cameraErrorTitle = 'Camera in use';
                            this.cameraErrorMsg = 'Close other apps or tabs using the camera, then retry.';
                        } else {
                            this.cameraErrorMsg = description;
                        }
                    } finally {
                        if (generation !== this.generation || !this.enabled || this.destroyed) await this.cleanScannerInstance();
                        this.isRequesting = false;
                        if (generation !== this.generation && this.enabled && !this.destroyed) this.startScanner();
                    }
                },
                detectCode(code) {
                    if (!this.enabled || this.destroyed || !code) return;
                    const now = Date.now();
                    if (code === this.lastCode) { this.lastSeen = now; return; }
                    if (this.hasActiveScan) return;
                    this.lastCode = code;
                    this.lastSeen = now;
                    if (autoStart) {
                        window.dispatchEvent(new CustomEvent('camera-code-read', { detail: { code } }));
                        this.scanSuccess = true;
                        this.scanMessage = 'Code added to the input. Looking up details…';
                        return;
                    }
                    this.hasActiveScan = true;
                    this.scanTimedOut = false;
                    this.scanSuccess = false;
                    this.scanMessage = 'Code detected. Looking up details…';
                    const scanId = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
                    this.scanId = scanId;
                    this.scanTimer = setTimeout(() => {
                        if (this.scanId === scanId) {
                            this.scanTimedOut = true;
                            this.scanMessage = 'Lookup is taking longer than expected. Check the connection or retry.';
                        }
                    }, 15000);
                    Promise.resolve(wire.handleScan(code, scanId)).catch(() => this.finishScan({ scanId, success: false, message: 'Could not process the scan. Try again.' }));
                },
                finishScan(result) {
                    if (!this.hasActiveScan || result.scanId !== this.scanId) return;
                    clearTimeout(this.scanTimer);
                    this.hasActiveScan = false;
                    this.scanTimedOut = false;
                    this.scanId = null;
                    this.scanSuccess = !!result.success;
                    this.scanMessage = result.message || 'Lookup complete.';
                },
                retryScan() {
                    clearTimeout(this.scanTimer);
                    this.hasActiveScan = false;
                    this.scanTimedOut = false;
                    this.scanId = null;
                    this.lastCode = '';
                    this.scanSuccess = false;
                    this.scanMessage = 'Point the camera at a QR code or barcode.';
                },
                stopScanner() {
                    this.enabled = false;
                    this.isPaused = true;
                    this.generation++;
                    this.retryScan();
                    if (!this.isRequesting) this.cleanup = this.cleanScannerInstance();
                },
                toggleScanner() {
                    if (this.enabled && !this.isPaused) this.stopScanner();
                    else this.startScanner();
                },
                destroy() {
                    this.destroyed = true;
                    this.stopScanner();
                }
            };
        }
        window.liveCameraScanner = liveCameraScanner;
    })();
</script>
