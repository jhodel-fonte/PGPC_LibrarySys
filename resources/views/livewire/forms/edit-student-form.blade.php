@php
    $collegePrograms = config('settings.college.programs', [
        'BSCS' => 'Bachelor of Science in Computer Science',
        'BS Crim' => 'Bachelor of Science in Criminology',
        'BSMA' => 'Bachelor of Science in Management Accounting',
        'BPA' => 'Bachelor of Public Administration',
    ]);
@endphp

<div
    x-data="{
        photoPreview: @js($existingPhotoUrl),
        photoName: '',
        photoSize: '',
        isDragging: false,
        menuOpen: false,

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
        }
    }"
    class="font-sans antialiased text-[#0F172A]"
>
    <!-- Main Form Card Container -->
    <div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-4 sm:p-6 lg:p-8 shadow-[0_8px_30px_rgba(16,43,112,0.04)]">
        
        <!-- Top Card Header: Title & Back Link -->
        <div class="flex items-start justify-between border-b border-[#F1F5F9] pb-5">
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Edit Student Information <span class="text-[11px] font-semibold text-[#EF4444]">* Required</span></h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                        Update student details, academic record, and library standing.
                    </p>
                </div>
            </div>

            <!-- More Actions Dropdown -->
            <div class="relative shrink-0" @click.outside="menuOpen = false">
                <button
                    type="button"
                    @click="menuOpen = !menuOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:border-[#102B70]/30 hover:bg-[#EFF6FF] hover:text-[#102B70] transition-colors focus:outline-none focus:ring-2 focus:ring-[#102B70]"
                    title="Form Options"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="12" cy="12" r="1.25" fill="currentColor"></circle>
                        <circle cx="19" cy="12" r="1.25" fill="currentColor"></circle>
                        <circle cx="5" cy="12" r="1.25" fill="currentColor"></circle>
                    </svg>
                </button>

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
                    <a
                        href="{{ route((request()->is('librarian*') ? 'librarian.' : 'admin.') . 'user-details', $this->encryptedId) }}"
                        wire:navigate
                        class="flex w-full items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        <span>Cancel Editing</span>
                    </a>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-9 pt-4">

            <!-- Global Form Error & Validation Banner -->
            @if($errorMessage || $errors->any())
                <div class="rounded-2xl border border-[#FECACA] bg-[#FEF2F2] p-4 sm:p-5 shadow-[0_4px_20px_rgba(239,68,68,0.06)] animate-fade-in transition-all">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 rounded-lg bg-[#EF4444] p-1 text-white shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-[#B91C1C]">Unable to update student information</h4>
                                <p class="text-xs text-[#EF4444] mt-0.5 font-medium">
                                    {{ $errorMessage ?: 'Please resolve the highlighted validation errors below before submitting.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- SECTION 1: PERSONAL IDENTITY INFORMATION   -->
            <!-- ========================================== -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                    <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
                    <h3 class="text-base font-bold text-[#102B70]">Personal Information</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
                    
                    <!-- First Name * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            First Name <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.blur="firstName"
                                placeholder="e.g. John"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('firstName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('firstName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Middle Name (Optional) -->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-[#334155]">
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
                                type="text"
                                wire:model.blur="middleName"
                                placeholder="e.g. Michael"
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('middleName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('middleName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Last Name <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.blur="lastName"
                                placeholder="e.g. Doe"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('lastName') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('lastName')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Gender <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="10" cy="11" r="5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h4v4m0-4-4.5 4.5M10 16v5m-2.5-2.5h5" />
                                </svg>
                            </div>
                            <select
                                wire:model.live="gender"
                                required
                                class="h-14 w-full pl-11 pr-11 rounded-2xl border bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('gender') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }} {{ empty($gender) ? 'text-[#94A3B8]' : 'text-[#0F172A]' }}"
                            >
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male" class="text-[#0F172A]">Male</option>
                                <option value="Female" class="text-[#0F172A]">Female</option>
                            </select>
                        </div>
                        @error('gender')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date Of Birth (mm-yyyy)-->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-[#334155]">
                                Date of Birth
                            </label>
                        </div>
                        <div class="relative flex items-center pgpc-date-wrapper w-full @error('dateOfBirth') has-error @enderror">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                                </svg>
                            </div>
                            <div class="w-full">
                                <x-date
                                    wire:model.live="dateOfBirth"
                                    month-year-only
                                    :min-year="1920"
                                    :max-year="(int) date('Y')"
                                    :max-date="now()->endOfMonth()->format('Y-m-d')"
                                    placeholder="Select month and year"
                                    invalidate
                                />
                            </div>
                        </div>
                        @error('dateOfBirth')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Number -->
                    <div class="space-y-2 group">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-[#334155]">
                                Contact Number
                            </label>
                        </div>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </div>
                            <input
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

                    <!-- Email Address * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Email Address <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                wire:model.blur="email"
                                placeholder="e.g. student@pgpc.edu.ph"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('email') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('email')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 2: ACADEMIC & ENROLLMENT DETAILS   -->
            <!-- ========================================== -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                    <div class="h-6 w-1 bg-[#102B70] rounded-full"></div>
                    <h3 class="text-base font-bold text-[#102B70]">Academic & Enrollment Details</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-1">
                    
                    <!-- School ID Number * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            School ID Number <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.374a4.125 4.125 0 0 0-6.338 0 .375.375 0 0 0 .285.626h5.768a.375.375 0 0 0 .285-.626Z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.blur="schoolIdNumber"
                                placeholder="e.g. 2024-00123"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('schoolIdNumber') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('schoolIdNumber')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Program * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Degree Program <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <select
                                wire:model.live="program"
                                required
                                class="h-14 w-full pl-11 pr-11 rounded-2xl border bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('program') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }} {{ empty($program) ? 'text-[#94A3B8]' : 'text-[#0F172A]' }}"
                            >
                                <option value="" disabled selected>Select Program</option>
                                @foreach($collegePrograms as $code => $title)
                                    <option value="{{ $code }} - {{ $title }}" class="text-[#0F172A]">{{ $code }} - {{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                        @error('program')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Year Level * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Year Level <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            <select
                                wire:model.live="yearLevel"
                                required
                                class="h-14 w-full pl-11 pr-11 rounded-2xl border bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('yearLevel') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }} {{ empty($yearLevel) ? 'text-[#94A3B8]' : 'text-[#0F172A]' }}"
                            >
                                <option value="" disabled selected>Select Year Level</option>
                                <option value="1st Year" class="text-[#0F172A]">1st Year</option>
                                <option value="2nd Year" class="text-[#0F172A]">2nd Year</option>
                                <option value="3rd Year" class="text-[#0F172A]">3rd Year</option>
                                <option value="4th Year" class="text-[#0F172A]">4th Year</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                        @error('yearLevel')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enrollment Status -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Enrollment Status
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <select
                                wire:model.live="enrollmentStatus"
                                class="h-14 w-full pl-11 pr-11 rounded-2xl border bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('enrollmentStatus') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }} text-[#0F172A]"
                            >
                                <option value="Regular" class="text-[#0F172A]">Regular</option>
                                <option value="Irregular" class="text-[#0F172A]">Irregular</option>
                                <option value="Transferee" class="text-[#0F172A]">Transferee</option>
                                <option value="Returnee" class="text-[#0F172A]">Returnee</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                        @error('enrollmentStatus')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enrollment Year * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Enrollment Year <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                                </svg>
                            </div>
                            <input
                                type="number"
                                wire:model.blur="enrollmentYear"
                                min="2000"
                                max="{{ (int) date('Y') + 1 }}"
                                placeholder="e.g. {{ date('Y') }}"
                                required
                                class="h-14 w-full pl-11 pr-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('enrollmentYear') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                            >
                        </div>
                        @error('enrollmentYear')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Library Standing (Status) * -->
                    <div class="space-y-2 group">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Library Standing <span class="text-[#EF4444] font-bold">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <select
                                wire:model.live="libraryStatus"
                                required
                                class="h-14 w-full pl-11 pr-11 rounded-2xl border bg-[#F8FAFC] text-sm font-medium outline-none appearance-none cursor-pointer transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] {{ $errors->has('libraryStatus') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }} text-[#0F172A]"
                            >
                                <option value="1" class="text-[#0F172A]">Good Standing</option>
                                <option value="2" class="text-[#0F172A]">With Overdues</option>
                                <option value="3" class="text-[#0F172A]">Suspended</option>
                                <option value="4" class="text-[#0F172A]">Barred</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                        @error('libraryStatus')
                            <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 3: REMARKS & PROFILE PICTURE       -->
            <!-- ========================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                
                <!-- Notes & Remarks -->
                <div class="space-y-2 group">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Library Notes & Remarks
                        </label>
                        <span class="text-[11px] font-semibold text-slate-400">Max 300 chars</span>
                    </div>
                    <div class="relative">
                        <textarea
                            wire:model.blur="note"
                            rows="4"
                            maxlength="300"
                            placeholder="Add administrative notes, library privileges, special requirements..."
                            class="w-full h-[120px] p-4 rounded-2xl border bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:bg-white focus:bg-white focus:ring-4 focus:ring-[#EFF6FF] resize-none {{ $errors->has('note') ? 'border-[#EF4444] focus:border-[#EF4444]' : 'border-[#E2E8F0] hover:border-[#CBD5E1] focus:border-[#102B70]' }}"
                        ></textarea>
                    </div>
                    @error('note')
                        <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Picture Drag & Drop Zone -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Profile Picture
                        </label>
                        <span class="text-[11px] font-semibold text-slate-400">Optional</span>
                    </div>

                    <!-- Dropzone Container -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="isDragging ? 'border-[#102B70] bg-[#EFF6FF]/60 ring-4 ring-[#EFF6FF]' : 'border-[#E2E8F0] bg-[#F8FAFC] hover:bg-white hover:border-[#CBD5E1]'"
                        class="relative rounded-2xl border-2 border-dashed px-4 py-3 sm:px-5 transition-all duration-150 flex items-center justify-between gap-4 h-[120px]"
                    >
                        <input
                            type="file"
                            x-ref="fileInput"
                            wire:model="photo"
                            @change="handleFileSelect($event)"
                            accept="image/png,image/jpeg,image/webp"
                            class="hidden"
                        >

                        <!-- Left & Center: Thumbnail + Info in one flex item -->
                        <div class="flex items-center gap-3.5 min-w-0 flex-1">
                            <!-- Left: Photo Preview or Placeholder Icon -->
                            <div class="relative shrink-0">
                                <template x-if="photoPreview">
                                    <div class="relative h-14 w-14 rounded-2xl overflow-hidden border border-[#E2E8F0] shadow-xs">
                                        <img :src="photoPreview" alt="Profile preview" class="h-full w-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!photoPreview">
                                    <div class="h-14 w-14 rounded-2xl bg-white border border-[#E2E8F0] text-[#94A3B8] flex items-center justify-center shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Center: Instructions & Info -->
                            <div class="min-w-0 flex-1">
                                <template x-if="photoPreview">
                                    <div>
                                        <p class="text-sm font-bold text-[#102B70] truncate" x-text="photoName || 'Current Profile Picture'"></p>
                                        <p class="text-xs text-slate-500 font-medium mt-0.5 truncate" x-text="photoSize || 'Uploaded file'"></p>
                                    </div>
                                </template>
                                <template x-if="!photoPreview">
                                    <div>
                                        <p class="text-sm font-semibold text-[#0F172A] truncate">Drag & drop photo here</p>
                                        <p class="text-xs text-slate-400 font-medium mt-0.5 truncate">PNG, JPG, WEBP (Max 5MB)</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Right: Action Buttons in same row -->
                        <div class="flex items-center gap-2 shrink-0">
                            <template x-if="photoPreview">
                                <button
                                    type="button"
                                    @click="removePhoto()"
                                    class="h-10 px-3 rounded-xl border border-red-200 bg-white hover:bg-red-50 text-red-600 text-xs font-semibold transition-colors cursor-pointer"
                                >
                                    Remove
                                </button>
                            </template>

                            <button
                                type="button"
                                @click="$refs.fileInput.click()"
                                class="inline-flex h-10 items-center justify-center gap-1.5 px-3.5 sm:px-4 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-xs font-bold transition-all shadow-xs focus:outline-none focus:ring-2 focus:ring-[#102B70] active:scale-[0.98] cursor-pointer"
                            >
                                <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span x-text="photoPreview ? 'Change' : 'Upload'">Upload</span>
                            </button>
                        </div>
                    </div>
                    @error('photo')
                        <p class="text-xs text-[#EF4444] font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- ========================================== -->
            <!-- SECTION 4: BOTTOM FORM ACTIONS             -->
            <!-- ========================================== -->
            <div class="pt-6 border-t border-[#F1F5F9] flex flex-col-reverse sm:flex-row items-center justify-end gap-3.5">
                <!-- Cancel Button -->
                <a
                    href="{{ route((request()->is('librarian*') ? 'librarian.' : 'admin.') . 'user-details', $this->encryptedId) }}"
                    wire:navigate
                    class="w-full sm:w-auto h-12 px-7 rounded-2xl border border-[#E2E8F0] bg-white hover:bg-slate-100 text-[#334155] hover:text-[#0F172A] text-sm font-semibold transition-colors shadow-xs flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                >
                    Cancel
                </a>

                <!-- Update Student Button with Loading State -->
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="w-full sm:w-auto h-12 px-9 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Update Student</span>
                    <span wire:loading wire:target="save">Saving Changes...</span>
                </button>
            </div>

        </form>

    </div>
</div>
