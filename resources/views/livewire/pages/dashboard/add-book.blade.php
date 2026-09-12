<div class="bg-[#F8FAFC] min-h-full pb-12">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-6">

        <!-- Form Grid Container -->
        <form wire:submit.prevent="save" class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-4 xl:col-span-4 space-y-6">
                
                <!-- 1. INITIAL PHYSICAL COPY CARD -->
                <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 space-y-5 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
                        <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center font-bold shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#102B70]">Initial Physical Copy</h2>
                            <p class="text-xs text-slate-500 font-medium">Inventory tracking & placement</p>
                        </div>
                    </div>

                    <!-- Accession Number -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">
                            Accession Number <span class="text-[#EF4444]">*</span>
                        </label>
                        <input
                            wire:model="accessionNumber"
                            type="text"
                            placeholder="Required"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] font-mono placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('accessionNumber') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Barcode with inline Generate button -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Barcode</label>
                        <div class="flex rounded-xl border border-[#E2E8F0] overflow-hidden focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF] transition-all bg-white">
                            <input
                                wire:model="barcode"
                                type="text"
                                placeholder="Optional"
                                class="w-full h-12 px-4 bg-transparent text-sm font-semibold text-[#0F172A] font-mono placeholder-slate-400 outline-none border-0"
                            >
                            <button
                                type="button"
                                wire:click="generateBarcode"
                                class="px-4 h-12 bg-slate-50 hover:bg-slate-100 text-[#102B70] hover:text-[#0B225E] text-xs uppercase tracking-wider font-bold border-l border-[#E2E8F0] transition-colors shrink-0 select-none flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Generate</span>
                            </button>
                        </div>
                        @error('barcode') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Status</label>
                        <div class="relative">
                            <select
                                wire:model="status"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all appearance-none cursor-pointer"
                            >
                                <option value="Available">Available</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Reserved">Reserved</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('status') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Location / Shelf -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Location / Shelf</label>
                        <input
                            wire:model="location"
                            type="text"
                            placeholder="e.g. Shelf A1"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('location') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date Acquired -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Date Acquired</label>
                        <input
                            wire:model="dateAcquired"
                            type="date"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer"
                        >
                        @error('dateAcquired') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- 2. BOOK COVER CARD -->
                <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 space-y-4 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
                        <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center font-bold shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#102B70]">Book Cover</h2>
                            <p class="text-xs text-slate-500 font-medium">Catalog visual artwork</p>
                        </div>
                    </div>

                    <label class="block relative border-2 border-dashed border-[#CBD5E1] hover:border-[#102B70] rounded-2xl p-6 text-center cursor-pointer transition-all bg-[#F8FAFC] hover:bg-[#EFF6FF]/40 group">
                        <input
                            type="file"
                            wire:model="coverImage"
                            accept="image/png,image/jpeg,image/jpg,image/webp"
                            class="sr-only"
                        >
                        
                        @if($coverImage)
                            <div class="flex flex-col items-center">
                                <img src="{{ $coverImage->temporaryUrl() }}" class="h-36 w-28 object-cover rounded-xl border border-slate-200 shadow-md" alt="Cover Preview">
                                <span class="mt-3 text-xs font-bold text-[#102B70] group-hover:underline">Click to change cover image</span>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-2">
                                <div class="w-12 h-12 rounded-2xl bg-white border border-[#E2E8F0] shadow-sm flex items-center justify-center text-slate-400 group-hover:text-[#102B70] group-hover:border-[#102B70]/30 transition-all mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-[#0F172A] group-hover:text-[#102B70]">Click to upload or drag & drop</p>
                                <p class="text-xs text-slate-500 font-semibold mt-1">PNG, JPG, JPEG up to 2MB</p>
                            </div>
                        @endif
                    </label>

                    <p class="text-xs text-slate-500 leading-relaxed text-center">
                        A high-quality cover image helps users easily identify the book in the catalog.
                    </p>
                    @error('coverImage') <span class="text-xs font-bold text-[#EF4444] text-center block">{{ $message }}</span> @enderror
                </div>

            </div>

            <!-- ============================================== -->
            <!-- RIGHT COLUMN: Bibliographic Information        -->
            <!-- ============================================== -->
            <div class="lg:col-span-8 xl:col-span-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 lg:p-8 space-y-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                
                <div class="flex items-center gap-3 border-b border-[#F1F5F9] pb-4">
                    <div class="h-9 w-9 rounded-xl bg-[#EFF6FF] text-[#102B70] flex items-center justify-center font-bold shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#102B70]">Bibliographic Information</h2>
                        <p class="text-xs text-slate-500 font-medium">Core title, authorship, identifiers, and classification</p>
                    </div>
                </div>

                <!-- Book Title -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">
                        Book Title <span class="text-[#EF4444]">*</span>
                    </label>
                    <input
                        wire:model="bookTitle"
                        type="text"
                        placeholder="Enter the complete book title"
                        class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    >
                    @error('bookTitle') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                </div>

                <!-- Subtitle -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Subtitle</label>
                    <input
                        wire:model="subtitle"
                        type="text"
                        placeholder="Optional"
                        class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    >
                    @error('subtitle') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                </div>

                <!-- Authors Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                    <!-- Existing Author Dropdown -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Main Author (Existing)</label>
                        <div class="relative">
                            <select
                                wire:model="authorId"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all appearance-none cursor-pointer"
                            >
                                <option value="">-- Select Existing Author --</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}">{{ trim($author->first_name . ' ' . $author->last_name) }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('authorId') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Author Inputs Stack -->
                    <div class="space-y-2.5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">OR New Author Last Name</label>
                            <input
                                wire:model="newAuthorLastName"
                                type="text"
                                placeholder="If author not in list"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                            >
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">New Author First Name</label>
                            <input
                                wire:model="newAuthorFirstName"
                                type="text"
                                placeholder="Optional"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- ISBN & ISSN Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">ISBN</label>
                        <input
                            wire:model="isbn"
                            type="text"
                            placeholder="10 or 13-digit ISBN"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('isbn') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">ISSN</label>
                        <input
                            wire:model="issn"
                            type="text"
                            placeholder="Optional"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('issn') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Call Number & Classification Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">
                            Call Number <span class="text-[#EF4444]">*</span>
                        </label>
                        <input
                            wire:model="callNumber"
                            type="text"
                            placeholder="e.g. QA76.73.J38"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('callNumber') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Classification</label>
                        <input
                            wire:model="classification"
                            type="text"
                            placeholder="Optional"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('classification') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Publisher Row -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Publisher</label>
                    <input
                        wire:model="publisherName"
                        type="text"
                        placeholder="Type to search or enter a new publisher..."
                        class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    >
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        If the publisher does not exist, just type the name and it will be created automatically.
                    </p>
                    @error('publisherName') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                </div>

                <!-- Publication Year, Edition, Pages Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Publication Year</label>
                        <input
                            wire:model="publicationYear"
                            type="number"
                            placeholder="YYYY"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('publicationYear') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Edition</label>
                        <input
                            wire:model="edition"
                            type="text"
                            placeholder="e.g. 1st Edition"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('edition') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Pages</label>
                        <input
                            wire:model="pages"
                            type="number"
                            placeholder="e.g. 350"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('pages') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Categories, Language & Copyright Year Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Categories Multi-select Listbox -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Categories</label>
                        <select
                            wire:model="selectedCategories"
                            multiple
                            class="w-full h-[132px] p-2.5 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all custom-scrollbar"
                        >
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" class="py-1 px-2 rounded-lg hover:bg-[#EFF6FF] cursor-pointer">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 font-medium mt-1">Hold Ctrl/Cmd to select multiple</p>
                        @error('selectedCategories') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Language & Copyright Stack -->
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Language</label>
                            <input
                                wire:model="language"
                                type="text"
                                placeholder="e.g. English"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                            >
                            @error('language') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Copyright Year</label>
                            <input
                                wire:model="copyrightYear"
                                type="number"
                                placeholder="YYYY"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                            >
                            @error('copyrightYear') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Book Description -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Book Description</label>
                    <textarea
                        wire:model="bookDescription"
                        rows="3"
                        placeholder="Write a brief summary..."
                        class="w-full p-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    ></textarea>
                    @error('bookDescription') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#334155]">Notes</label>
                    <textarea
                        wire:model="notes"
                        rows="2"
                        placeholder="Additional notes..."
                        class="w-full p-4 rounded-xl border border-[#E2E8F0] bg-white text-sm font-semibold text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                    ></textarea>
                    @error('notes') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                </div>

                <!-- Bottom Action Buttons Bar -->
                <div class="pt-6 border-t border-[#F1F5F9] flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.book-management.index') }}"
                        wire:navigate
                        class="px-5 h-11 rounded-xl border border-[#E2E8F0] bg-white hover:bg-slate-50 text-slate-700 text-xs uppercase tracking-wider font-bold transition-colors flex items-center justify-center focus:outline-none"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 h-11 rounded-xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs uppercase tracking-wider font-bold transition-all shadow-sm flex items-center gap-2 focus:outline-none disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#FCC719]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Book Details</span>
                        </span>
                        <span wire:loading.flex wire:target="save" class="items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Saving...</span>
                        </span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
