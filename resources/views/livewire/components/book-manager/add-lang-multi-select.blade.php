<div class="space-y-1.5"
     wire:key="languages-combobox-wrapper"
     x-data="{
        selectedLanguages: @entangle('selected').live,
        preloads: {{ Js::from($languages->map(fn($l) => ['id' => $l->id, 'name' => $l->lang])->values()) }},
        query: '',
        isOpen: false,
        highlightedIndex: 0,

        init() {
            if (!Array.isArray(this.selectedLanguages)) this.selectedLanguages = [];
        },

        isSelected(name) {
            return (this.selectedLanguages || []).some(l => l.toLowerCase() === String(name).toLowerCase());
        },

        get filteredLanguages() {
            const q = this.query.trim().toLowerCase();
            return (this.preloads || []).filter(lang => {
                const notSelected = !this.isSelected(lang.name);
                if (!q) return notSelected;
                return notSelected && lang.name.toLowerCase().includes(q);
            });
        },

        get canCreateNew() {
            const q = this.query.trim().toLowerCase();
            if (!q) return false;
            return !(this.preloads || []).some(l => l.name.toLowerCase() === q);
        },

        selectLanguage(lang) {
            const name = typeof lang === 'object' ? lang.name : lang;
            if (typeof lang === 'object' && lang.id && !(this.preloads || []).some(p => String(p.id) === String(lang.id))) {
                this.preloads.push({ id: lang.id, name: lang.name });
            }
            if (!this.isSelected(name)) {
                this.selectedLanguages = [...(this.selectedLanguages || []), name];
            }
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.langSearchInput?.focus());
        },

        removeLanguage(name) {
            this.selectedLanguages = (this.selectedLanguages || []).filter(l => l.toLowerCase() !== String(name).toLowerCase());
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.langSearchInput?.focus());
        },

        useAsNewLanguage() {
            const q = this.query.trim();
            if (!q) return;
            $wire.createAndSelectLanguage(q).then(newLang => {
                if (newLang && newLang.name) {
                    if (!(this.preloads || []).some(p => p.name.toLowerCase() === newLang.name.toLowerCase())) {
                        this.preloads.push(newLang);
                    }
                    if (!this.isSelected(newLang.name)) {
                        this.selectedLanguages = [...(this.selectedLanguages || []), newLang.name];
                    }
                }
            });
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = false;
            this.$nextTick(() => this.$refs.langSearchInput?.focus());
        },

        clearAll() {
            this.selectedLanguages = [];
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.langSearchInput?.focus());
        },

        handleBackspace() {
            if (this.query === '' && (this.selectedLanguages || []).length > 0) {
                const removed = this.selectedLanguages[this.selectedLanguages.length - 1];
                this.removeLanguage(removed);
            }
        },

        nextItem() {
            const total = this.filteredLanguages.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % total;
        },

        prevItem() {
            const total = this.filteredLanguages.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex - 1 + total) % total;
        },

        selectHighlighted() {
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (this.highlightedIndex < this.filteredLanguages.length) {
                this.selectLanguage(this.filteredLanguages[this.highlightedIndex]);
            } else if (this.canCreateNew) {
                this.useAsNewLanguage();
            }
        }
     }"
     @click.outside="isOpen = false"
>
    <div class="flex items-center justify-between">
        <label class="text-sm font-semibold text-[#334155]">Language</label>
        <template x-if="selectedLanguages.length > 0">
            <span class="text-xs font-semibold text-[#102B70]" x-text="selectedLanguages.length + ' selected'"></span>
        </template>
    </div>

    <!-- Relative Positioning Wrapper for Input & Dropdown -->
    <div class="relative">
        <!-- True Tokenized Tag-Input Container -->
        <div
            @click="$refs.langSearchInput?.focus()"
            class="min-h-[56px] w-full p-2.5 pr-20 rounded-2xl border border-[#E2E8F0] bg-white transition-all cursor-text flex flex-wrap items-center gap-1.5 focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF]"
            :class="{ 'border-[#102B70] ring-4 ring-[#EFF6FF]': isOpen }"
        >
            <!-- Globe / Language Icon -->
            <div class="pl-1 pr-0.5 text-slate-400 pointer-events-none shrink-0 flex items-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Selected Language Chips -->
            <template x-for="lang in selectedLanguages" :key="lang">
                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-lg text-xs font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] shadow-2xs group shrink-0 select-none animate-fade-in">
                    <span x-text="lang"></span>
                    <button
                        type="button"
                        @click.stop="removeLanguage(lang)"
                        class="w-4 h-4 rounded inline-flex items-center justify-center text-[#3B82F6] hover:bg-[#102B70] hover:text-white transition-colors duration-150"
                        title="Remove language"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </span>
            </template>

            <!-- Inline Continuing Filter / Search Input -->
            <input
                x-ref="langSearchInput"
                x-model="query"
                @focus="isOpen = true"
                @keydown.arrow-down.prevent="nextItem()"
                @keydown.arrow-up.prevent="prevItem()"
                @keydown.enter.prevent="selectHighlighted()"
                @keydown.escape="isOpen = false"
                @keydown.backspace="handleBackspace()"
                type="text"
                :placeholder="selectedLanguages.length === 0 ? 'Type to search or select languages...' : 'Add language...'"
                class="flex-1 min-w-[110px] h-8 bg-transparent border-0 border-none p-0 m-0 text-sm font-medium text-[#0F172A] placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-0 shadow-none"
                style="outline: none !important; box-shadow: none !important; border: none !important;"
            >

            <!-- Trailing Action Controls: Clear & Dropdown Toggle -->
            <div class="absolute right-3 top-3.5 flex items-center gap-1 shrink-0">
                <button
                    type="button"
                    x-show="selectedLanguages.length > 0 || (query && query.length > 0)"
                    x-cloak
                    @click.stop="clearAll()"
                    class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                    title="Clear selection"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <button
                    type="button"
                    @click.stop="isOpen = !isOpen"
                    class="p-1 text-slate-400 hover:text-[#102B70] rounded-lg hover:bg-slate-100 transition-transform duration-200"
                    :class="{ 'rotate-180 text-[#102B70]': isOpen }"
                    title="Toggle languages list"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
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
            class="absolute z-50 left-0 right-0 top-full mt-1.5 bg-white border border-[#E2E8F0] rounded-2xl shadow-xl overflow-hidden max-h-60 overflow-y-auto custom-scrollbar"
        >
            <!-- Header -->
            <div class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-50 border-b border-[#F1F5F9] flex items-center justify-between">
                <span>Languages in Catalog</span>
                <span x-text="filteredLanguages.length + ' available'"></span>
            </div>

            <!-- Available Results -->
            <template x-if="filteredLanguages.length > 0">
                <div class="py-1">
                    <template x-for="(lang, index) in filteredLanguages" :key="lang.id">
                        <div
                            @click="selectLanguage(lang)"
                            @mouseenter="highlightedIndex = index"
                            :class="{
                                'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === index,
                                'text-[#0F172A] hover:bg-slate-50': highlightedIndex !== index
                            }"
                            class="px-3.5 py-2.5 flex items-center justify-between cursor-pointer transition-colors text-sm font-medium border-b border-slate-50 last:border-0"
                        >
                            <div class="flex items-center gap-2.5">
                                <span x-text="lang.name"></span>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 px-2 py-0.5 rounded bg-slate-100 group-hover:bg-[#DBEAFE] group-hover:text-[#102B70]">
                                + Add
                            </span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Add New Language Option -->
            <template x-if="canCreateNew">
                <div
                    @click="useAsNewLanguage()"
                    @mouseenter="highlightedIndex = filteredLanguages.length"
                    :class="{ 'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === filteredLanguages.length }"
                    class="px-3.5 py-2.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between cursor-pointer hover:bg-[#EFF6FF] text-sm font-semibold text-[#102B70] transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xs font-bold shrink-0">
                            +
                        </div>
                        <span>Register language: "<span class="underline" x-text="query.trim()"></span>"</span>
                    </div>
                    <span class="text-xs font-semibold text-[#3B82F6] px-2 py-0.5 rounded bg-[#EFF6FF]">New Language</span>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="filteredLanguages.length === 0 && !canCreateNew">
                <div class="p-4 text-center text-xs text-slate-400 font-medium">
                    <span x-show="preloads.length > 0">All matching languages are already selected.</span>
                    <span x-show="preloads.length === 0">No languages found in catalog.</span>
                </div>
            </template>
        </div>
    </div>

    <p class="text-xs text-slate-400 font-medium">Select one or more languages for this publication.</p>
    @error('selected') <span class="text-xs font-semibold text-[#EF4444] block">{{ $message }}</span> @enderror
</div>
