<!-- Form Grid Container -->
<form wire:submit.prevent="save" class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start font-sans">

    <!-- Global Form Error & Validation Banner -->
    @if($errorMessage || $errors->any())
        <div class="col-span-1 lg:col-span-12 rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 md:p-5 shadow-[0_4px_20px_rgba(239,68,68,0.06)] animate-fade-in transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex-1 min-w-0 pt-0.5">
                        <h4 class="text-sm font-bold text-[#B91C1C] uppercase tracking-wider">
                            {{ $errorMessage ? 'Unable to Update Book' : 'Error Updating Book' }}
                        </h4>
                        <p class="text-sm text-[#7F1D1D] font-medium mt-1 leading-relaxed">
                            {{ $errorMessage ?: 'Please review and correct the ' . $errors->count() . ' highlighted field(s) below before submitting.' }}
                        </p>
                        @if($errors->any() && !$errorMessage)
                            <ul class="mt-2 list-disc list-inside text-sm text-[#B91C1C] font-medium space-y-0.5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                @if($errorMessage)
                    <button
                        type="button"
                        wire:click="$set('errorMessage', '')"
                        class="text-[#EF4444] hover:text-[#B91C1C] p-1 rounded-lg transition-colors focus:outline-none shrink-0"
                        title="Dismiss"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- LEFT COLUMN: Physical Copy & Visual Cover      -->
    <!-- ============================================== -->
    <div class="lg:col-span-4 xl:col-span-4 space-y-6">
        
        <!-- 1. INITIAL PHYSICAL COPY CARD -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 space-y-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#102B70]">Book Copy</h2>
                    <p>Fill up all the required fields to add a new book copy.</p>
                </div>
            </div>

            <!-- Accession Number -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">
                    Accession Number
                </label>
                <input
                    wire:model="accessionNumber"
                    type="text"
                    placeholder="Required"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('accessionNumber') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>

            <!-- Barcode with inline Generate button -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Barcode</label>
                <div class="flex rounded-2xl border border-[#E2E8F0] overflow-hidden focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF] transition-all bg-white">
                    <input
                        wire:model="barcode"
                        type="text"
                        placeholder="Optional"
                        class="w-full h-14 px-4 bg-transparent text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none border-0"
                    >
                    <button
                        type="button"
                        wire:click="generateBarcode"
                        class="px-4 h-14 bg-slate-50 hover:bg-slate-100 text-[#102B70] hover:text-[#0B225E] text-xs uppercase tracking-wider font-bold border-l border-[#E2E8F0] transition-colors shrink-0 select-none flex items-center gap-1.5"
                    >
                        <span>Generate</span>
                    </button>
                </div>
                @error('barcode') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>

            <!-- Status -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Status</label>
                <div class="relative">
                    <select
                        wire:model="status"
                        class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all appearance-none cursor-pointer"
                    >
                        <option value="Available">Available</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Reserved">Reserved</option>
                    </select>

                </div>
                @error('status') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>

            <!-- Location / Shelf -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Location / Shelf</label>
                <input
                    wire:model="location"
                    type="text"
                    placeholder="e.g. Shelf A1"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('location') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>

            <!-- Date Acquired -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Date Acquired</label>
                <input
                    wire:model="dateAcquired"
                    type="date"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer"
                >
                @error('dateAcquired') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- 2. BOOK COVER CARD (Instant Alpine Preview + Livewire Direct Upload) -->
        <div
            class="rounded-2xl border border-[#E2E8F0] bg-white p-6 space-y-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]"
            x-data="{
                previewUrl: null,
                fileName: '',
                fileSize: '',
                isDragging: false,
                fileError: null,

                handleFileSelect(event) {
                    const file = event.target.files && event.target.files[0];
                    if (file) {
                        if (!file.type.startsWith('image/')) {
                            this.fileError = 'Please select a valid image file (PNG, JPG, JPEG, WEBP).';
                            return;
                        }
                        if (file.size > 5242880) { // 5MB limit
                            this.fileError = 'Image size must be less than 5MB.';
                            return;
                        }
                        this.fileError = null;
                        this.previewUrl = URL.createObjectURL(file);
                        this.fileName = file.name;
                        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                    }
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const files = event.dataTransfer && event.dataTransfer.files;
                    if (files && files.length > 0) {
                        const file = files[0];
                        if (!file.type.startsWith('image/')) {
                            this.fileError = 'Please select a valid image file (PNG, JPG, JPEG, WEBP).';
                            return;
                        }
                        if (file.size > 5242880) {
                            this.fileError = 'Image size must be less than 5MB.';
                            return;
                        }
                        this.fileError = null;
                        this.previewUrl = URL.createObjectURL(file);
                        this.fileName = file.name;
                        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';

                        if (this.$refs.fileInput) {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            this.$refs.fileInput.files = dt.files;
                            this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                },

                clearImage() {
                    this.previewUrl = null;
                    this.fileName = '';
                    this.fileSize = '';
                    this.fileError = null;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                    $wire.removeCoverImage();
                }
            }"
            x-on:livewire-upload-error="previewUrl = null; fileError = 'Upload failed on the server. The file may exceed PHP upload_max_filesize / post_max_size.'"
        >
            <!-- Header -->
            <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#102B70]">Book Cover</h2>
                    <p class="text-xs text-slate-500 font-medium">Upload a Book Cover</p>
                </div>
            </div>

            <!-- Upload Dropzone / Full Length Adaptive Preview Container -->
            <div class="relative">
                <label
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    class="block relative border-2 border-dashed rounded-2xl p-4 sm:p-5 text-center cursor-pointer transition-all bg-[#F8FAFC] group overflow-hidden min-h-[140px] flex flex-col justify-center"
                    :class="isDragging ? 'border-[#102B70] bg-[#EFF6FF]/60 ring-4 ring-[#EFF6FF]' : 'border-[#CBD5E1] hover:border-[#102B70] hover:bg-[#EFF6FF]/30'"
                >
                    <input
                        x-ref="fileInput"
                        type="file"
                        wire:model="coverImage"
                        @change="handleFileSelect($event)"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="sr-only"
                    >

                    <!-- Full-Length / Adaptive Clear Preview State (x-show prevents morph DOM teardown) -->
                    <div x-show="previewUrl" x-cloak class="flex flex-col items-center w-full animate-fade-in">
                        <!-- Image Card Container (Full Length Adaptive Aspect Ratio) -->
                        <div class="relative w-full rounded-2xl bg-white border border-slate-200/80 shadow-md overflow-hidden flex items-center justify-center p-2 group/preview">
                            <img
                                :src="previewUrl"
                                class="w-full max-h-[500px] h-auto object-contain rounded-xl transition-transform duration-300 group-hover/preview:scale-[1.01]"
                                alt="Book Cover Preview"
                            >

                            <!-- Top Floating Remove Button -->
                            <div class="absolute top-3 right-3 flex items-center justify-end pointer-events-none">
                                <button
                                    type="button"
                                    @click.prevent.stop="clearImage()"
                                    class="pointer-events-auto w-8 h-8 rounded-full bg-white/95 hover:bg-[#EF4444] text-slate-700 hover:text-white flex items-center justify-center shadow-lg border border-slate-200 hover:border-[#EF4444] transition-all transform hover:scale-110"
                                    title="Remove cover image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Livewire upload processing indicator overlay -->
                            <div
                                wire:loading
                                wire:target="coverImage"
                                class="absolute inset-0 bg-[#071943]/60 backdrop-blur-[2px] rounded-2xl flex flex-col items-center justify-center p-4 text-white"
                            >
                                <svg class="animate-spin w-8 h-8 text-[#FCC719] mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-xs font-bold tracking-wide">Processing cover...</span>
                            </div>
                        </div>

                        <!-- Action Info Below Full Image -->
                        <div class="mt-3 flex items-center justify-between w-full px-1">
                            <span class="text-xs font-medium text-slate-400 truncate max-w-[200px]" x-text="fileName"></span>
                        </div>
                    </div>

                    <!-- Empty Dropzone State (x-show prevents morph DOM teardown) -->
                    <div x-show="!previewUrl" class="flex flex-col items-center justify-center py-6 w-full">
                        <div class="w-14 h-14 rounded-2xl bg-white border border-[#E2E8F0] shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#102B70] group-hover:border-[#102B70]/30 transition-all mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-[#0F172A] group-hover:text-[#102B70]">Click to upload or drag & drop book cover</p>
                        <p class="text-xs text-slate-500 font-medium mt-1">PNG, JPG, JPEG, WEBP up to 5MB (Auto-converted to WebP upon saving)</p>
                    </div>
                </label>
            </div>

            <!-- Error Messages -->
            <template x-if="fileError">
                <span class="text-xs font-semibold text-[#EF4444] text-center block" x-text="fileError"></span>
            </template>
            @error('coverImage') <span class="text-xs font-semibold text-[#EF4444] text-center block">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- ============================================== -->
    <!-- RIGHT COLUMN: Bibliographic Information        -->
    <!-- ============================================== -->
    <div class="lg:col-span-8 xl:col-span-8 rounded-2xl border border-[#E2E8F0] bg-white p-6 lg:p-8 space-y-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
        
        <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
            <div>
                <h2 class="text-base font-bold text-[#102B70]">Bibliographic Information</h2>
                <p class="text-xs text-slate-500 font-medium">Core title, authorship, identifiers, and classification</p>
            </div>
        </div>

        <!-- ISBN & ISSN Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">ISBN</label>
                <input
                    wire:model="isbn"
                    type="text"
                    placeholder="10 or 13-digit ISBN"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('isbn') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">ISSN</label>
                <input
                    wire:model="issn"
                    type="text"
                    placeholder="Optional"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('issn') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Book Title -->
        <div class="space-y-1.5">
            <label class="text-sm font-semibold text-[#334155]">
                Book Title <span class="text-[#EF4444] font-bold">*</span>
            </label>
            <input
                wire:model="bookTitle"
                type="text"
                placeholder="Enter the complete book title"
                class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
            >
            @error('bookTitle') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
        </div>

        <!-- Subtitle -->
        <div class="space-y-1.5">
            <label class="text-sm font-semibold text-[#334155]">Subtitle</label>
            <input
                wire:model="subtitle"
                type="text"
                placeholder="Optional"
                class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
            >
            @error('subtitle') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
        </div>

        <!-- Author Field with Live Alpine.js + AJAX Autocomplete Search -->
        <div class="space-y-3"
             x-data="{
                query: @entangle('authorSearch').live,
                selectedAuthorId: @entangle('authorId').live,
                selectedAuthorName: @entangle('selectedAuthorName').live,
                results: [],
                isOpen: false,
                isLoading: false,
                highlightedIndex: 0,
                debounceTimer: null,
                showSplitFields: @entangle('showManualAuthor').live,

                searchAuthors() {
                    clearTimeout(this.debounceTimer);
                    if (!this.query || this.query.trim().length === 0) {
                        this.results = [];
                        this.isOpen = false;
                        this.isLoading = false;
                        return;
                    }
                    this.isLoading = true;
                    this.debounceTimer = setTimeout(() => {
                        fetch('{{ route('admin.authors.search') }}?query=' + encodeURIComponent(this.query.trim()), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.results = data || [];
                            this.isOpen = true;
                            this.isLoading = false;
                            this.highlightedIndex = 0;
                        })
                        .catch(err => {
                            console.error('Author search failed:', err);
                            this.isLoading = false;
                        });
                    }, 250);
                },

                selectAuthor(author) {
                    this.selectedAuthorId = author.id;
                    this.selectedAuthorName = author.name;
                    this.query = author.name;
                    this.isOpen = false;
                    this.results = [];
                    $wire.selectAuthor(author.id, author.name);
                },

                useAsNewAuthor() {
                    this.selectedAuthorId = null;
                    this.selectedAuthorName = this.query.trim();
                    this.isOpen = false;
                    this.results = [];
                    $wire.set('authorId', null);
                    $wire.set('selectedAuthorName', this.query.trim());
                },

                clearSelection() {
                    this.selectedAuthorId = null;
                    this.selectedAuthorName = '';
                    this.query = '';
                    this.results = [];
                    this.isOpen = false;
                    $wire.clearAuthor();
                },

                nextItem() {
                    if (!this.isOpen || this.results.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex + 1) % (this.results.length + 1);
                },

                prevItem() {
                    if (!this.isOpen || this.results.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex - 1 + (this.results.length + 1)) % (this.results.length + 1);
                },

                selectHighlighted() {
                    if (!this.isOpen) return;
                    if (this.highlightedIndex < this.results.length) {
                        this.selectAuthor(this.results[this.highlightedIndex]);
                    } else if (this.query && this.query.trim().length > 0) {
                        this.useAsNewAuthor();
                    }
                }
             }"
             @click.outside="isOpen = false"
        >
            <div class="flex items-center justify-between">
                <label class="text-sm font-semibold text-[#334155]">
                    Author <span class="text-[#EF4444] font-bold">*</span>
                </label>
                <button
                    type="button"
                    @click="showSplitFields = !showSplitFields"
                    class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] hover:underline flex items-center gap-1 transition-colors"
                >
                    <span x-text="showSplitFields ? '– Hide split name fields' : '+ Split First & Last Name'"></span>
                </button>
            </div>

            <!-- Author Search Input Container -->
            <div class="relative">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <input
                        type="text"
                        x-model="query"
                        @input="searchAuthors()"
                        @focus="if(query && query.trim().length > 0) searchAuthors()"
                        @keydown.arrow-down.prevent="nextItem()"
                        @keydown.arrow-up.prevent="prevItem()"
                        @keydown.enter.prevent="selectHighlighted()"
                        @keydown.escape="isOpen = false"
                        placeholder="Type to search existing authors or enter a new author name..."
                        class="w-full h-14 pl-10 pr-20 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        :class="{ 'border-[#102B70] ring-2 ring-[#EFF6FF]': selectedAuthorId }"
                    >

                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
                        <!-- Loading indicator -->
                        <div x-show="isLoading" x-cloak class="text-[#102B70]">
                            <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <!-- Clear button -->
                        <button
                            type="button"
                            x-show="query && query.length > 0"
                            x-cloak
                            @click="clearSelection()"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                            title="Clear input"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Floating Dropdown Panel -->
                <div
                    x-show="isOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-[#E2E8F0] rounded-2xl shadow-xl overflow-hidden max-h-64 overflow-y-auto"
                >
                    <!-- Matching Existing Authors -->
                    <template x-if="results.length > 0">
                        <div class="py-1">
                            <div class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-50 border-b border-[#F1F5F9]">
                                Existing Authors in Catalog
                            </div>
                            <template x-for="(author, index) in results" :key="author.id">
                                <div
                                    @click="selectAuthor(author)"
                                    @mouseenter="highlightedIndex = index"
                                    :class="{
                                        'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === index || selectedAuthorId === author.id,
                                        'text-[#0F172A] hover:bg-slate-50': highlightedIndex !== index && selectedAuthorId !== author.id
                                    }"
                                    class="px-4 py-2.5 flex items-center justify-between cursor-pointer transition-colors text-sm font-medium border-b border-slate-50 last:border-0"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-[#EFF6FF] text-[#102B70] flex items-center justify-center text-xs font-bold shrink-0">
                                            <span x-text="author.name.charAt(0)"></span>
                                        </div>
                                        <span x-text="author.name"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-400 px-2 py-0.5 rounded-md bg-slate-100">Existing</span>
                                        <template x-if="selectedAuthorId === author.id">
                                            <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Add as New Author Option -->
                    <template x-if="query && query.trim().length > 0">
                        <div
                            @click="useAsNewAuthor()"
                            @mouseenter="highlightedIndex = results.length"
                            :class="{ 'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === results.length }"
                            class="px-4 py-3 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between cursor-pointer hover:bg-[#EFF6FF] text-sm font-semibold text-[#102B70] transition-colors"
                        >
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    +
                                </div>
                                <span>Create new author: "<span class="underline" x-text="query.trim()"></span>"</span>
                            </div>
                            <span class="text-xs font-semibold text-[#3B82F6] px-2 py-0.5 rounded-md bg-[#EFF6FF]">New Author</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Author Status Indicator Pill -->
            <template x-if="selectedAuthorId">
                <div class="flex items-center gap-2 pt-1 text-xs font-semibold text-[#15803D] animate-fade-in">
                    <svg class="w-4 h-4 text-[#16A34A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Selected existing author from catalog (<span x-text="selectedAuthorName"></span>)</span>
                </div>
            </template>
            <template x-if="!selectedAuthorId && query && query.trim().length > 0">
                <div class="flex items-center gap-2 pt-1 text-xs font-semibold text-[#3B82F6] animate-fade-in">
                    <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Will be registered as a new author</span>
                </div>
            </template>

            @error('authorSearch') <span class="text-xs font-semibold text-[#EF4444] block">{{ $message }}</span> @enderror
            @error('authorId') <span class="text-xs font-semibold text-[#EF4444] block">{{ $message }}</span> @enderror

            <!-- Optional Split First Name & Last Name (collapsible) -->
            <div x-show="showSplitFields" x-cloak class="pt-2 grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in bg-[#F8FAFC] p-4 rounded-2xl border border-[#E2E8F0]">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#334155]">Author First Name</label>
                    <input
                        wire:model="newAuthorFirstName"
                        type="text"
                        placeholder="e.g. Robert"
                        class="w-full h-12 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                    >
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#334155]">Author Last Name</label>
                    <input
                        wire:model="newAuthorLastName"
                        type="text"
                        placeholder="e.g. Martin"
                        class="w-full h-12 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF]"
                    >
                </div>
            </div>
        </div>

        <!-- Call Number & Classification Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">
                    Call Number <span class="text-[#EF4444] font-bold">*</span>
                </label>
                <input
                    wire:model.live="callNumber"
                    @input="
                        const val = ($event.target.value || '').trim();
                        const match = val.match(/^([a-zA-Z]{1,3})\s*\d+/);
                        $dispatch('call-number-code-detected', { code: match ? match[1].toUpperCase() : null });
                    "
                    type="text"
                    placeholder="e.g. QA76.73.J38"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('callNumber') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Classification</label>
                <input
                    wire:model="classification"
                    type="text"
                    placeholder="Optional"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('classification') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Publisher Searchable Select Component -->
        <div>
            <livewire:components.book-manager.add-publisher-select
                :publisher-id="$publisherId"
                :publisher-name="$publisherName"
                :selected-publisher-name="$selectedPublisherName"
                wire:key="book-add-publisher-select"
            />
        </div>

        <!-- Publication Year, Copyright Year, Edition, Pages Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Publication Year</label>
                <input
                    wire:model="publicationYear"
                    type="number"
                    placeholder="YYYY"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('publicationYear') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Copyright Year</label>
                <input
                    wire:model="copyrightYear"
                    type="number"
                    placeholder="YYYY"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('copyrightYear') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Edition</label>
                <input
                    wire:model="edition"
                    type="text"
                    placeholder="e.g. 1st Edition"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('edition') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-[#334155]">Pages</label>
                <input
                    wire:model="pages"
                    type="number"
                    placeholder="e.g. 350"
                    class="w-full h-14 px-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                >
                @error('pages') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Categories & Language Multi-Select Comboboxes Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Categories Searchable Multi-Select Component -->
            <div>
                <livewire:components.book-manager.add-category-multi-select wire:model="selectedCategories" wire:key="book-add-categories-select" />
                @error('selectedCategories') <span class="text-xs font-semibold text-[#EF4444] block mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Language Searchable Multi-Select Component -->
            <div>
                <livewire:components.book-manager.add-lang-multi-select wire:model="selectedLanguages" wire:key="book-add-languages-select" />
                @error('selectedLanguages') <span class="text-xs font-semibold text-[#EF4444] block mt-1">{{ $message }}</span> @enderror
                @error('language') <span class="text-xs font-semibold text-[#EF4444] block mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Book Description -->
        <div class="space-y-1.5">
            <label class="text-sm font-semibold text-[#334155]">Book Description</label>
            <textarea
                wire:model="bookDescription"
                rows="3"
                placeholder="Write a brief summary..."
                class="w-full p-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
            ></textarea>
            @error('bookDescription') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
        </div>

        <!-- Notes -->
        <div class="space-y-1.5">
            <label class="text-sm font-semibold text-[#334155]">Notes</label>
            <textarea
                wire:model="notes"
                rows="2"
                placeholder="Additional notes..."
                class="w-full p-4 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
            ></textarea>
            @error('notes') <span class="text-xs font-semibold text-[#EF4444]">{{ $message }}</span> @enderror
        </div>

        <!-- Bottom Action Buttons Bar -->
        <div class="pt-6 border-t border-[#F1F5F9] flex items-center justify-end gap-3">
            <a
                href="{{ route('admin.book-management.index') }}"
                wire:navigate
                class="px-6 h-12 rounded-2xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-colors flex items-center justify-center focus:outline-none"
            >
                Cancel
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="px-7 h-12 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-semibold transition-all shadow-sm flex items-center gap-2 focus:outline-none disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <span>Update Book Details</span>
                </span>
                <span wire:loading.flex wire:target="save" class="items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Updating...</span>
                </span>
            </button>
        </div>

    </div>
</form>
