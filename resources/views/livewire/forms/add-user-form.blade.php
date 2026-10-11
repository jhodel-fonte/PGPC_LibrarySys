<div
    x-data="{
        firstName: '',
        lastName: '',
        gender: '',
        dob: '',
        roll: '',
        bloodGroup: '',
        religion: '',
        email: '',
        studentClass: '',
        section: '',
        admissionId: '',
        phone: '',
        bio: '',
        photoPreview: null,
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
        },

        fillSampleData() {
            this.firstName = 'Maria Clara';
            this.lastName = 'Santos';
            this.gender = 'Female';
            this.dob = '2005-08-16';
            this.roll = '2026-04192';
            this.bloodGroup = 'O+';
            this.religion = 'Roman Catholic';
            this.email = 'mariaclara.santos@pgpc.edu.ph';
            this.studentClass = 'BSIT - 2nd Year';
            this.section = 'Section 2-A';
            this.admissionId = 'ADM-2026-088';
            this.phone = '+63 917 842 1902';
            this.bio = 'Dean\'s Lister, active student assistant for the PGPC Library tech committee and reader of historical fiction.';
            this.menuOpen = false;
        },

        clearForm() {
            this.firstName = '';
            this.lastName = '';
            this.gender = '';
            this.dob = '';
            this.roll = '';
            this.bloodGroup = '';
            this.religion = '';
            this.email = '';
            this.studentClass = '';
            this.section = '';
            this.admissionId = '';
            this.phone = '';
            this.bio = '';
            this.removePhoto();
            this.menuOpen = false;
        }
    }"
    class="font-sans antialiased text-[#0F172A]"
>
    <!-- Main Card Container -->
    <div class="relative rounded-2xl md:rounded-3xl border border-[#E2E8F0] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_8px_30px_rgba(15,43,112,0.035)]">
        
        <!-- Top Card Header: Title & More Options -->
        <div class="flex items-start justify-between border-b border-[#F1F5F9] pb-6 mb-7">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#102B70]">Add New Students</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                    Provide complete student credentials, academic standing, and library membership records.
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
                        @click="fillSampleData()"
                        class="flex w-full items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-[#EFF6FF] hover:text-[#102B70] transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                        <span>Fill with Sample Data</span>
                    </button>
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
                        href="{{ route('admin.user-management') }}"
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

        <!-- Form Fields Grid (3 columns on desktop, 2 on tablet, 1 on mobile) -->
        <form @submit.prevent="" class="space-y-6">

            <!-- Primary 3-Column Field Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                
                <!-- ROW 1, COL 1: First Name * -->
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
                            x-model="firstName"
                            placeholder="e.g. Juan"
                            required
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 1, COL 2: Last Name * -->
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
                            x-model="lastName"
                            placeholder="e.g. Dela Cruz"
                            required
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 1, COL 3: Gender * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Gender <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v10m-5-5h10" />
                            </svg>
                        </div>
                        <select
                            x-model="gender"
                            required
                            class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] outline-none appearance-none cursor-pointer transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            :class="gender === '' ? 'text-[#94A3B8]' : 'text-[#0F172A]'"
                        >
                            <option value="" disabled selected>Please Select Gender</option>
                            <option value="Male" class="text-[#0F172A]">Male</option>
                            <option value="Female" class="text-[#0F172A]">Female</option>
                            <option value="Other" class="text-[#0F172A]">Prefer not to say</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ROW 2, COL 1: Date Of Birth * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Date Of Birth <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                            </svg>
                        </div>
                        <input
                            type="date"
                            x-model="dob"
                            required
                            class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            :class="dob === '' ? 'text-[#94A3B8]' : 'text-[#0F172A]'"
                        >
                        <!-- Calendar icon indicator on right side matching reference image -->
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ROW 2, COL 2: Roll -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Roll
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5m-13.5 7.5h13.5M9.75 3.75v16.5m4.5-16.5v16.5" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            x-model="roll"
                            placeholder="e.g. 2026-00124"
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 2, COL 3: Blood Group * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Blood Group <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a6 6 0 0 0 6-6c0-4-6-11-6-11S6 11 6 15a6 6 0 0 0 6 6Z" />
                            </svg>
                        </div>
                        <select
                            x-model="bloodGroup"
                            required
                            class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] outline-none appearance-none cursor-pointer transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            :class="bloodGroup === '' ? 'text-[#94A3B8]' : 'text-[#0F172A]'"
                        >
                            <option value="" disabled selected>Please Select Blood Group</option>
                            <option value="A+" class="text-[#0F172A]">A+</option>
                            <option value="A-" class="text-[#0F172A]">A-</option>
                            <option value="B+" class="text-[#0F172A]">B+</option>
                            <option value="B-" class="text-[#0F172A]">B-</option>
                            <option value="AB+" class="text-[#0F172A]">AB+</option>
                            <option value="AB-" class="text-[#0F172A]">AB-</option>
                            <option value="O+" class="text-[#0F172A]">O+</option>
                            <option value="O-" class="text-[#0F172A]">O-</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ROW 3, COL 1: Religion * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Religion <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            x-model="religion"
                            placeholder="e.g. Roman Catholic, Christian, Islam"
                            required
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 3, COL 2: E-Mail -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        E-Mail
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <input
                            type="email"
                            x-model="email"
                            placeholder="e.g. student@pgpc.edu.ph"
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 3, COL 3: Class * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Class <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0 .17.012.338" />
                            </svg>
                        </div>
                        <select
                            x-model="studentClass"
                            required
                            class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] outline-none appearance-none cursor-pointer transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            :class="studentClass === '' ? 'text-[#94A3B8]' : 'text-[#0F172A]'"
                        >
                            <option value="" disabled selected>Please Select Class</option>
                            <optgroup label="College Programs">
                                <option value="BSIT - 1st Year" class="text-[#0F172A]">BS Information Technology - 1st Year</option>
                                <option value="BSIT - 2nd Year" class="text-[#0F172A]">BS Information Technology - 2nd Year</option>
                                <option value="BSIT - 3rd Year" class="text-[#0F172A]">BS Information Technology - 3rd Year</option>
                                <option value="BSIT - 4th Year" class="text-[#0F172A]">BS Information Technology - 4th Year</option>
                                <option value="BSCS - 1st Year" class="text-[#0F172A]">BS Computer Science - 1st Year</option>
                                <option value="BSCS - 2nd Year" class="text-[#0F172A]">BS Computer Science - 2nd Year</option>
                                <option value="BSBA - 1st Year" class="text-[#0F172A]">BS Business Administration - 1st Year</option>
                                <option value="BSHM - 1st Year" class="text-[#0F172A]">BS Hospitality Management - 1st Year</option>
                            </optgroup>
                            <optgroup label="Basic Education / SHS">
                                <option value="Grade 11 - STEM" class="text-[#0F172A]">Grade 11 - STEM</option>
                                <option value="Grade 11 - ABM" class="text-[#0F172A]">Grade 11 - ABM</option>
                                <option value="Grade 12 - STEM" class="text-[#0F172A]">Grade 12 - STEM</option>
                                <option value="Grade 12 - HUMSS" class="text-[#0F172A]">Grade 12 - HUMSS</option>
                            </optgroup>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ROW 4, COL 1: Section * -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Section <span class="text-[#EF4444] font-bold">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                        </div>
                        <select
                            x-model="section"
                            required
                            class="h-14 w-full pl-11 pr-11 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] outline-none appearance-none cursor-pointer transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                            :class="section === '' ? 'text-[#94A3B8]' : 'text-[#0F172A]'"
                        >
                            <option value="" disabled selected>Please Select Section</option>
                            <option value="Section A" class="text-[#0F172A]">Section A</option>
                            <option value="Section B" class="text-[#0F172A]">Section B</option>
                            <option value="Section C" class="text-[#0F172A]">Section C</option>
                            <option value="Section 1-A" class="text-[#0F172A]">Section 1-A</option>
                            <option value="Section 1-B" class="text-[#0F172A]">Section 1-B</option>
                            <option value="Section 2-A" class="text-[#0F172A]">Section 2-A</option>
                            <option value="Section 2-B" class="text-[#0F172A]">Section 2-B</option>
                            <option value="Section 3-A" class="text-[#0F172A]">Section 3-A</option>
                            <option value="Section 4-A" class="text-[#0F172A]">Section 4-A</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#94A3B8]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- ROW 4, COL 2: Admission ID -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Admission ID
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.374a4.125 4.125 0 0 0-6.338 0 .375.375 0 0 0 .285.626h5.768a.375.375 0 0 0 .285-.626Z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            x-model="admissionId"
                            placeholder="e.g. ADM-2026-0042"
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

                <!-- ROW 4, COL 3: Phone -->
                <div class="space-y-2 group">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Phone
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#94A3B8] group-focus-within:text-[#102B70] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                        </div>
                        <input
                            type="tel"
                            x-model="phone"
                            placeholder="e.g. +63 912 345 6789"
                            class="h-14 w-full pl-11 pr-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        >
                    </div>
                </div>

            </div>

            <!-- ROW 5: Short BIO & Upload Profile Picture -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 pt-2">
                
                <!-- Short BIO (Col 1) -->
                <div class="space-y-2 group lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-semibold text-[#334155]">
                            Short BIO
                        </label>
                        <span class="text-[11px] font-medium text-slate-400" x-text="(bio ? bio.length : 0) + ' / 300'">0 / 300</span>
                    </div>
                    <div class="relative">
                        <textarea
                            x-model="bio"
                            maxlength="300"
                            rows="4"
                            placeholder="Brief student background, academic track, or library notes..."
                            class="w-full p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-medium text-[#0F172A] placeholder:text-[#94A3B8] outline-none resize-none transition-all hover:border-[#CBD5E1] hover:bg-white focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF]"
                        ></textarea>
                    </div>
                </div>

                <!-- Upload Profile Picture (Cols 2-3 on desktop) -->
                <div class="space-y-2 lg:col-span-2">
                    <label class="block text-sm font-semibold text-[#334155]">
                        Upload Profile Picture
                    </label>
                    
                    <!-- Hidden File Input -->
                    <input
                        type="file"
                        x-ref="fileInput"
                        accept="image/png,image/jpeg,image/webp"
                        class="hidden"
                        @change="handleFileSelect($event)"
                    >

                    <!-- Upload Zone & Live Preview Box -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="isDragging ? 'border-[#102B70] bg-[#EFF6FF]' : 'border-[#E2E8F0] bg-[#F8FAFC] hover:border-[#CBD5E1]'"
                        class="relative flex flex-col sm:flex-row items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl border-2 border-dashed transition-all"
                    >
                        <!-- Left: Upload Icon / Avatar Preview -->
                        <div class="flex items-center gap-4 w-full sm:w-auto">
                            <!-- Image Thumbnail Preview or Empty State -->
                            <template x-if="photoPreview">
                                <div class="relative h-16 w-16 shrink-0 rounded-2xl overflow-hidden border border-[#CBD5E1] shadow-xs group/photo">
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
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white border border-[#E2E8F0] text-[#94A3B8] shadow-xs">
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
                                        <p class="text-sm font-semibold text-[#0F172A]">Drag and drop your photo here</p>
                                        <p class="text-xs text-slate-400 font-medium mt-0.5">PNG, JPG or WEBP (Max 5.0 MB)</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Right: Action Button -->
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
                                class="inline-flex h-11 items-center justify-center gap-2 px-5 rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 hover:border-[#102B70] text-[#102B70] text-xs font-bold transition-all shadow-xs focus:outline-none focus:ring-2 focus:ring-[#102B70] active:scale-[0.98]"
                            >
                                <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span x-text="photoPreview ? 'Change Photo' : 'Upload File'">Upload File</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Action Row: Cancel and Save (Aligned to Bottom Right) -->
            <div class="pt-6 border-t border-[#F1F5F9] flex flex-col-reverse sm:flex-row items-center justify-end gap-3.5">
                <!-- Cancel Button -->
                <a
                    href="{{ route('admin.user-management') }}"
                    wire:navigate
                    class="w-full sm:w-auto h-12 px-7 rounded-2xl border border-[#E2E8F0] bg-white hover:bg-slate-100 text-[#334155] hover:text-[#0F172A] text-sm font-semibold transition-colors shadow-xs flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400"
                >
                    Cancel
                </a>

                <!-- Save Button (Primary Navy) -->
                <button
                    type="button"
                    @click="
                        if (!firstName || !lastName || !gender || !dob || !bloodGroup || !religion || !studentClass || !section) {
                            alert('Please fill in all required fields marked with *');
                        } else {
                            alert('Frontend Preview: Student validated successfully! Ready to connect backend persistence.');
                        }
                    "
                    class="w-full sm:w-auto h-12 px-9 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#EFF6FF]"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>Save</span>
                </button>
            </div>

        </form>

    </div>
</div>
