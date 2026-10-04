<div
    class="rounded-3xl border border-[#E2E8F0] bg-white p-6 space-y-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]"
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
    @clear-book-cover.window="clearImage()"
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

