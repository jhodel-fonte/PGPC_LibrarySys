<div class="bg-[#F8FAFC] flex flex-col min-h-0 flex-1 overflow-y-auto font-sans">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 pb-24 lg:pb-32 relative flex flex-col gap-6 min-h-0 flex-1">

        <!-- ============================================== -->
        <!-- 1. PAGE HEADER                                 -->
        <!-- ============================================== -->
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:shrink-0">
            <div class="flex min-w-0 items-start gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-bold tracking-tight text-[#102B70] sm:text-3xl">Import MARC Records</h1>
                        <span class="text-sm font-semibold text-slate-500">
                            &bull; MARC21 Standard
                        </span>
                    </div>
                    <p class="mt-1.5 max-w-3xl text-sm font-medium text-slate-600 leading-relaxed">
                        Upload standard MARC21 binary exchange files (<span class="font-mono text-slate-800">.mrc</span>, <span class="font-mono text-slate-800">.marc</span>) to extract bibliographic metadata, inspect book records, and batch-catalog directly into the library collection.
                    </p>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- 2. WORKFLOW STEP PROGRESSION (Initial Upload)  -->
        <!-- ============================================== -->
        @if(!$hasUploadedFile)
            <div class="relative z-10 rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Step 1 -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE]">
                        <div class="text-sm font-bold text-[#102B70]">
                            1.
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-[#102B70]">
                                Current Step
                            </div>
                            <div class="text-sm font-bold text-[#0F172A] truncate">Upload &amp; Configure MARC</div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200">
                        <div class="text-sm font-bold text-slate-400">
                            2.
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Next Step
                            </div>
                            <div class="text-sm font-bold text-slate-700 truncate">Review Extracted Books</div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200">
                        <div class="text-sm font-bold text-slate-400">
                            3.
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Final Step</div>
                            <div class="text-sm font-semibold text-slate-700 truncate">Commit to Catalog</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- 3. MAIN WORKSPACE                              -->
        <!-- ============================================== -->
        @if(!$hasUploadedFile)
            <!-- ========================================== -->
            <!-- STATE A: UPLOAD & SETUP FORM               -->
            <!-- ========================================== -->
            <div
                x-data="{
                    localFile: null,
                    fileName: '{{ addslashes($selectedFileName) }}',
                    fileSize: '{{ addslashes($selectedFileSize) }}',
                    isFileSelected: {{ $isFileSelected ? 'true' : 'false' }},
                    fileError: null,
                    isDragging: false,
                    isProcessing: false,

                    uploadProgress: 0,

                    formatBytes(bytes) {
                        if (bytes <= 0) return '0 KB';
                        const units = ['B', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(1024));
                        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
                    },

                    handleFileSelect(file) {
                        this.fileError = null;
                        if (!file) return;

                        const ext = file.name.split('.').pop().toLowerCase();
                        const allowed = ['mrc', 'marc', 'dat', 'txt', 'xml'];
                        if (!allowed.includes(ext)) {
                            this.fileError = 'Unsupported file format (.' + ext + '). Please select a standard MARC21 binary (.mrc, .marc, .dat) or MARCXML (.xml) file.';
                            return;
                        }

                        if (file.size > 52428800) {
                            this.fileError = 'File size exceeds maximum allowed limit (50MB).';
                            return;
                        }

                        this.localFile = file;
                        this.fileName = file.name;
                        this.fileSize = this.formatBytes(file.size);
                        this.isFileSelected = true;

                        // Check if this file was already uploaded within the 30-minute cache window
                        $wire.checkDuplicateFilename(file.name);
                    },

                    clearFile() {
                        this.localFile = null;
                        this.fileName = '';
                        this.fileSize = '';
                        this.isFileSelected = false;
                        this.fileError = null;
                        this.isProcessing = false;
                        this.uploadProgress = 0;
                        if (this.$refs.marcInput) {
                            this.$refs.marcInput.value = '';
                        }
                        $wire.removeFile();
                    },

                    startUploadAndProcess() {
                        if (this.isProcessing) return;
                        if (!this.localFile && !this.isFileSelected) {
                            this.fileError = 'Please select or drop a MARC file first.';
                            return;
                        }

                        this.fileError = null;
                        this.isProcessing = true;
                        this.uploadProgress = 0;

                        const executeExtraction = () => {
                            $wire.processAndReviewFile().then((success) => {
                                this.isProcessing = false;
                                if (!success) {
                                    this.fileError = $wire.fileValidationError || 'Failed to extract valid MARC21 records from the uploaded file.';
                                }
                            }).catch((err) => {
                                this.isProcessing = false;
                                this.fileError = 'Extraction Error: ' + (err?.message || 'Server error occurred while extracting MARC records.');
                            });
                        };

                        if (this.localFile) {
                            $wire.upload('marcFile', this.localFile, () => {
                                executeExtraction();
                            }, (error) => {
                                this.isProcessing = false;
                                this.fileError = 'Upload Failed: ' + (error || 'Could not upload file to server. Please check file size and connection.');
                            }, (event) => {
                                if (event && event.detail && typeof event.detail.progress !== 'undefined') {
                                    this.uploadProgress = Math.round(event.detail.progress);
                                }
                            });
                        } else {
                            executeExtraction();
                        }
                    }
                }"
                class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start"
            >
                <!-- Left: Dropzone & File Selection -->
                <div class="lg:col-span-6 rounded-2xl border border-[#E2E8F0] bg-white p-6 sm:p-7 space-y-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <div class="border-b border-[#F1F5F9] pb-4">
                        <h2 class="text-lg font-bold text-[#102B70]">Upload MARC Exchange File</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1">Select a bibliographic exchange record file to begin extraction.</p>
                    </div>

                    <!-- Hidden file input with Alpine x-ref -->
                    <input
                        type="file"
                        id="marcFileInput"
                        x-ref="marcInput"
                        class="hidden"
                        accept=".mrc,.marc,.dat,.txt,.xml"
                        x-on:change="if ($event.target.files.length > 0) handleFileSelect($event.target.files[0])"
                    >

                    <!-- State 1: Dropzone for choosing/dropping file -->
                    <div
                        x-show="!isFileSelected"
                        x-on:dragover.prevent="isDragging = true"
                        x-on:dragleave.prevent="isDragging = false"
                        x-on:drop.prevent="
                            isDragging = false;
                            if ($event.dataTransfer.files.length > 0) {
                                handleFileSelect($event.dataTransfer.files[0]);
                            }
                        "
                        x-on:click="$refs.marcInput.click()"
                        class="relative group cursor-pointer"
                    >
                        <div :class="isDragging ? 'border-[#102B70] bg-[#EFF6FF]/60 ring-2 ring-[#102B70]' : 'border-[#CBD5E1] bg-slate-50/70'"
                            class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-12 text-center transition-all group-hover:border-[#102B70] group-hover:bg-[#EFF6FF]/40">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] text-[#102B70] mb-4 group-hover:scale-105 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-[#102B70]">Click to browse or drop MARC file here</span>
                            <span class="text-sm text-slate-500 mt-2 font-medium">Accepts .mrc, .marc, .dat, .txt, .xml (Standard MARC21 Binary / XML)</span>
                            <div class="mt-5 text-xs text-slate-500 font-mono">
                                Standard: ISO 2709 &bull; Max file size: 50MB
                            </div>
                        </div>
                    </div>

                    <!-- State 2: Verified File Selected Card -->
                    <div x-show="isFileSelected" style="display: none;"
                        :class="fileError ? 'border-rose-300 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/60'"
                        class="rounded-2xl border p-6 space-y-4 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <div
                                    :class="fileError ? 'bg-rose-600' : 'bg-emerald-700'"
                                    class="flex h-12 w-12 items-center justify-center rounded-xl text-white shrink-0 shadow-xs transition-colors">
                                    <template x-if="!fileError">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </template>
                                    <template x-if="fileError">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <div class="mb-1">
                                        <span x-show="!fileError" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            File Selected & Ready
                                        </span>
                                        <span x-show="fileError" style="display: none;" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Extraction Failed
                                        </span>
                                    </div>
                                    <h3 class="text-base font-bold text-[#102B70] truncate leading-snug" x-text="fileName">
                                        {{ $selectedFileName }}
                                    </h3>
                                    <p class="text-xs text-slate-600 font-medium mt-0.5">
                                        Size: <strong class="font-bold text-slate-800" x-text="fileSize">{{ $selectedFileSize }}</strong> &bull; Format: <span class="font-mono text-slate-700">MARC21 Binary</span>
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                x-on:click="clearFile()"
                                :disabled="isProcessing"
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0 cursor-pointer disabled:opacity-50"
                                title="Remove file"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Error details block inside the card when failed -->
                        <div x-show="fileError" style="display: none;" class="rounded-xl border border-rose-200 bg-rose-100/70 p-3 text-xs text-rose-800 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="font-bold text-rose-900">MARC Extraction Error: </span>
                                <span x-text="fileError"></span>
                            </div>
                        </div>

                        <!-- Processing State inside card -->
                        <div x-show="isProcessing" style="display: none;" class="flex items-center gap-2 text-xs font-bold text-[#102B70] pt-3 border-t border-slate-200/70">
                            <svg class="animate-spin h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="uploadProgress > 0 && uploadProgress < 100 ? 'Uploading file (' + uploadProgress + '%)...' : 'Uploading file and extracting MARC records...'">Uploading file and extracting MARC records...</span>
                        </div>

                        <!-- Normal Ready state inside card -->
                        <div x-show="!isProcessing" class="flex items-center justify-between pt-3 border-t border-slate-200/70 text-xs">
                            <span x-show="!fileError" class="text-emerald-900 font-medium">Configure preferences on the right, then click below to import.</span>
                            <span x-show="fileError" style="display: none;" class="text-rose-800 font-medium">Please review the error or choose another file.</span>
                            <button
                                type="button"
                                x-on:click="$refs.marcInput.click()"
                                class="text-xs font-bold text-[#102B70] hover:underline cursor-pointer"
                            >
                                Choose Different File
                            </button>
                        </div>
                    </div>

                    <!-- 30-Minute Duplicate Filename Detection Banner -->
                    @if($duplicateFileNotice)
                        <div class="rounded-2xl border border-amber-300 bg-amber-50/95 p-4 sm:p-5 space-y-3 shadow-xs transition-all animate-fade-in">
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-2xs mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900">
                                            Similar File Recently Uploaded
                                        </span>
                                        <span class="text-xs text-amber-800 font-medium">
                                            Uploaded {{ $duplicateFileNotice['time_ago'] }} by {{ $duplicateFileNotice['uploaded_by'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm font-semibold text-amber-950 mt-1.5 leading-snug">
                                        A file named <strong class="font-bold underline decoration-amber-400">{{ $duplicateFileNotice['filename'] }}</strong> was already uploaded and parsed in the last 30 minutes ({{ $duplicateFileNotice['total_records'] }} records).
                                    </p>
                                    <p class="text-xs text-amber-800 font-medium mt-1">
                                        This 30-minute memory cache automatically expires in <strong class="font-bold text-amber-950">{{ $duplicateFileNotice['minutes_left'] }} minute(s)</strong>.
                                    </p>
                                </div>
                            </div>

                            @if(!empty($duplicateFileNotice['records_available']))
                                <div class="pt-3 border-t border-amber-200/80 flex flex-wrap items-center justify-between gap-3">
                                    <button
                                        type="button"
                                        wire:click="useCachedImport('{{ $duplicateFileNotice['import_key'] }}')"
                                        class="h-9 px-4 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs font-bold transition flex items-center gap-2 shadow-2xs active:scale-[0.98] cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>Use Cached Records ({{ $duplicateFileNotice['total_records'] }})</span>
                                    </button>
                                    <span class="text-xs text-amber-800 font-medium italic">or click &quot;Upload and Read MARC File&quot; to re-extract</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Validation Error Alert if any (When no file card displayed) -->
                    <div x-show="fileError && !isFileSelected" style="display: none;" class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-text="fileError"></span>
                    </div>

                    @if($fileValidationError)
                        <div x-show="!fileError && !isFileSelected" class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>{{ $fileValidationError }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right: Cataloging Preferences -->
                <div class="lg:col-span-6 rounded-2xl border border-[#E2E8F0] bg-white p-6 sm:p-7 space-y-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <div class="border-b border-[#F1F5F9] pb-4">
                        <h2 class="text-lg font-bold text-[#102B70]">Cataloging & Accession Setup</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1">Configure how extracted books should be assigned into the catalog.</p>
                    </div>

                    <!-- Target Collection / Section -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#334155] uppercase tracking-wider">
                            Default Collection / Shelf Location
                        </label>
                        <select wire:model="defaultCollection"
                            class="w-full h-12 px-4 rounded-xl border border-[#CBD5E1] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer">
                            <option value="General Circulation">General Circulation</option>
                            <option value="Filipiniana">Filipiniana Section</option>
                            <option value="Reference">Reference Collection</option>
                            <option value="Periodicals">Periodicals / Serials</option>
                            <option value="Special Collections">Theses & Special Collections</option>
                        </select>
                    </div>

                    <!-- Accession Number Prefix & Starting Sequence -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#334155] uppercase tracking-wider">
                                Accession Prefix
                            </label>
                            <input type="text" wire:model="accessionPrefix"
                                class="w-full h-12 px-4 rounded-xl border border-[#CBD5E1] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                                placeholder="PGPC-2026-" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#334155] uppercase tracking-wider">
                                Starting Sequence
                            </label>
                            <input type="number" wire:model="startAccessionNumber"
                                class="w-full h-12 px-4 rounded-xl border border-[#CBD5E1] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                                placeholder="1042" />
                        </div>
                    </div>

                    <!-- Copies per title -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#334155] uppercase tracking-wider">
                            Physical Copies per Book Title
                        </label>
                        <input type="number" min="1" max="50" wire:model="defaultCopies"
                            class="w-full h-12 px-4 rounded-xl border border-[#CBD5E1] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all" />
                    </div>

                    <!-- Duplicate Collision Rule -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <label class="text-xs font-bold text-[#334155] uppercase tracking-wider">
                            Duplicate ISBN Rule
                        </label>
                        <div class="space-y-2.5 text-sm font-medium text-slate-700">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="duplicatePolicy" value="skip" wire:model="duplicatePolicy" class="text-[#102B70] focus:ring-[#102B70]">
                                <span>Skip duplicates if ISBN exists in catalog</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="duplicatePolicy" value="update" wire:model="duplicatePolicy" class="text-[#102B70] focus:ring-[#102B70]">
                                <span>Add as extra copy to existing catalog entry</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 space-y-3">
                        <!-- Validation / Extraction Error Alert (Right Side) -->
                        <div x-show="fileError" style="display: none;" class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="font-bold text-rose-900">Extraction Error: </span>
                                <span class="font-medium" x-text="fileError"></span>
                            </div>
                        </div>

                        @if($fileValidationError)
                            <div x-show="!fileError" class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs text-rose-700 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <span class="font-bold text-rose-900">Extraction Error: </span>
                                    <span class="font-medium">{{ $fileValidationError }}</span>
                                </div>
                            </div>
                        @endif

                        @if($duplicateFileNotice && !empty($duplicateFileNotice['records_available']))
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 flex items-center justify-between gap-2">
                                <span>⚡ Batch parsed {{ $duplicateFileNotice['time_ago'] }} is active in 30m cache.</span>
                                <button
                                    type="button"
                                    wire:click="useCachedImport('{{ $duplicateFileNotice['import_key'] }}')"
                                    class="font-bold text-[#102B70] hover:underline cursor-pointer whitespace-nowrap"
                                >
                                    Use Cache ({{ $duplicateFileNotice['total_records'] }})
                                </button>
                            </div>
                        @endif

                        <button
                            type="button"
                            x-on:click="startUploadAndProcess()"
                            :disabled="!isFileSelected || isProcessing"
                            :class="(!isFileSelected || isProcessing) ? 'opacity-60 cursor-not-allowed' : 'hover:bg-[#0B225E] active:scale-[0.98] cursor-pointer shadow-sm'"
                            class="w-full h-12 rounded-xl bg-[#102B70] text-sm font-bold text-white transition-all flex items-center justify-center"
                        >
                            {{-- Normal State: icon and text --}}
                            <span x-show="!isProcessing" class="inline-flex items-center gap-2">
                                <template x-if="!fileError">
                                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </template>
                                <template x-if="fileError">
                                    <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </template>
                                <span x-text="fileError ? 'Retry MARC Extraction' : 'Upload and Read MARC File'">Upload and Read MARC File</span>
                            </span>

                            {{-- Loading State --}}
                            <span x-show="isProcessing" style="display: none;" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="uploadProgress > 0 && uploadProgress < 100 ? 'Uploading (' + uploadProgress + '%)...' : 'Processing MARC File...'">Processing MARC File...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- ========================================== -->
            <!-- STATE B: UPLOADED FILE SUMMARY & REVIEW    -->
            <!-- (Step Progression & Current File in One Row) -->
            <!-- ========================================== -->
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
                <!-- Step Progression -->
                <div class="lg:col-span-5 rounded-2xl border border-[#E2E8F0] bg-white p-3.5 sm:p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex items-center">
                    <div class="grid grid-cols-3 gap-2 sm:gap-2.5 w-full">
                        <!-- Step 1 -->
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200">
                            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white text-xs font-bold shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Done</div>
                                <div class="text-xs font-bold text-[#0F172A] truncate">1. Upload</div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE]">
                            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-white text-xs font-bold shadow-xs">2</span>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#102B70]">Current</div>
                                <div class="text-xs font-bold text-[#102B70] truncate">2. Review</div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-500 text-xs font-bold">3</span>
                            <div class="min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Next</div>
                                <div class="text-xs font-semibold text-slate-600 truncate">3. Catalog</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder of Current Uploaded File -->
                <div class="lg:col-span-7 rounded-2xl border border-[#CBD5E1] bg-white p-3.5 sm:p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] flex items-center justify-center shrink-0 text-[#102B70]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-bold text-[#102B70] truncate max-w-[220px] sm:max-w-xs md:max-w-sm" title="{{ $fileName }}">
                                    {{ $fileName }}
                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                    {{ $totalRecordsFound }} Records Parsed
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE] whitespace-nowrap" title="Batch records and filename are stored in memory cache with 30-minute auto-expiry">
                                    <svg class="w-3 h-3 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    30m Cache Active
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium mt-0.5 truncate">
                                {{ $fileSize }} &bull; {{ $fileFormat }} &bull; Target: <strong class="font-bold text-[#102B70]">{{ $defaultCollection }}</strong> ({{ $accessionPrefix }}{{ $startAccessionNumber }}...)
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            wire:click="removeFile"
                            class="h-9 px-3.5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition flex items-center gap-1.5 shadow-2xs hover:border-[#102B70] active:scale-[0.98] cursor-pointer"
                            title="Upload a different MARC file"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span class="whitespace-nowrap">Upload Different File</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- EMBEDDED MARC-UPLOAD REVIEW TABLE COMPONENT -->
            <div>
                <livewire:components.book-manager.marc-upload
                    :import-key="$importKey"
                    :records="$records"
                    :file-summary="$fileSummary"
                    :default-collection="$defaultCollection"
                    :default-copies="$defaultCopies"
                    :accession-prefix="$accessionPrefix"
                    :start-accession-number="$startAccessionNumber"
                    :duplicate-policy="$duplicatePolicy"
                    :key="'marc-upload-' . ($importKey ?: ($fileName ?: 'default'))"
                />
            </div>
        @endif

    </div>
</div>
