<div class="space-y-1.5"
     wire:key="publisher-select-combobox-wrapper"
     x-data="{
        query: @entangle('publisherName').live,
        selectedPublisherId: @entangle('publisherId').live,
        selectedPublisherName: @entangle('selectedPublisherName').live,
        preloads: {{ Js::from($publishers->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()) }},
        isOpen: false,
        highlightedIndex: 0,

        get filteredPublishers() {
            const q = (this.query || '').trim().toLowerCase();
            if (!q) return this.preloads || [];

            return (this.preloads || [])
                .filter(p => p.name && p.name.toLowerCase().includes(q))
                .sort((a, b) => {
                    const nameA = a.name.toLowerCase();
                    const nameB = b.name.toLowerCase();
                    const aStarts = nameA.startsWith(q);
                    const bStarts = nameB.startsWith(q);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                    return nameA.localeCompare(nameB);
                });
        },

        get canCreateNew() {
            const q = (this.query || '').trim().toLowerCase();
            if (!q) return false;
            return !(this.preloads || []).some(p => p.name.toLowerCase() === q);
        },

        selectPublisher(publisher) {
            this.selectedPublisherId = publisher.id;
            this.selectedPublisherName = publisher.name;
            this.query = publisher.name;
            this.isOpen = false;
            $wire.selectPublisher(publisher.id, publisher.name);
        },

        useAsNewPublisher() {
            const q = (this.query || '').trim();
            if (!q) return;
            this.selectedPublisherId = null;
            this.selectedPublisherName = q;
            this.query = q;
            this.isOpen = false;
            $wire.setAsNewPublisher(q);
        },

        clearSelection() {
            this.selectedPublisherId = null;
            this.selectedPublisherName = '';
            this.query = '';
            this.isOpen = false;
            $wire.clearPublisher();
        },

        nextItem() {
            const total = this.filteredPublishers.length + (this.canCreateNew ? 1 : 0);
            if (!this.isOpen) {
                this.isOpen = true;
                return;
            }
            if (total === 0) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % total;
        },

        prevItem() {
            const total = this.filteredPublishers.length + (this.canCreateNew ? 1 : 0);
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
            if (this.highlightedIndex < this.filteredPublishers.length) {
                this.selectPublisher(this.filteredPublishers[this.highlightedIndex]);
            } else if (this.canCreateNew) {
                this.useAsNewPublisher();
            }
        }
     }"
     @click.outside="isOpen = false"
>
    <label class="text-sm font-semibold text-[#334155]">Publisher</label>

    <!-- Publisher Search Input Container -->
    <div class="relative">
        <div class="relative flex items-center">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>

            <input
                x-ref="publisherInput"
                type="text"
                x-model="query"
                @input="highlightedIndex = 0; isOpen = true"
                @focus="isOpen = true"
                @keydown.arrow-down.prevent="nextItem()"
                @keydown.arrow-up.prevent="prevItem()"
                @keydown.enter.prevent="selectHighlighted()"
                @keydown.escape="isOpen = false"
                placeholder="Type to search existing publishers or enter a new publisher..."
                class="w-full h-14 pl-10 pr-20 rounded-2xl border border-[#E2E8F0] bg-white text-sm font-medium text-[#0F172A] placeholder-slate-400 outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                :class="{ 'border-[#102B70] ring-2 ring-[#EFF6FF]': selectedPublisherId }"
            >

            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
                <!-- Clear button -->
                <button
                    type="button"
                    x-show="query && query.length > 0"
                    x-cloak
                    @click.stop="clearSelection()"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                    title="Clear input"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Dropdown toggle arrow button -->
                <button
                    type="button"
                    @click.stop="isOpen = !isOpen"
                    class="p-1 text-slate-400 hover:text-[#102B70] rounded-lg hover:bg-slate-100 transition-transform duration-200"
                    :class="{ 'rotate-180 text-[#102B70]': isOpen }"
                    title="Toggle publishers list"
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
            class="absolute z-50 left-0 right-0 top-full mt-1.5 bg-white border border-[#E2E8F0] rounded-2xl shadow-xl overflow-hidden max-h-64 overflow-y-auto custom-scrollbar"
        >
            <!-- Matching Existing Publishers -->
            <template x-if="filteredPublishers.length > 0">
                <div class="py-1">
                    <div class="px-4 py-2 text-xs font-bold text-slate-500 bg-slate-50 border-b border-[#F1F5F9] flex items-center justify-between uppercase tracking-wider">
                        <span>Existing Publishers in Catalog</span>
                        <span x-text="filteredPublishers.length + ' found'"></span>
                    </div>
                    <template x-for="(publisher, index) in filteredPublishers" :key="publisher.id">
                        <div
                            @click="selectPublisher(publisher)"
                            @mouseenter="highlightedIndex = index"
                            :class="{
                                'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === index || selectedPublisherId === publisher.id,
                                'text-[#0F172A] hover:bg-slate-50': highlightedIndex !== index && selectedPublisherId !== publisher.id
                            }"
                            class="px-4 py-2.5 flex items-center justify-between cursor-pointer transition-colors text-sm font-medium border-b border-slate-50 last:border-0"
                        >
                            <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                <div class="w-6 h-6 rounded-full bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] flex items-center justify-center text-xs font-bold shrink-0">
                                    <span x-text="(publisher.name || '').charAt(0).toUpperCase()"></span>
                                </div>
                                <span class="truncate" x-text="publisher.name"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-400 px-2 py-0.5 rounded-md bg-slate-100">Existing</span>
                                <template x-if="selectedPublisherId === publisher.id">
                                    <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Add as New Publisher Option -->
            <template x-if="canCreateNew">
                <div
                    @click="useAsNewPublisher()"
                    @mouseenter="highlightedIndex = filteredPublishers.length"
                    :class="{ 'bg-[#EFF6FF] text-[#102B70]': highlightedIndex === filteredPublishers.length }"
                    class="px-4 py-3 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between cursor-pointer hover:bg-[#EFF6FF] text-sm font-semibold text-[#102B70] transition-colors"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xs font-bold shrink-0">
                            +
                        </div>
                        <span>Register publisher: "<span class="underline" x-text="(query || '').trim()"></span>"</span>
                    </div>
                    <span class="text-xs font-semibold text-[#3B82F6] px-2 py-0.5 rounded-md bg-[#EFF6FF]">New Publisher</span>
                </div>
            </template>

            <!-- Empty State when catalog is empty and cannot create new -->
            <template x-if="filteredPublishers.length === 0 && !canCreateNew">
                <div class="p-4 text-center text-xs text-slate-400 font-medium">
                    <span>No publishers found in catalog. Type above to register a new publisher.</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Publisher Status Indicator Pill -->
    <template x-if="selectedPublisherId">
        <div class="flex items-center gap-2 pt-1 text-xs font-semibold text-[#15803D] animate-fade-in">
            <svg class="w-4 h-4 text-[#16A34A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span>Selected existing publisher (<span x-text="selectedPublisherName"></span>)</span>
        </div>
    </template>
    <template x-if="!selectedPublisherId && query && query.trim().length > 0">
        <div class="flex items-center gap-2 pt-1 text-xs font-semibold text-[#3B82F6] animate-fade-in">
            <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Will be registered as a new publisher</span>
        </div>
    </template>

    @error('publisherName') <span class="text-xs font-semibold text-[#EF4444] block">{{ $message }}</span> @enderror
</div>
