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
        <!-- 2. WORKFLOW STEP PROGRESSION                   -->
        <!-- ============================================== -->
        <div class="relative z-10 rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Step 1 -->
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasUploadedFile ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-[#EFF6FF] border border-[#BFDBFE]' }}">
                    <div class="text-sm font-bold {{ $hasUploadedFile ? 'text-emerald-700' : 'text-[#102B70]' }}">
                        @if($hasUploadedFile)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>1.</span>
                            </span>
                        @else
                            1.
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wider {{ $hasUploadedFile ? 'text-emerald-800' : 'text-[#102B70]' }}">
                            {{ $hasUploadedFile ? 'Completed' : 'Current Step' }}
                        </div>
                        <div class="text-sm font-bold text-[#0F172A] truncate">Upload & Configure MARC</div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasUploadedFile ? 'bg-[#EFF6FF] border border-[#BFDBFE]' : 'bg-white border border-slate-200' }}">
                    <div class="text-sm font-bold {{ $hasUploadedFile ? 'text-[#102B70]' : 'text-slate-400' }}">
                        2.
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold uppercase tracking-wider {{ $hasUploadedFile ? 'text-[#102B70]' : 'text-slate-500' }}">
                            {{ $hasUploadedFile ? 'Current Step' : 'Next Step' }}
                        </div>
                        <div class="text-sm font-bold {{ $hasUploadedFile ? 'text-[#102B70]' : 'text-slate-700' }} truncate">Review Extracted Books</div>
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

        <!-- ============================================== -->
        <!-- 3. MAIN WORKSPACE                              -->
        <!-- ============================================== -->
        @if(!$hasUploadedFile)
            <!-- ========================================== -->
            <!-- STATE A: UPLOAD & SETUP FORM               -->
            <!-- ========================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left: Dropzone -->
                <div class="lg:col-span-6 rounded-2xl border border-[#E2E8F0] bg-white p-6 sm:p-7 space-y-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <div class="border-b border-[#F1F5F9] pb-4">
                        <h2 class="text-lg font-bold text-[#102B70]">Upload MARC Exchange File</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1">Select a bibliographic exchange record file to begin extraction.</p>
                    </div>

                    <!-- DROPZONE -->
                    <div class="relative group cursor-pointer" wire:click="simulateFileUpload">
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#CBD5E1] bg-slate-50/70 p-12 text-center transition-all group-hover:border-[#102B70] group-hover:bg-[#EFF6FF]/40">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] text-[#102B70] mb-4 group-hover:scale-105 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <span class="text-base sm:text-lg font-bold text-[#102B70]">Click to browse or drop MARC file here</span>
                            <span class="text-sm text-slate-500 mt-2 font-medium">Accepts .mrc, .marc, .dat, .txt (Standard MARC21 Binary)</span>

                            <div class="mt-5 text-xs text-slate-500 font-mono">
                                Standard: ISO 2709 &bull; Encodings: UTF-8 / MARC-8
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm text-slate-500">Need a sample file to test?</span>
                        <button type="button" wire:click="simulateFileUpload" class="text-sm font-bold text-[#102B70] hover:underline">
                            Load Sample Batch File (7 Books)
                        </button>
                    </div>
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

                    <div class="pt-4">
                        <button
                            type="button"
                            wire:click="simulateFileUpload"
                            class="w-full h-12 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>Extract & Review MARC Records</span>
                        </button>
                    </div>
                </div>

            </div>
        @else
            <!-- ========================================== -->
            <!-- STATE B: UPLOADED FILE SUMMARY & REVIEW    -->
            <!-- ========================================== -->
            <!-- COMPACT FILE BANNER -->
            <div class="rounded-2xl border border-[#CBD5E1] bg-white p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <svg class="w-7 h-7 text-[#102B70] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base sm:text-lg font-bold text-[#102B70] truncate">
                                {{ $fileName }}
                            </h3>
                            <span class="text-sm font-bold text-emerald-700">
                                &bull; {{ $totalRecordsFound }} Records Parsed
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 font-medium mt-0.5">
                            {{ $fileSize }} &bull; {{ $fileFormat }} &bull; Target: <strong class="font-bold text-[#102B70]">{{ $defaultCollection }}</strong> ({{ $accessionPrefix }}{{ $startAccessionNumber }}...)
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        wire:click="removeFile"
                        class="h-10 px-4 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-bold text-slate-700 transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Upload Different File</span>
                    </button>
                </div>
            </div>

            <!-- EMBEDDED MARC-UPLOAD REVIEW TABLE COMPONENT -->
            <div>
                <livewire:components.book-manager.marc-upload
                    :default-collection="$defaultCollection"
                    :default-copies="$defaultCopies"
                    :accession-prefix="$accessionPrefix"
                    :start-accession-number="$startAccessionNumber"
                />
            </div>
        @endif

    </div>
</div>
