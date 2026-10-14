<div
    x-data="{
        photoPreview: null,
        photoName: '',
        photoSize: '',
        isDragging: false,
        menuOpen: false,

        // Role & permissions entangled with Livewire backend
        role: @entangle('role').live,
        permissions: @entangle('permissions'),
        presets: @js(config('settings.librarian_role_permission', [])),

        applyRole() {
            const roleKey = (this.role === 'head_librarian' || this.role === 'librarian_head') ? 'librarian_head' : this.role;
            const allowed = this.presets[roleKey] || this.presets[this.role] || [];
            Object.keys(this.permissions).forEach(key => this.permissions[key] = allowed.includes(key));
        },

        togglePermission(key) {
            if (!this.role) return;
            this.permissions[key] = !this.permissions[key];
        },

        toggleCategory(keys) {
            if (!this.role) return;
            const allActive = keys.every(k => this.permissions[k]);
            keys.forEach(k => {
                this.permissions[k] = !allActive;
            });
        },

        categoryActiveCount(keys) {
            if (!this.permissions) return 0;
            return keys.filter(k => this.permissions[k]).length;
        },

        selectAll() {
            if (!this.role) return;
            Object.keys(this.permissions).forEach(k => this.permissions[k] = true);
        },

        clearAll() {
            if (!this.role) return;
            Object.keys(this.permissions).forEach(k => this.permissions[k] = false);
        },

        get enabledCount() {
            return Object.values(this.permissions).filter(Boolean).length;
        },

        get totalCount() {
            return Object.keys(this.permissions).length;
        },

        get isCustom() {
            if (!this.role) return false;
            const roleKey = (this.role === 'head_librarian' || this.role === 'librarian_head') ? 'librarian_head' : this.role;
            const allowed = this.presets[roleKey] || this.presets[this.role] || [];
            return Object.keys(this.permissions).some(key => this.permissions[key] !== allowed.includes(key));
        },

        handleFileSelect(event) {
            const file = event.target.files?.[0];
            if (file) {
                this.processFile(file);
            }
        },

        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer?.files?.[0];
            if (file) {
                this.processFile(file);
                $wire.upload('photo', file);
            }
        },

        processFile(file) {
            if (!file.type.match('image.*')) {
                alert('Please upload an image file (PNG, JPG, or WEBP)');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('File size exceeds 5MB limit');
                return;
            }
            this.photoName = file.name;
            this.photoSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removePhoto() {
            this.photoPreview = null;
            this.photoName = '';
            this.photoSize = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
            $wire.removePhoto();
        },

        clearForm() {
            this.removePhoto();
            this.menuOpen = false;
            this.role = '';
            Object.keys(this.permissions).forEach(k => this.permissions[k] = false);
            $wire.clearForm();
        },

        scrollToError(detail) {
            const field = typeof detail === 'string' ? detail : (detail?.field || '');
            const fieldMap = {
                'firstName': 'librarian-first-name',
                'middleName': 'librarian-middle-name',
                'lastName': 'librarian-last-name',
                'schoolIdNumber': 'librarian-school-id',
                'contactNum': 'librarian-contact',
                'email': 'librarian-email',
                'role': 'librarian-role',
                'photo': 'librarian-photo-container',
                'error-banner': 'form-error-banner'
            };
            const targetId = fieldMap[field] || field;
            let el = document.getElementById(targetId);
            if (!el && field) {
                el = document.querySelector(`[name="${field}"]`)
                    || document.querySelector(`[wire\\:model*="${field}"]`)
                    || document.querySelector(`[wire\\:model\\.blur*="${field}"]`);
            }
            if (!el) {
                el = document.getElementById('form-error-banner');
            }
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Temporary focus ring highlight to catch the user's attention
                el.classList.add('ring-4', 'ring-[#EF4444]', 'ring-offset-2');
                setTimeout(() => {
                    el.classList.remove('ring-4', 'ring-[#EF4444]', 'ring-offset-2');
                }, 2500);
                setTimeout(() => {
                    if (typeof el.focus === 'function') {
                        try {
                            el.focus({ preventScroll: true });
                        } catch (err) {}
                    }
                }, 350);
            }
        }
    }"
    x-init="$wire.on('scroll-to-error', data => scrollToError(data))"
    @scroll-to-error.window="scrollToError($event.detail)"
    class="font-sans antialiased text-[#0F172A]"
>
    <!-- Main Form Card Container -->
    <div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-4 sm:p-6 lg:p-8 shadow-[0_8px_30px_rgba(16,43,112,0.04)]">

        <!-- Top Card Header: Title & More Options -->
        <div class="flex items-start justify-between border-b border-[#F1F5F9] pb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Librarian Registration Form <span class="text-[11px] font-semibold text-[#EF4444]">* Required</span></h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                    Enter staff details to create a librarian account.
                </p>
            </div>

            <!-- More Actions Dropdown -->
            <div class="relative shrink-0" @click.outside="menuOpen = false">
                <button
                    type="button"
                    @click="menuOpen = !menuOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:border-[#102B70]/30 hover:bg-[#EFF6FF] hover:text-[#102B70] transition-colors focus:outline-none focus:ring-2 focus:ring-[#102B70]"
                    title="Form Options"
                    aria-label="Form Options"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="12" cy="12" r="1.25" fill="currentColor"></circle>
                        <circle cx="19" cy="12" r="1.25" fill="currentColor"></circle>
                        <circle cx="5" cy="12" r="1.25" fill="currentColor"></circle>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    x-show="menuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 rounded-xl border border-[#E2E8F0] bg-white py-1.5 shadow-lg z-30"
                >
                    <button
                        type="button"
                        @click="clearForm()"
                        class="flex w-full items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-red-600 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Clear Form</span>
                    </button>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a
                        href="{{ route((request()->is('librarian*') ? 'librarian.' : 'admin.') . 'user-management', ['type' => 'librarian']) }}"
                        wire:navigate
                        class="flex w-full items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        <span>Cancel Registration</span>
                    </a>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-9 pt-4">

            <!-- Global Form Error & Validation Banner -->
            @if($errorMessage || $errors->any())
                <div id="form-error-banner" class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 sm:p-5 shadow-[0_4px_20px_rgba(239,68,68,0.06)] animate-fade-in transition-all">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0 pt-0.5">
                                <h4 class="text-sm font-bold text-[#B91C1C] uppercase tracking-wider">
                                    {{ $errorMessage ? 'Unable to Save Librarian' : 'Please check required fields' }}
                                </h4>
                                <p class="text-sm text-[#7F1D1D] font-medium mt-1 leading-relaxed">
                                    {{ $errorMessage ?: 'Please review and correct the ' . $errors->count() . ' highlighted field(s) below before submitting.' }}
                                </p>
                                @if($errors->any() && !$errorMessage)
                                    <ul class="mt-2 list-disc list-inside text-xs text-[#B91C1C] font-semibold space-y-1">
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

            <!-- ========================================== -->
            <!-- SECTION 1: PERSONAL IDENTITY INFORMATION   -->
            <!-- ========================================== -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">

                    <!-- First Name * (librarians.first_name) -->
                    <div class="space-y-2 group">
                        <label for="librarian-first-name" class="block text-sm font-semibold text-[#334155]">
                            First Name <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                id="librarian-first-name"
                                type="text"
                                wire:model.blur="firstName"
                                placeholder="e.g. Maria Clara"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('firstName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('firstName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Middle Name (librarians.middle_name - nullable) -->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label for="librarian-middle-name" class="block text-sm font-semibold text-[#334155]">
                                Middle Name
                            </label>
                            <span class="text-[11px] font-semibold text-slate-400">Optional</span>
                        </div>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                id="librarian-middle-name"
                                type="text"
                                wire:model.blur="middleName"
                                placeholder="e.g. De Los Santos"
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('middleName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('middleName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name * (librarians.last_name) -->
                    <div class="space-y-2 group">
                        <label for="librarian-last-name" class="block text-sm font-semibold text-[#334155]">
                            Last Name <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                id="librarian-last-name"
                                type="text"
                                wire:model.blur="lastName"
                                placeholder="e.g. Reyes"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('lastName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('lastName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Employee / School ID Number * (librarians.school_id_number) -->
                    <div class="space-y-2 group">
                        <label for="librarian-school-id" class="block text-sm font-semibold text-[#334155]">
                            Employee ID Number <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.374a4.125 4.125 0 0 0-6.338 0 .375.375 0 0 0 .285.626h5.768a.375.375 0 0 0 .285-.626Z" />
                                </svg>
                            </div>
                            <input
                                id="librarian-school-id"
                                type="text"
                                wire:model.blur="schoolIdNumber"
                                placeholder="e.g. 12-3456"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('schoolIdNumber') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('schoolIdNumber')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Number (librarians.contact_num - nullable) -->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label for="librarian-contact" class="block text-sm font-semibold text-[#334155]">
                                Contact Number
                            </label>
                            <span class="text-[11px] font-semibold text-slate-400">Optional</span>
                        </div>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </div>
                            <input
                                id="librarian-contact"
                                type="tel"
                                wire:model.blur="contactNum"
                                placeholder="e.g. 09178421902"
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('contactNum') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('contactNum')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address * (accounts.email) -->
                    <div class="space-y-2 group">
                        <label for="librarian-email" class="block text-sm font-semibold text-[#334155]">
                            Email <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input
                                id="librarian-email"
                                type="email"
                                wire:model.blur="email"
                                placeholder="e.g. librarian@pgpc.edu.ph"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('email') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('email')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Default Account Credentials Notice -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 rounded-2xl border border-[#DBEAFE] bg-[#EFF6FF] p-4 text-sm font-medium text-[#1E3A8A] flex items-start gap-3">
                        <div class="space-y-0.5 leading-relaxed">
                            <span class="font-bold text-[#102B70]">Default Login Credentials:</span>
                            <span class="text-slate-600">The account username is automatically assigned from the Employee ID (or email). Initial default password is generated as <span class="font-semibold text-[#102B70]">lastname{id}</span> (e.g. <span class="font-semibold text-[#102B70]">lastname23-1234</span>).</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 2: ROLE & PERMISSIONS              -->
            <!-- ========================================== -->
            @php
                $permissionCategories = [
                    'catalog' => [
                        'title' => 'Cataloging & Collection',
                        'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
                        'permissions' => [
                            'view_catalog' => [
                                'label' => 'Browse Catalog',
                                'description' => 'Search and inspect book details, classifications, and physical copies.',
                                'icon' => 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z',
                                'elevated' => false,
                            ],
                            'create_book' => [
                                'label' => 'Add Books & Copies',
                                'description' => 'Register new book titles, barcodes, and physical accession copies.',
                                'icon' => 'M12 4.5v15m7.5-7.5h-15',
                                'elevated' => false,
                            ],
                            'edit_book' => [
                                'label' => 'Edit Book Metadata',
                                'description' => 'Modify book titles, authors, publishers, categories, and shelf locations.',
                                'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125',
                                'elevated' => false,
                            ],
                            'delete_book' => [
                                'label' => 'Delete Book Copies',
                                'description' => 'Archive or delete physical copies and catalog records from the system.',
                                'icon' => 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',
                                'elevated' => true,
                            ],
                            'manage_catalog' => [
                                'label' => 'Full Catalog Control',
                                'description' => 'Complete catalog administration, bulk operations, and master metadata.',
                                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                                'elevated' => true,
                            ],
                        ],
                    ],
                    'circulation' => [
                        'title' => 'Circulation Desk',
                        'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
                        'permissions' => [
                            'manage_circulation' => [
                                'label' => 'Circulation Desk (Loans & Returns)',
                                'description' => 'Issue book borrowings, process returns (check-in), and inspect circulation logs.',
                                'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
                                'elevated' => false,
                            ],
                            'manage_fines' => [
                                'label' => 'Fines & Payments',
                                'description' => 'Assess overdue penalties, receive payments, and waive fine obligations.',
                                'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
                                'elevated' => false,
                            ],
                        ],
                    ],
                    'administration' => [
                        'title' => 'Student & Staff Management',
                        'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z',
                        'permissions' => [
                            'view_students' => [
                                'label' => 'View Student Records',
                                'description' => 'Search, filter, and view student profiles and library cards.',
                                'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
                                'elevated' => false,
                            ],
                            'create_students' => [
                                'label' => 'Register Students',
                                'description' => 'Add and register new student patron accounts into the system.',
                                'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.765Z',
                                'elevated' => false,
                            ],
                            'edit_students' => [
                                'label' => 'Edit Student Info',
                                'description' => 'Update student identity, department, year levels, and contact info.',
                                'icon' => 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125',
                                'elevated' => false,
                            ],
                            'delete_students' => [
                                'label' => 'Delete Students',
                                'description' => 'Soft-delete student patron accounts and revoke library borrowing access.',
                                'icon' => 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',
                                'elevated' => true,
                            ],
                            'manage_users' => [
                                'label' => 'Full Student Management',
                                'description' => 'Full administrative control over all student patron records and bulk operations.',
                                'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                                'elevated' => true,
                            ],
                            'manage_librarian' => [
                                'label' => 'Staff Management',
                                'description' => 'Create, edit, and manage library staff accounts and role privileges.',
                                'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.964 0a9 9 0 1 0-11.963 0m11.964 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                                'elevated' => true,
                            ],
                        ],
                    ],
                    'system' => [
                        'title' => 'System & Analytics',
                        'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                        'permissions' => [
                            'view_reports' => [
                                'label' => 'Analytics & Reports',
                                'description' => 'Generate and export circulation metrics, inventory audits, and activity summaries.',
                                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                                'elevated' => false,
                            ],
                            'manage_system' => [
                                'label' => 'System Settings',
                                'description' => 'Configure library rules, system parameters, policies, and danger zone actions.',
                                'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                                'elevated' => true,
                            ],
                        ],
                    ],
                ];
            @endphp
            <div class="space-y-4 pt-2">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-[#102B70]">Role &amp; Permissions</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Select a staff role to apply standard baseline privileges, or toggle fine-grained access below.</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0" x-show="role" x-cloak>
                        <span
                            x-show="isCustom"
                            x-transition.opacity
                            class="inline-flex items-center h-6 px-2.5 rounded-full bg-[#FCC719]/20 text-[#7A5A00] text-[11px] font-bold"
                        >
                            Customized
                        </span>
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full bg-[#EFF6FF] text-[#102B70] text-[11px] font-bold tabular-nums">
                            <span x-text="enabledCount"></span>&nbsp;of&nbsp;<span x-text="totalCount"></span>&nbsp;enabled
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-1">

                    <!-- User Type Selection & Role Summary Column -->
                    <div class="space-y-4 lg:sticky lg:top-6 self-start">
                        <div class="space-y-2 group">
                            <label for="librarian-role" class="block text-sm font-semibold text-[#334155]">
                                User Type <span class="text-[#EF4444] font-bold">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                </div>
                                <select
                                    id="librarian-role"
                                    x-model="role"
                                    x-on:change="applyRole()"
                                    required
                                    :class="role ? 'text-[#0F172A]' : 'text-[#94A3B8]'"
                                    class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white hover:border-[#CBD5E1] focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                                >
                                    <option value="" disabled selected>Select User Type</option>
                                    <option value="librarian" class="text-[#0F172A]">Librarian</option>
                                    <option value="head_librarian" class="text-[#0F172A]">Head Librarian</option>
                                </select>
                            </div>
                            @error('role')
                                <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role summary & Quick Actions Card -->
                        <div class="rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 sm:p-5 space-y-4">
                            <div>
                                <template x-if="!role">
                                    <p class="text-xs font-medium text-slate-500 leading-relaxed">
                                        Select a user type above to load preset permissions. You can then toggle specific privileges below.
                                    </p>
                                </template>
                                <template x-if="role === 'librarian'">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-[#0F172A]">Librarian Role</p>
                                        </div>
                                        <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">
                                            Operational staff access. Includes day-to-day circulation, catalog viewing, student browsing, and book management without deletion or system privileges.
                                        </p>
                                    </div>
                                </template>
                                <template x-if="role === 'librarian_head' || role === 'head_librarian'">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-[#0F172A]">Head Librarian Role</p>
                                        </div>
                                        <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">
                                            Full administrative authority. All cataloging, circulation, staff management, and system settings are enabled by default.
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <template x-if="role">
                                <div class="pt-3 border-t border-[#E2E8F0] space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-600">Active Permissions</span>
                                        <span class="font-bold text-[#102B70] tabular-nums" x-text="enabledCount + ' of ' + totalCount"></span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                        <div
                                            class="h-full bg-[#102B70] transition-all duration-300 rounded-full"
                                            :style="'width: ' + ((enabledCount / (totalCount || 1)) * 100) + '%'"
                                        ></div>
                                    </div>

                                    <div class="pt-2 flex flex-col gap-2">
                                        <button
                                            type="button"
                                            x-show="isCustom"
                                            x-cloak
                                            x-on:click="applyRole()"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#102B70] hover:text-[#0B225E] transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                            </svg>
                                            Reset to role defaults
                                        </button>
                                        <div class="flex items-center gap-2 pt-1">
                                            <button
                                                type="button"
                                                x-on:click="selectAll()"
                                                class="text-[11px] font-semibold text-slate-600 hover:text-[#102B70] hover:underline cursor-pointer"
                                            >
                                                Select All
                                            </button>
                                            <span class="text-slate-300">•</span>
                                            <button
                                                type="button"
                                                x-on:click="clearAll()"
                                                class="text-[11px] font-semibold text-slate-600 hover:text-red-600 hover:underline cursor-pointer"
                                            >
                                                Clear All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-2 space-y-5">
                        @foreach ($permissionCategories as $catKey => $category)
                            @php
                                $catKeysJson = json_encode(array_keys($category['permissions']));
                            @endphp
                            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:p-5 shadow-xs space-y-3.5 transition-all">
                                <!-- Category Header -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF] text-[#102B70]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $category['icon'] }}" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-[#0F172A]">{{ $category['title'] }}</h4>
                                            <p class="text-[11px] text-slate-500 font-medium hidden sm:block">{{ count($category['permissions']) }} permissions</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2.5 self-end sm:self-auto" x-show="role" x-cloak>
                                        <span
                                            class="text-[11px] font-bold tabular-nums px-2 py-0.5 rounded-full"
                                            :class="categoryActiveCount({{ $catKeysJson }}) > 0 ? 'bg-[#EFF6FF] text-[#102B70]' : 'bg-slate-100 text-slate-500'"
                                        >
                                            <span x-text="categoryActiveCount({{ $catKeysJson }})"></span>/{{ count($category['permissions']) }} active
                                        </span>
                                        <button
                                            type="button"
                                            x-on:click="toggleCategory({{ $catKeysJson }})"
                                            :disabled="!role"
                                            class="text-xs font-semibold text-[#102B70] hover:text-[#0B225E] hover:underline disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                            x-text="categoryActiveCount({{ $catKeysJson }}) === {{ count($category['permissions']) }} ? 'Deselect all' : 'Select all'"
                                        >
                                        </button>
                                    </div>
                                </div>

                                <!-- Items Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach ($category['permissions'] as $key => $perm)
                                        <div
                                            x-on:click="togglePermission('{{ $key }}')"
                                            :class="{
                                                'border-[#DBEAFE] bg-[#EFF6FF]/60': permissions.{{ $key }},
                                                'border-[#E2E8F0] bg-white hover:border-[#CBD5E1] hover:bg-slate-50/50': !permissions.{{ $key }},
                                                'cursor-pointer': role,
                                                'opacity-60 cursor-not-allowed': !role
                                            }"
                                            class="flex items-start gap-3 p-3.5 rounded-xl border transition-all select-none group/item"
                                        >
                                            <div
                                                :class="permissions.{{ $key }} ? 'bg-[#102B70] text-white shadow-xs' : 'bg-[#F8FAFC] text-[#94A3B8] border border-[#E2E8F0]'"
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors mt-0.5"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $perm['icon'] }}" />
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <p class="text-xs sm:text-sm font-semibold text-[#0F172A] leading-tight">{{ $perm['label'] }}</p>
                                                    @if (!empty($perm['elevated']))
                                                        <span class="inline-flex items-center h-4 px-1.5 rounded bg-[#FCC719]/20 text-[#7A5A00] text-[9px] font-bold uppercase tracking-wider">Elevated</span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-slate-500 font-medium mt-1 leading-snug">{{ $perm['description'] }}</p>
                                            </div>

                                            <!-- Switch -->
                                            <button
                                                type="button"
                                                role="switch"
                                                id="perm-{{ $key }}"
                                                :aria-checked="permissions.{{ $key }}.toString()"
                                                :disabled="!role"
                                                x-on:click.stop="togglePermission('{{ $key }}')"
                                                :class="permissions.{{ $key }} ? 'bg-[#102B70]' : 'bg-[#CBD5E1]'"
                                                class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus-visible:ring-4 focus-visible:ring-[#DBEAFE] disabled:cursor-not-allowed cursor-pointer"
                                                aria-label="Toggle {{ $perm['label'] }}"
                                            >
                                                <span
                                                    :class="permissions.{{ $key }} ? 'translate-x-6' : 'translate-x-1'"
                                                    class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                                ></span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 3: PROFILE PICTURE                 -->
            <!-- ========================================== -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Upload Profile Picture
                    </label>
                    <span class="text-[11px] font-semibold text-slate-400">Optional</span>
                </div>

                <input
                    type="file"
                    wire:model="photo"
                    x-ref="fileInput"
                    accept="image/png,image/jpeg,image/webp"
                    class="hidden"
                    @change="handleFileSelect($event)"
                >

                <div
                    id="librarian-photo-container"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    :class="isDragging ? 'border-[#102B70] bg-[#EFF6FF]' : 'border-[#E2E8F0] bg-[#F8FAFC] hover:border-[#CBD5E1]'"
                    class="relative flex flex-col sm:flex-row items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl border-2 border-dashed transition-all"
                >
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <template x-if="photoPreview">
                            <div class="relative h-16 w-16 shrink-0 rounded-2xl overflow-hidden border border-[#CBD5E1] group/photo">
                                <img :src="photoPreview" alt="Profile Preview" class="h-full w-full object-cover">
                                <button
                                    type="button"
                                    @click="removePhoto()"
                                    class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover/photo:opacity-100 transition-opacity"
                                    title="Remove Photo"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <template x-if="!photoPreview">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white border border-[#E2E8F0] text-[#94A3B8]">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                        </template>

                        <div class="min-w-0">
                            <template x-if="photoPreview">
                                <div>
                                    <p class="text-sm font-bold text-[#102B70] truncate max-w-[200px] sm:max-w-[260px]" x-text="photoName"></p>
                                    <p class="text-xs text-slate-500 font-medium" x-text="photoSize"></p>
                                </div>
                            </template>
                            <template x-if="!photoPreview">
                                <div>
                                    <p class="text-sm font-semibold text-[#0F172A]">Drag and drop staff photo here</p>
                                    <p class="text-xs text-slate-400 font-medium mt-0.5">PNG, JPG or WEBP (Max 5.0 MB)</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-stretch sm:self-auto justify-end">
                        <template x-if="photoPreview">
                            <button
                                type="button"
                                @click="removePhoto()"
                                class="h-10 px-3.5 rounded-xl border border-red-200 bg-white hover:bg-red-50 text-red-600 text-xs font-semibold transition-colors"
                            >
                                Remove
                            </button>
                        </template>

                        <button
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="inline-flex h-11 items-center justify-center gap-2 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-xs font-bold transition-all focus:outline-none focus:ring-2 focus:ring-[#102B70] active:scale-[0.98]"
                        >
                            <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span x-text="photoPreview ? 'Change Photo' : 'Upload Photo'">Upload Photo</span>
                        </button>
                    </div>
                    @error('photo')
                        <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 3: BOTTOM FORM ACTIONS             -->
            <!-- ========================================== -->
            <div class="pt-6 border-t border-[#F1F5F9] flex flex-col-reverse sm:flex-row items-center justify-end gap-3.5">
                <a
                    href="{{ route((request()->is('librarian*') ? 'librarian.' : 'admin.') . 'user-management', ['type' => 'librarian']) }}"
                    wire:navigate
                    class="w-full sm:w-auto h-12 px-7 rounded-2xl border border-[#E2E8F0] bg-white hover:bg-slate-100 text-[#334155] hover:text-[#0F172A] text-sm font-semibold transition-colors flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="w-full sm:w-auto h-12 px-9 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold transition-all flex items-center justify-center gap-2 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Add Librarian</span>
                    <span wire:loading wire:target="save">Saving Librarian...</span>
                </button>
            </div>

        </form>
    </div>
</div>
