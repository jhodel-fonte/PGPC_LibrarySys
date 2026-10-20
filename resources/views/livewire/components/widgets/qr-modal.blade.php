<div
    wire:ignore.self
    x-data="qrModalHandler()"
    @open-qr-modal.window="openModal($event.detail)"
    @close-qr-modal.window="closeModal()"
    @keydown.escape.window="closeModal()"
>
    <!-- Modal Teleported to Body -->
    <template x-teleport="body">
        <div
            x-show="isOpen"
            x-cloak
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 select-none"
            aria-modal="true"
            role="dialog"
        >
            <!-- Backdrop Overlay with subtle blur -->
            <div
                @click="closeModal()"
                x-show="isOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-[#071943]/65 backdrop-blur-xs transition-opacity"
            ></div>

            <!-- Modal Window (Institutional Library Pass Badge) -->
            <div
                x-show="isOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-slate-200/90 overflow-hidden"
            >
                <!-- Card Header with PGPC Institutional Branding -->
                <div class="bg-[#102B70] px-5 py-4 text-white relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-bold text-white leading-tight truncate" x-text="title">
                                    PGPC Library QR Code
                                </h3>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="closeModal()"
                            class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer shrink-0 ml-2"
                            aria-label="Close modal"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-6 flex flex-col items-center text-center relative">
                    <!-- Subject / Title & Secondary Meta -->
                    <div x-show="name" class="mb-3.5 max-w-full">
                        <h4 class="text-lg font-extrabold text-[#0B2454] leading-snug break-words" x-text="name"></h4>
                        <p x-show="secondaryInfo" class="text-xs font-semibold text-slate-500 mt-0.5 break-words" x-text="secondaryInfo"></p>
                    </div>

                    <!-- QR Code Container Box (Fixed dimensions to prevent any stacking or duplication) -->
                    <div
                        x-ref="qrContainer"
                        class="p-3.5 rounded-2xl bg-white border-2 border-dashed border-[#102B70]/20 shadow-xs flex items-center justify-center transition-all hover:border-[#102B70]/40 w-[248px] h-[248px] sm:w-[264px] sm:h-[264px] relative overflow-hidden"
                    >
                        <!-- 1. SVG Vector Display -->
                        <div
                            x-show="Boolean(qrSvg) && !isLoading"
                            class="w-52 h-52 sm:w-56 sm:h-56 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full"
                            x-html="qrSvg"
                        ></div>

                        <!-- 2. Loading State while generating -->
                        <div
                            x-show="isLoading"
                            class="absolute inset-0 flex flex-col items-center justify-center gap-2.5 text-[#102B70] bg-white"
                        >
                            <svg class="w-8 h-8 animate-spin text-[#102B70]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs font-bold text-[#102B70]">Generating QR Code...</span>
                        </div>

                        <!-- 3. Dynamic Fallback Image if no SVG generated but value exists and not loading -->
                        <template x-if="!qrSvg && !isLoading && value">
                            <img
                                :src="'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' + encodeURIComponent(value) + '&color=16-43-112'"
                                alt="QR Code"
                                class="w-52 h-52 sm:w-56 sm:h-56 object-contain rounded-lg"
                                loading="lazy"
                            >
                        </template>

                        <!-- 4. Empty State -->
                        <div
                            x-show="!value && !isLoading"
                            class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-xs bg-white"
                        >
                            <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>No QR payload provided</span>
                        </div>
                    </div>

                    <!-- Value Display & Copy Button -->
                    <div x-show="value" class="mt-4 flex items-center justify-center gap-2">
                        <span class="text-xs font-semibold text-slate-400" x-text="label"></span>
                        <span class="font-mono text-base font-extrabold text-[#102B70] tracking-wider select-all" x-text="value"></span>
                        <button
                            type="button"
                            @click="copyValue()"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#102B70] hover:bg-slate-100 transition cursor-pointer"
                            title="Copy Value"
                            aria-label="Copy Value"
                        >
                            <span x-show="!copied">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span x-show="copied">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </button>
                    </div>

                    <!-- Usage Instruction -->
                    <p x-show="instruction" class="text-[11.5px] text-slate-500 mt-2 max-w-[270px] leading-relaxed" x-text="instruction"></p>

                    <!-- Action Buttons in Footer -->
                    <div class="mt-5 w-full pt-4 border-t border-slate-100 flex flex-col gap-2">
                        <div x-show="value" class="grid grid-cols-2 gap-2 w-full">
                            <button
                                type="button"
                                @click="downloadQr()"
                                class="h-10 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center gap-1.5"
                                title="Save pure QR code as PNG image"
                            >
                                <svg class="w-3.5 h-3.5 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Save</span>
                            </button>

                            <button
                                type="button"
                                @click="printPass()"
                                class="h-10 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-bold transition shadow-2xs cursor-pointer flex items-center justify-center gap-1.5"
                                title="Print QR code in new tab"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Print</span>
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="closeModal()"
                            class="w-full h-10 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <span>Close</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    (function() {
        // Multi-layer client-side cache (memory map + sessionStorage)
        window.__pgpcQrCache = window.__pgpcQrCache || new Map();

        function getCachedQr(key) {
            if (!key) return null;
            const cleanKey = String(key).trim();
            if (window.__pgpcQrCache && window.__pgpcQrCache.has(cleanKey)) {
                return window.__pgpcQrCache.get(cleanKey);
            }
            try {
                const stored = sessionStorage.getItem('pgpc_qr_' + cleanKey);
                if (stored) {
                    if (window.__pgpcQrCache) window.__pgpcQrCache.set(cleanKey, stored);
                    return stored;
                }
            } catch (e) {}
            return null;
        }

        function setCachedQr(key, svg) {
            if (!key || !svg || typeof svg !== 'string') return;
            const cleanKey = String(key).trim();
            if (!window.__pgpcQrCache) window.__pgpcQrCache = new Map();
            window.__pgpcQrCache.set(cleanKey, svg);
            try {
                sessionStorage.setItem('pgpc_qr_' + cleanKey, svg);
            } catch (e) {}
        }

        function qrModalHandler() {
            return {
                isOpen: false,
                copied: false,
                value: '',
                title: 'PGPC Library QR Code',
                subtitle: 'Digital Identification Pass',
                name: '',
                secondaryInfo: '',
                label: 'ID Number:',
                instruction: 'Present this QR code to the library circulation desk or scanner.',
                qrSvg: '',
                isLoading: false,

                openModal(detail) {
                    if (!detail) return;
                    if (typeof detail === 'string') {
                        detail = { value: detail };
                    }

                    // 1. Immediately wipe previous active data so no prior QR code or text flickers
                    this.copied = false;
                    this.qrSvg = '';
                    this.isLoading = false;

                    // 2. Extract new modal parameters
                    const val = String(
                        detail.value 
                        || detail.id 
                        || detail.accession_no 
                        || detail.accession_number 
                        || detail.student_id 
                        || detail.school_id_number 
                        || detail.code 
                        || detail.qr 
                        || ''
                    ).trim();

                    this.value = val;
                    this.title = detail.title || detail.heading || 'PGPC Library QR Code';
                    this.subtitle = detail.subtitle || 'Digital Identification Pass';
                    this.name = detail.name || '';
                    this.secondaryInfo = detail.secondaryInfo || detail.secondary || detail.program || '';
                    this.label = detail.label || 'ID Number:';
                    this.instruction = detail.instruction || detail.note || detail.description || 'Present this QR code to the library circulation desk or scanner.';
                    this.isOpen = true;

                    if (!val) {
                        return;
                    }

                    // 3. If explicit SVG is already passed in detail, display and cache it
                    const explicitSvg = detail.qrSvg || detail.svg;
                    if (explicitSvg && typeof explicitSvg === 'string' && explicitSvg.trim()) {
                        const cleanSvg = explicitSvg.trim();
                        this.qrSvg = cleanSvg;
                        setCachedQr(val, cleanSvg);
                        return;
                    }

                    // 4. Check cache for instantaneous zero-latency display
                    const cachedSvg = getCachedQr(val);
                    if (cachedSvg) {
                        this.qrSvg = cachedSvg;
                        return;
                    }

                    // 5. Not in cache: show loading spinner and fetch from backend
                    this.isLoading = true;
                    const requestedVal = val;

                    if (this.$wire && typeof this.$wire.getSvg === 'function') {
                        this.$wire.getSvg(requestedVal)
                            .then(svg => {
                                if (svg && typeof svg === 'string' && svg.trim()) {
                                    const cleanSvg = svg.trim();
                                    setCachedQr(requestedVal, cleanSvg);

                                    // Guard against race conditions: Only render if modal is still open for the SAME copy
                                    if (this.isOpen && this.value === requestedVal) {
                                        this.qrSvg = cleanSvg;
                                        this.isLoading = false;
                                    }
                                } else if (this.isOpen && this.value === requestedVal) {
                                    this.isLoading = false;
                                }
                            })
                            .catch(err => {
                                console.error('Failed to generate QR code SVG:', err);
                                if (this.isOpen && this.value === requestedVal) {
                                    this.isLoading = false;
                                }
                            });
                    } else {
                        this.isLoading = false;
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    // Wipe active QR data cleanly so next open starts fresh
                    this.value = '';
                    this.qrSvg = '';
                    this.name = '';
                    this.secondaryInfo = '';
                    this.title = 'PGPC Library QR Code';
                    this.subtitle = 'Digital Identification Pass';
                    this.label = 'ID Number:';
                    this.instruction = '';
                    this.isLoading = false;
                    this.copied = false;

                    if (this.$wire && typeof this.$wire.closeQrModal === 'function') {
                        this.$wire.closeQrModal();
                    }
                },

                copyValue() {
                    const textToCopy = this.value;
                    if (!textToCopy) return;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(textToCopy);
                    } else {
                        const el = document.createElement('textarea');
                        el.value = textToCopy;
                        document.body.appendChild(el);
                        el.select();
                        document.execCommand('copy');
                        document.body.removeChild(el);
                    }
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                },

                downloadQr() {
                    const targetVal = this.value || 'pgpc-qrcode';
                    const safeName = String(targetVal).trim().replace(/[^a-zA-Z0-9_-]/g, '_') || 'pgpc-qrcode';
                    
                    let svgString = '';
                    if (this.qrSvg && this.qrSvg.trim()) {
                        svgString = this.qrSvg.trim();
                    } else if (this.$refs.qrContainer) {
                        const svgElem = this.$refs.qrContainer.querySelector('svg');
                        if (svgElem) {
                            svgString = new XMLSerializer().serializeToString(svgElem);
                        }
                    }

                    const imgElem = this.$refs.qrContainer ? this.$refs.qrContainer.querySelector('img') : null;

                    const exportSize = 1024;
                    const canvas = document.createElement('canvas');
                    canvas.width = exportSize;
                    canvas.height = exportSize;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, exportSize, exportSize);

                    const triggerDownload = (c) => {
                        c.toBlob((blob) => {
                            if (!blob) return;
                            const pngUrl = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = pngUrl;
                            a.download = safeName + '.png';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            setTimeout(() => URL.revokeObjectURL(pngUrl), 1000);
                        }, 'image/png');
                    };

                    if (svgString) {
                        let cleanSvg = svgString;
                        if (!cleanSvg.includes('xmlns=')) {
                            cleanSvg = cleanSvg.replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"');
                        }
                        const svgBlob = new Blob([cleanSvg], { type: 'image/svg+xml;charset=utf-8' });
                        const url = URL.createObjectURL(svgBlob);
                        const img = new Image();
                        img.onload = () => {
                            ctx.drawImage(img, 0, 0, exportSize, exportSize);
                            URL.revokeObjectURL(url);
                            triggerDownload(canvas);
                        };
                        img.onerror = () => {
                            URL.revokeObjectURL(url);
                            const a = document.createElement('a');
                            a.href = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(cleanSvg);
                            a.download = safeName + '.svg';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        };
                        img.src = url;
                    } else if (imgElem && imgElem.src) {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => {
                            ctx.drawImage(img, 0, 0, exportSize, exportSize);
                            triggerDownload(canvas);
                        };
                        img.onerror = () => {
                            window.open(imgElem.src, '_blank');
                        };
                        img.src = imgElem.src;
                    }
                },

                printPass() {
                    let cleanQrHtml = '';
                    if (this.qrSvg && this.qrSvg.trim()) {
                        cleanQrHtml = this.qrSvg.trim();
                    } else if (this.$refs.qrContainer) {
                        const svgElem = this.$refs.qrContainer.querySelector('svg');
                        const imgElem = this.$refs.qrContainer.querySelector('img');
                        if (svgElem) {
                            cleanQrHtml = new XMLSerializer().serializeToString(svgElem);
                        } else if (imgElem && imgElem.src) {
                            cleanQrHtml = `<img src="${imgElem.src}" alt="QR Code" />`;
                        }
                    }

                    if (!cleanQrHtml) return;

                    const escapeHtml = (text) => {
                        const div = document.createElement('div');
                        div.textContent = text || '';
                        return div.innerHTML;
                    };

                    const docTitle = this.name ? (this.name + ' - QR Code') : (this.title || 'PGPC Library QR Code');
                    const subMeta = [this.secondaryInfo, this.value ? (this.label + ' ' + this.value) : ''].filter(Boolean).join(' • ');

                    const printTab = window.open('', '_blank');
                    if (!printTab) return;

                    const htmlContent = [
                        '<!DOCTYPE html>',
                        '<html><head><meta charset="utf-8">',
                        '<title>' + escapeHtml(docTitle) + '</title>',
                        '<link rel="preconnect" href="https://fonts.googleapis.com">',
                        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
                        '<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">',
                        '<style>',
                        '  @page { size: auto; margin: 12mm; }',
                        '  * { box-sizing: border-box; }',
                        '  body {',
                        '    font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;',
                        '    display: flex;',
                        '    flex-direction: column;',
                        '    align-items: center;',
                        '    justify-content: center;',
                        '    min-height: 90vh;',
                        '    margin: 0;',
                        '    padding: 24px;',
                        '    color: #0F172A;',
                        '    text-align: center;',
                        '    background-color: #FFFFFF;',
                        '  }',
                        '  .card {',
                        '    border: 2px solid #E2E8F0;',
                        '    border-radius: 16px;',
                        '    padding: 24px 32px;',
                        '    max-width: 380px;',
                        '    display: flex;',
                        '    flex-direction: column;',
                        '    align-items: center;',
                        '  }',
                        '  .brand {',
                        '    font-size: 11px;',
                        '    font-weight: 800;',
                        '    letter-spacing: 1.2px;',
                        '    text-transform: uppercase;',
                        '    color: #102B70;',
                        '    margin-bottom: 6px;',
                        '  }',
                        '  .title {',
                        '    font-size: 16px;',
                        '    font-weight: 800;',
                        '    color: #0B2454;',
                        '    margin-bottom: 4px;',
                        '    line-height: 1.3;',
                        '  }',
                        '  .meta {',
                        '    font-size: 12px;',
                        '    font-weight: 600;',
                        '    color: #64748B;',
                        '    margin-bottom: 16px;',
                        '  }',
                        '  .qr-box {',
                        '    width: 240px;',
                        '    height: 240px;',
                        '    padding: 12px;',
                        '    border: 1.5px dashed rgba(16, 43, 112, 0.25);',
                        '    border-radius: 14px;',
                        '    display: flex;',
                        '    align-items: center;',
                        '    justify-content: center;',
                        '    background: #FFFFFF;',
                        '  }',
                        '  .qr-box svg, .qr-box img { width: 100%; height: 100%; object-fit: contain; }',
                        '  .value {',
                        '    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;',
                        '    font-size: 15px;',
                        '    font-weight: 800;',
                        '    color: #102B70;',
                        '    letter-spacing: 1px;',
                        '    margin-top: 14px;',
                        '  }',
                        '  .instruction {',
                        '    font-size: 11px;',
                        '    color: #64748B;',
                        '    margin-top: 8px;',
                        '    max-width: 280px;',
                        '    line-height: 1.4;',
                        '  }',
                        '</style>',
                        '</head><body>',
                        '  <div class="card">',
                        '    <div class="brand">Padre Garcia Polytechnic College</div>',
                        (this.name ? ('    <div class="title">' + escapeHtml(this.name) + '</div>') : ''),
                        (subMeta ? ('    <div class="meta">' + escapeHtml(subMeta) + '</div>') : ''),
                        '    <div class="qr-box">' + cleanQrHtml + '</div>',
                        (this.value ? ('    <div class="value">' + escapeHtml(this.value) + '</div>') : ''),
                        (this.instruction ? ('    <div class="instruction">' + escapeHtml(this.instruction) + '</div>') : ''),
                        '  </div>',
                        '  <script>',
                        '    window.onload = function() {',
                        '      setTimeout(function() { window.print(); }, 250);',
                        '    };',
                        '  <\/script>',
                        '</body></html>'
                    ].join('\n');

                    printTab.document.write(htmlContent);
                    printTab.document.close();
                }
            };
        }

        window.qrModalHandler = qrModalHandler;
        if (window.Alpine) {
            window.Alpine.data('qrModalHandler', qrModalHandler);
        } else {
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('qrModalHandler', qrModalHandler);
            });
        }
    })();
</script>
