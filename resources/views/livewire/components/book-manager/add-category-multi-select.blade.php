<div class="space-y-1.5"
     wire:key="categories-combobox-wrapper"
     x-data="{
        selectedIds: @entangle('selected').live,
        preloads: {{ Js::from($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()) }},
        query: '',
        results: [],
        isOpen: false,
        isLoading: false,
        highlightedIndex: 0,
        debounceTimer: null,

        init() {
            if (!Array.isArray(this.selectedIds)) this.selectedIds = [];
            this.results = [...this.preloads];
        },

        // Chips are always derived from selectedIds, so they can never drift out of sync
        get selectedItems() {
            return (this.selectedIds || [])
                .map(id => this.preloads.find(c => String(c.id) === String(id)))
                .filter(Boolean);
        },

        isSelected(id) {
            return (this.selectedIds || []).some(s => String(s) === String(id));
        },

        remember(cat) {
            if (cat && cat.id && !this.preloads.some(c => String(c.id) === String(cat.id))) {
                this.preloads.push({ id: cat.id, name: cat.name });
            }
        },

        resetResults() {
            this.results = [...this.preloads];
            this.highlightedIndex = 0;
        },

        searchCategories() {
            clearTimeout(this.debounceTimer);
            if (!this.query || this.query.trim().length === 0) {
                this.resetResults();
                this.isOpen = true;
                this.isLoading = false;
                return;
            }
            this.isLoading = true;
            this.debounceTimer = setTimeout(() => {
                fetch('{{ route('admin.categories.search') }}?query=' + encodeURIComponent(this.query.trim()), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.results = data || [];
                    this.results.forEach(c => this.remember(c));
                    this.isOpen = true;
                    this.isLoading = false;
                    this.highlightedIndex = 0;
                })
                .catch(err => {
                    console.error('Category search failed:', err);
                    this.isLoading = false;
                });
            }, 250);
        },

        get unselectedResults() {
            return (this.results || []).filter(cat => !this.isSelected(cat.id));
        },

        get canCreateNew() {
            const q = this.query.trim();
            if (!q) return false;
            const inResults = (this.results || []).some(c => c.name.toLowerCase() === q.toLowerCase());
            const inSelected = this.selectedItems.some(c => c.name.toLowerCase() === q.toLowerCase());
            return !inResults && !inSelected;
        },

        selectCategory(cat) {
            this.remember(cat);
            if (!this.isSelected(cat.id)) {
                this.selectedIds = [...(this.selectedIds || []), cat.id];
            }
            this.query = '';
            this.resetResults();
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput.focus());
        },

        removeCategory(id) {
            this.selectedIds = (this.selectedIds || []).filter(item => String(item) !== String(id));
            if (!this.query || this.query.trim().length === 0) {
                this.resetResults();
            } else {
                this.searchCategories();
            }
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput.focus());
        },

        useAsNewCategory() {
            const q = this.query.trim();
            if (!q) return;
            $wire.createAndSelectCategory(q).then(newCat => {
                if (newCat && newCat.id) {
                    this.remember(newCat);
                    if (!this.isSelected(newCat.id)) {
                        this.selectedIds = [...(this.selectedIds || []), newCat.id];
                    }
                    this.resetResults();
                }
            });
            this.query = '';
            this.isOpen = false;
            this.$nextTick(() => this.$refs.catSearchInput.focus());
        },

        clearAll() {
            this.selectedIds = [];
            this.query = '';
            this.resetResults();
            this.isOpen = true;
            this.$nextTick(() => this.$refs.catSearchInput.focus());
        },

        handleBackspace() {
            if (this.query === '' && this.selectedIds.length > 0) {
                const removedId = this.selectedIds[this.selectedIds.length - 1];
                this.removeCategory(removedId);
            }
        },

        nextItem() {
            const total = this.unselectedResults.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                this.searchCategories();
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % total;
        },

        prevItem() {
            const total = this.unselectedResults.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                this.searchCategories();
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex - 1 + total) % total;
        },

        selectHighlighted() {
            if (!this.isOpen) {
                this.isOpen = true;
                this.searchCategories();
                return;
            }
            if (this.highlightedIndex < this.unselectedResults.length) {
                this.selectCategory(this.unselectedResults[this.highlightedIndex]);
            } else if (this.canCreateNew) {
                this.useAsNewCategory();
            }
        }
     }"
     @click.outside="isOpen = false"
>
    <div class="flex items-center justify-between">
        <label class="text-sm font-bold uppercase tracking-wider text-[#334155]">Categories</label>
        <template x-if="selectedIds.length > 0">
            <span class="text-xs font-semibold text-[#102B70]" x-text="selectedIds.length + ' selected'"></span>
        </template>
    </div>

    <!-- Relative Positioning Wrapper for Input & Dropdown -->
    <div class="relative">
        <!-- True Tokenized Tag-Input Container -->
        <div
            @click="$refs.catSearchInput.focus()"
            class="min-h-[56px] w-full p-2.5 pr-20 rounded-2xl border border-[#E2E8F0] bg-white transition-all cursor-text flex flex-wrap items-center gap-1.5 focus-within:border-[#102B70] focus-within:ring-4 focus-within:ring-[#EFF6FF]"
            :class="{ 'border-[#102B70] ring-4 ring-[#EFF6FF]': isOpen }"
        >
            <!-- Selected Category Chips -->
            <template x-for="cat in selectedItems" :key="cat.id">
                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1 py-1 rounded-lg text-sm font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] shadow-2xs group shrink-0 select-none animate-fade-in">
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
                @input="searchCategories()"
                @focus="searchCategories()"
                @keydown.arrow-down.prevent="nextItem()"
                @keydown.arrow-up.prevent="prevItem()"
                @keydown.enter.prevent="selectHighlighted()"
                @keydown.escape="isOpen = false"
                @keydown.backspace="handleBackspace()"
                type="text"
                :placeholder="selectedIds.length === 0 ? 'Type to search or select categories...' : 'Add category...'"
                class="flex-1 min-w-[110px] h-8 bg-transparent border-0 border-none p-0 m-0 text-base font-semibold text-[#0F172A] placeholder-slate-400 focus:outline-none focus:ring-0 focus:border-0 shadow-none"
                style="outline: none !important; box-shadow: none !important; border: none !important;"
            >

            <!-- Trailing Action Controls: Spinner, Clear & Dropdown Toggle -->
            <div class="absolute right-3 top-3.5 flex items-center gap-1 shrink-0">
                <div x-show="isLoading" x-cloak class="p-0.5 text-[#102B70]">
                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

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
                    @click.stop="isOpen ? (isOpen = false) : searchCategories()"
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
            <div class="px-3.5 py-1.5 text-xs font-bold text-slate-400 bg-slate-50 border-b border-[#F1F5F9] flex items-center justify-between">
                <span>Categories in Catalog</span>
                <span x-text="unselectedResults.length + ' available'"></span>
            </div>

            <!-- Available Results -->
            <template x-if="unselectedResults.length > 0">
                <div class="py-1">
                    <template x-for="(cat, index) in unselectedResults" :key="cat.id">
                        <div
                            @click="selectCategory(cat)"
                            @mouseenter="highlightedIndex = index"
                            :class="{
                                'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === index,
                                'text-[#0F172A] hover:bg-slate-50': highlightedIndex !== index
                            }"
                            class="px-3.5 py-2.5 flex items-center justify-between cursor-pointer transition-colors text-sm font-semibold border-b border-slate-50 last:border-0"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-[#EFF6FF] text-[#102B70] flex items-center justify-center text-xs font-bold shrink-0">
                                    <span x-text="cat.name.charAt(0)"></span>
                                </div>
                                <span x-text="cat.name"></span>
                            </div>
                            <span class="text-xs font-bold text-slate-400 px-2 py-0.5 rounded bg-slate-100 group-hover:bg-[#DBEAFE] group-hover:text-[#102B70]">
                                + Add
                            </span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Add New Category Option (if typed query not existing) -->
            <template x-if="canCreateNew">
                <div
                    @click="useAsNewCategory()"
                    @mouseenter="highlightedIndex = unselectedResults.length"
                    :class="{ 'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === unselectedResults.length }"
                    class="px-3.5 py-2.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between cursor-pointer hover:bg-[#EFF6FF] text-sm font-bold text-[#102B70] transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xs font-bold shrink-0">
                            +
                        </div>
                        <span>Register category: "<span class="underline" x-text="query.trim()"></span>"</span>
                    </div>
                    <span class="text-xs font-bold text-[#3B82F6] px-2 py-0.5 rounded bg-[#EFF6FF]">New Category</span>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!isLoading && unselectedResults.length === 0 && !canCreateNew">
                <div class="p-4 text-center text-sm text-slate-400 font-medium">
                    <span x-show="results.length > 0">All matching categories are already selected.</span>
                    <span x-show="results.length === 0">No categories found matching your query.</span>
                </div>
            </template>
        </div>
    </div>

    <p class="text-xs text-slate-400 font-medium">Select one or more categories for this book.</p>
    @error('selected') <span class="text-sm font-bold text-[#EF4444] block">{{ $message }}</span> @enderror
</div>
