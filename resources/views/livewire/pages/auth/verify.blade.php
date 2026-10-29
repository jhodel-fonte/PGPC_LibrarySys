<div
    x-data="{
        mode: 'upload',
        documentType: @entangle('documentType'),
        capturedImageBase64: @entangle('capturedImageBase64'),
        previewUrl: null,
        fileName: '',
        cameraActive: false,
        cameraError: '',
        facingMode: 'environment',
        stream: null,

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.capturedImageBase64 = null;
            this.fileName = file.name;
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewUrl = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.previewUrl = 'pdf';
            }
        },

        async startCamera() {
            this.cameraError = '';
            this.stopCamera();
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.cameraError = 'Camera access is not supported on this browser or device.';
                return;
            }
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: this.facingMode }
                });
                this.cameraActive = true;
                this.$nextTick(() => {
                    if (this.$refs.video) {
                        this.$refs.video.srcObject = this.stream;
                    }
                });
            } catch (err) {
                this.cameraError = 'Unable to access camera. Please allow camera permissions or use File Upload instead.';
                this.cameraActive = false;
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        async switchCamera() {
            this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
            await this.startCamera();
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            if (!video || !canvas) return;
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            this.previewUrl = dataUrl;
            this.capturedImageBase64 = dataUrl;
            this.fileName = 'captured-id-photo.jpg';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
            this.stopCamera();
        },

        clearDocument() {
            this.previewUrl = null;
            this.capturedImageBase64 = null;
            this.fileName = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
            $wire.set('uploadedFile', null);
        }
    }"
    x-on:livewire:navigating.window="stopCamera()"
    class="w-full rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-8 shadow-sm font-sans text-[#0F172A]"
>
    <!-- Header -->
    <div class="mb-6">
        <p class="text-xs sm:text-sm font-bold text-[#0F172A]">
            Step 2 of 2 · Account Verification
        </p>
        <h1 class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-[#0F172A]">
            Upload Verification Document
        </h1>
        <p class="mt-1.5 text-sm sm:text-base text-[#64748B] leading-relaxed">
            Your account has been created. Please upload a clear photo of your School ID or Certificate of Registration (COR) so a librarian or administrator can verify and activate your account.
        </p>
    </div>

    <form wire:submit="submitVerification" class="space-y-5">
        @error('uploadedFile')
            <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 text-xs sm:text-sm font-semibold text-[#B91C1C]">
                {{ $message }}
            </div>
        @enderror

        <!-- 1. Document Type Selection -->
        <div>
            <label for="documentType" class="mb-2 block text-sm font-semibold text-[#334155]">
                Document Type <span class="text-[#EF4444]">*</span>
            </label>
            <select
                id="documentType"
                x-model="documentType"
                class="h-14 w-full rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 text-sm font-semibold text-[#0F172A] outline-none transition hover:border-[#CBD5E1] focus:border-[#102B70] focus:bg-white focus:ring-4 focus:ring-[#EFF6FF]"
            >
                <option value="school_id">School ID Card</option>
                <option value="cor">Certificate of Registration (COR)</option>
                <option value="enrollment_form">Official Enrollment Form</option>
            </select>
        </div>

        <!-- 2. Method Tabs: Upload File vs Use Camera -->
        <div>
            <label class="mb-2 block text-sm font-semibold text-[#334155]">
                Submission Method <span class="text-[#EF4444]">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-1.5">
                <button
                    type="button"
                    @click="mode = 'upload'; stopCamera();"
                    :class="mode === 'upload' ? 'bg-[#102B70] text-white shadow-xs' : 'text-[#334155] hover:text-[#0F172A]'"
                    class="h-10 rounded-xl text-xs sm:text-sm font-semibold transition-colors cursor-pointer"
                >
                    Upload File
                </button>
                <button
                    type="button"
                    @click="mode = 'camera'; clearDocument(); startCamera();"
                    :class="mode === 'camera' ? 'bg-[#102B70] text-white shadow-xs' : 'text-[#334155] hover:text-[#0F172A]'"
                    class="h-10 rounded-xl text-xs sm:text-sm font-semibold transition-colors cursor-pointer"
                >
                    Use Camera
                </button>
            </div>
        </div>

        <!-- 3A. File Upload Mode -->
        <div x-show="mode === 'upload'" class="space-y-3">
            <div x-show="!previewUrl">
                <label
                    for="verification-file"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#CBD5E1] bg-[#F8FAFC] px-6 py-10 text-center transition hover:border-[#102B70] hover:bg-[#EFF6FF]/40 cursor-pointer"
                >
                    <p class="text-sm font-semibold text-[#0F172A]">
                        Click to choose a file or drag and drop
                    </p>
                    <p class="mt-1 text-xs text-[#64748B]">
                        Accepted formats: JPG, PNG, WEBP, or PDF (Max 5MB)
                    </p>
                    <span class="mt-4 inline-flex h-9 items-center rounded-xl border border-[#CBD5E1] bg-white px-4 text-xs font-semibold text-[#102B70]">
                        Browse Files
                    </span>
                    <input
                        x-ref="fileInput"
                        id="verification-file"
                        type="file"
                        wire:model="uploadedFile"
                        accept="image/jpeg,image/png,image/webp,application/pdf"
                        class="hidden"
                        @change="handleFileSelect($event)"
                    >
                </label>
            </div>
        </div>

        <!-- 3B. Camera Capture Mode -->
        <div x-show="mode === 'camera'" x-cloak class="space-y-3">
            <template x-if="cameraError">
                <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 text-xs font-medium text-[#B91C1C]">
                    <p x-text="cameraError"></p>
                </div>
            </template>

            <div x-show="cameraActive && !previewUrl" class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-slate-900">
                <video
                    x-ref="video"
                    autoplay
                    playsinline
                    class="aspect-video w-full object-cover"
                ></video>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white p-3 border-t border-[#E2E8F0]">
                    <button
                        type="button"
                        @click="switchCamera()"
                        class="h-10 rounded-xl border border-[#CBD5E1] bg-white px-4 text-xs sm:text-sm font-semibold text-[#334155] hover:bg-[#F8FAFC] cursor-pointer"
                    >
                        Switch Camera
                    </button>
                    <button
                        type="button"
                        @click="capturePhoto()"
                        class="h-10 rounded-xl bg-[#102B70] px-5 text-xs sm:text-sm font-semibold text-white hover:bg-[#0B225E] cursor-pointer"
                    >
                        Capture Photo
                    </button>
                </div>
            </div>

            <div x-show="!cameraActive && !previewUrl && !cameraError" class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-6 text-center">
                <p class="text-sm text-[#64748B]">Camera is currently off.</p>
                <button
                    type="button"
                    @click="startCamera()"
                    class="mt-3 h-10 rounded-xl bg-[#102B70] px-4 text-xs font-semibold text-white hover:bg-[#0B225E] cursor-pointer"
                >
                    Start Camera
                </button>
            </div>

            <canvas x-ref="canvas" class="hidden"></canvas>
        </div>

        <!-- 4. Preview Selected / Captured Document -->
        <div x-show="previewUrl" x-cloak class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Selected Document</p>
                    <p class="truncate text-sm font-semibold text-[#0F172A]" x-text="fileName"></p>
                </div>
                <button
                    type="button"
                    @click="clearDocument(); if (mode === 'camera') startCamera();"
                    class="shrink-0 rounded-xl border border-[#CBD5E1] bg-white px-3 py-1.5 text-xs font-semibold text-[#B91C1C] hover:bg-[#FEF2F2] cursor-pointer"
                >
                    Remove / Retake
                </button>
            </div>

            <template x-if="previewUrl && previewUrl !== 'pdf'">
                <div class="overflow-hidden rounded-xl border border-[#E2E8F0] bg-white">
                    <img :src="previewUrl" alt="Document preview" class="max-h-64 w-full object-contain">
                </div>
            </template>

            <template x-if="previewUrl === 'pdf'">
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 text-xs font-semibold text-[#334155]">
                    PDF document ready for submission (<span x-text="fileName"></span>)
                </div>
            </template>
        </div>

        <!-- Guidelines Note -->
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 text-xs text-[#64748B] space-y-1">
            <p class="font-semibold text-[#334155]">Before submitting, please make sure:</p>
            <ul class="list-disc pl-4 space-y-0.5">
                <li>Your full name and Student ID number are clearly readable.</li>
                <li>The image is not blurry, cropped, or covered by glare.</li>
            </ul>
        </div>

        <!-- Submit & Secondary Actions -->
        <div class="pt-2 space-y-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="h-14 w-full rounded-2xl bg-[#102B70] text-sm sm:text-base font-bold text-white transition hover:bg-[#0B225E] focus:outline-none focus:ring-4 focus:ring-[#EFF6FF] disabled:opacity-60 cursor-pointer"
            >
                <span wire:loading.remove wire:target="submitVerification,uploadedFile">Submit for Verification</span>
                <span wire:loading wire:target="submitVerification,uploadedFile">Uploading & Submitting...</span>
            </button>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-1 text-xs sm:text-sm font-semibold text-[#64748B]">
                <a href="{{ route('opac.index') }}" class="hover:text-[#102B70] hover:underline" wire:navigate>
                    Browse OPAC Catalog First
                </a>
                <a href="{{ route('logout') }}" class="hover:text-[#B91C1C] hover:underline">
                    Sign Out
                </a>
            </div>
        </div>
    </form>
</div>
