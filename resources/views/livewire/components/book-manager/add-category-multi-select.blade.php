<div class="space-y-1.5"
     wire:key="categories-combobox-wrapper"
     x-data="{
        selectedIds: @entangle('selected').live,
        preloads: {{ Js::from($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->code])->values()) }},
        query: '',
        isOpen: false,
        highlightedIndex: 0,
        lastAutoDetectedId: null,

        init() {
            if (!Array.isArray(this.selectedIds)) this.selectedIds = [];
        },

        isSelected(id) {
            return (this.selectedIds || []).some(s => String(s) === String(id));
        },

        get selectedItems() {
            return (this.selectedIds || [])
                .map(id => (this.preloads || []).find(c => String(c.id) === String(id)))
                .filter(Boolean);
        },

        get filteredCategories() {
            const q = this.query.trim().toLowerCase();
            const list = (this.preloads || []).filter(cat => !this.isSelected(cat.id));
            if (!q) return list;

            const getScore = (cat) => {
                const code = (cat.code || '').toLowerCase();
                const name = (cat.name || '').toLowerCase();

                if (code === q) return 1;
                if (code.startsWith(q)) return 2;
                if (code.includes(q)) return 3;
                if (name.startsWith(q)) return 4;
                if (name.split(/\s+/).some(word => word.startsWith(q))) return 5;
                if (name.includes(q)) return 6;
                return 99;
            };

            return list
                .filter(cat => {
                    const matchesName = cat.name && cat.name.toLowerCase().includes(q);
                    const matchesCode = cat.code && cat.code.toLowerCase().includes(q);
                    return matchesName || matchesCode;
                })
                .sort((a, b) => {
                    const scoreA = getScore(a);
                    const scoreB = getScore(b);
                    if (scoreA !== scoreB) return scoreA - scoreB;

                    const codeA = a.code || '';
                    const codeB = b.code || '';
                    if (codeA && codeB && codeA.length !== codeB.length) {
                        return codeA.length - codeB.length;
                    }
                    if (codeA && codeB && codeA !== codeB) {
                        return codeA.localeCompare(codeB);
                    }
                    return (a.name || '').localeCompare(b.name || '');
                });
        },

        get canCreateNew() {
            const q = this.query.trim().toLowerCase();
            if (!q) return false;
            return !(this.preloads || []).some(c => c.name.toLowerCase() === q || (c.code && c.code.toLowerCase() === q));
        },

        selectCategory(cat) {
            if (cat && cat.id && !(this.preloads || []).some(c => String(c.id) === String(cat.id))) {
                this.preloads.push({ id: cat.id, name: cat.name, code: cat.code || null });
            }
            if (!this.isSelected(cat.id)) {
                this.selectedIds = [...(this.selectedIds || []), cat.id];
            }
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput?.focus());
        },

        removeCategory(id) {
            if (this.lastAutoDetectedId && String(this.lastAutoDetectedId) === String(id)) {
                this.lastAutoDetectedId = null;
            }
            this.selectedIds = (this.selectedIds || []).filter(item => String(item) !== String(id));
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput?.focus());
        },

        useAsNewCategory() {
            const q = this.query.trim();
            if (!q) return;
            $wire.createAndSelectCategory(q).then(newCat => {
                if (newCat && newCat.id) {
                    if (!(this.preloads || []).some(c => String(c.id) === String(newCat.id))) {
                        this.preloads.push({ id: newCat.id, name: newCat.name, code: newCat.code || null });
                    }
                    if (!this.isSelected(newCat.id)) {
                        this.selectedIds = [...(this.selectedIds || []), newCat.id];
                    }
                }
            });
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = false;
            this.$nextTick(() => this.$refs.catSearchInput?.focus());
        },

        clearAll() {
            this.selectedIds = [];
            this.lastAutoDetectedId = null;
            this.query = '';
            this.highlightedIndex = 0;
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput?.focus());
        },

        handleBackspace() {
            if (this.query === '' && (this.selectedIds || []).length > 0) {
                const removedId = this.selectedIds[this.selectedIds.length - 1];
                this.removeCategory(removedId);
            }
        },

        nextItem() {
            const total = this.filteredCategories.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % total;
        },

        prevItem() {
            const total = this.filteredCategories.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex - 1 + total) % total;
        },

        autoSelectByCode(code) {
            const cleanCode = (code || '').trim().toUpperCase();
            const matched = cleanCode ? (this.preloads || []).find(c => (c.code || '').toUpperCase() === cleanCode) : null;
            const newId = matched ? matched.id : null;

            // If previously auto-detected category is different from new match, auto-remove it
            if (this.lastAutoDetectedId && String(this.lastAutoDetectedId) !== String(newId)) {
                this.selectedIds = (this.selectedIds || []).filter(item => String(item) !== String(this.lastAutoDetectedId));
                this.lastAutoDetectedId = null;
            }

            // Add newly detected category if not already selected
            if (matched) {
                if (!(this.preloads || []).some(c => String(c.id) === String(matched.id))) {
                    this.preloads.push({ id: matched.id, name: matched.name, code: matched.code || null });
                }
                if (!this.isSelected(matched.id)) {
                    this.selectedIds = [...(this.selectedIds || []), matched.id];
                }
                this.lastAutoDetectedId = matched.id;
            }
        },

        selectHighlighted() {
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (this.highlightedIndex < this.filteredCategories.length) {
                this.selectCategory(this.filteredCategories[this.highlightedIndex]);
            } else if (this.canCreateNew) {
                this.useAsNewCategory();
            }
        }
     }"
     @call-number-code-detected.window="autoSelectByCode($event.detail.code)"
     @click.outside="isOpen = false"
>
    <div class="flex items-center justify-between">
        <label class="text-sm font-semibold text-[#334155]">Categories</label>
        <template x-if="selectedIds.length > 0">
            <span class="text-xs font-semibold text-[#102B70]" x-text="selectedIds.length + ' selected'"></span>
        </template>
    </div>

    <!-- Relative Positioning Wrapper for Input & Dropdown -->
    <div class="relative">
        <!-- True Tokenized Tag-Input Container -->
        <div
            @click="$refs.catSearchInput?.focus()"
            class="min-h-[56px] w-full p-2.5 pr-20 rounded-2xl border border-[#E2E8F0] bg-white transition-all cursor-text flex flex-wrap items-center gap-1.5 focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF]"
            :class="{ 'border-[#102B70] ring-4 ring-[#EFF6FF]': isOpen }"
        >
            <!-- Selected Category Chips -->
            <template x-for="cat in selectedItems" :key="cat.id">
                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-lg text-xs font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] shadow-2xs group shrink-0 select-none animate-fade-in">
                    <span x-text="cat.name"></span>
                    <button
                        type="button"
                        @click.stop="removeCategory(cat.id)"
                        class="w-4 h-4 rounded inline-flex items-center justify-center text-[#3B82F6] hover:bg-[#102B70] hover:text-white transition-colors duration-150"
                        title="Remove category"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </span>
            </template>

            <!-- Inline Continuing Filter / Search Input -->
            <input
                x-ref="catSearchInput"
                x-model="query"
                @input="highlightedIndex = 0"
                @focus="isOpen = true"
                @keydown.arrow-down.prevent="nextItem()"
                @keydown.arrow-up.prevent="prevItem()"
                @keydown.enter.prevent="selectHighlighted()"
                @keydown.escape="isOpen = false"
                @keydown.backspace="handleBackspace()"
                type="text"
                :placeholder="selectedIds.length === 0 ? 'Type to search or select categories...' : 'Add category...'"
                class="flex-1 min-w-[110px] h-8 bg-transparent border-0 border-none p-0 m-0 text-sm font-medium text-[#0F172A] placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-0 shadow-none"
                style="outline: none !important; box-shadow: none !important; border: none !important;"
            >

            <!-- Trailing Action Controls: Clear & Dropdown Toggle -->
            <div class="absolute right-3 top-3.5 flex items-center gap-1 shrink-0">
                <button
                    type="button"
                    x-show="selectedIds.length > 0 || (query && query.length > 0)"
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
                    title="Toggle categories list"
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
            <div class="px-4 py-2 text-xs font-bold text-slate-500 bg-slate-50 border-b border-[#F1F5F9] flex items-center justify-between uppercase tracking-wider">
                <span>Category</span>
                <span>Code</span>
            </div>

            <!-- Available Results -->
            <template x-if="filteredCategories.length > 0">
                <div class="py-1">
                    <template x-for="(cat, index) in filteredCategories" :key="cat.id">
                        <div
                            @click="selectCategory(cat)"
                            @mouseenter="highlightedIndex = index"
                            :class="{
                                'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === index,
                                'text-[#0F172A] hover:bg-slate-50': highlightedIndex !== index
                            }"
                            class="px-4 py-2.5 flex items-center justify-between gap-4 cursor-pointer transition-colors border-b border-slate-50 last:border-0"
                        >
                            <!-- Left Side: Category Name -->
                            <div class="flex-1 min-w-0 pr-2">
                                <span class="text-sm font-medium leading-snug" x-text="cat.name"></span>
                            </div>

                            <!-- Right Side: Code (Blue, no box, 5px larger than text-sm) -->
                            <div class="shrink-0 text-right">
                                <span class="text-[15px] font-bold text-[#2563EB] font-mono tracking-wide" x-text="cat.code || ''"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Add New Category Option (if typed query not existing) -->
            <template x-if="canCreateNew">
                <div
                    @click="useAsNewCategory()"
                    @mouseenter="highlightedIndex = filteredCategories.length"
                    :class="{ 'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === filteredCategories.length }"
                    class="px-3.5 py-2.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between cursor-pointer hover:bg-[#EFF6FF] text-sm font-semibold text-[#102B70] transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xs font-bold shrink-0">
                            +
                        </div>
                        <span>Register category: "<span class="underline" x-text="query.trim()"></span>"</span>
                    </div>
                    <span class="text-xs font-semibold text-[#3B82F6] px-2 py-0.5 rounded bg-[#EFF6FF]">New Category</span>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="filteredCategories.length === 0 && !canCreateNew">
                <div class="p-4 text-center text-xs text-slate-400 font-medium">
                    <span x-show="preloads.length > 0">All matching categories are already selected.</span>
                    <span x-show="preloads.length === 0">No categories found in catalog.</span>
                </div>
            </template>
        </div>
    </div>

    <p class="text-xs text-slate-400 font-medium">Select one or more categories for this book.</p>
    @error('selected') <span class="text-xs font-semibold text-[#EF4444] block">{{ $message }}</span> @enderror
</div>
