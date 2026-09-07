<div class="w-full flex flex-col gap-2"
<div class="w-full flex flex-col gap-1.5"
     x-data="{ queryVal: @entangle('value'), openingAdd: false }"
     @clear-search-input.window="queryVal = ''; $nextTick(() => { $refs.checkinInput.focus(); });"
     @set-search-value.window="
         let codeStr = $event.detail.code;
         queryVal = '';
         let index = 0;
         let interval = setInterval(() => {
             if (index < codeStr.length) {
                 queryVal += codeStr[index];
                 index++;
             } else {
                 clearInterval(interval);
                 $wire.submit(queryVal);
             }
         }, 30);
     "
     @search-modal-opened.window="openingAdd = false"
     @search-modal-closed.window="openingAdd = false"
>
    <label class="block text-sm font-semibold text-[#102B70] tracking-wide">{{ $label }}</label>
    <label for="circulation-search-input" class="block text-xs font-bold text-[#334155] uppercase tracking-wider">{{ $label }}</label>
    <div class="relative w-full flex items-center">
        <!-- Clickable Barcode/Scanner Icon Button on the Left -->
        <button type="button"
                @click="$dispatch('start-camera')"
                class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#64748B] hover:text-[#102B70] transition-colors focus:outline-none z-10"
                aria-label="Activate camera scanner"
                title="Start/Resume Scanner">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7V5a2 2 0 012-2h2M18 5a2 2 0 012 2v2M4 17v2a2 2 0 002 2h2M18 19a2 2 0 002-2v-2M10 8v8M14 8v8M7 8v8M17 8v8"></path>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7V5a2 2 0 012-2h2M18 5a2 2 0 012 2v2M4 17v2a2 2 0 002 2h2M18 19a2 2 0 002-2v-2M10 8v8M14 8v8M7 8v8M17 8v8"></path>
            </svg>
        </button>

        <input type="text"
               id="circulation-search-input"
               x-ref="checkinInput"
               x-model="queryVal"
               wire:model="value"
               wire:keydown.enter="submit"
               wire:loading.attr="disabled"
               wire:loading.class="opacity-60 cursor-not-allowed bg-slate-50"
               placeholder="{{ $placeholder }}"
               class="w-full pl-12 pr-[155px] h-11 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-[15px] font-semibold text-[#0F172A] placeholder-[#64748B] tracking-wide focus:outline-none focus:ring-2 focus:ring-[#102B70]/20 focus:border-[#102B70] transition-shadow">
               class="w-full pl-12 pr-[160px] h-14 bg-white border border-[#E2E8F0] rounded-2xl text-sm font-semibold text-[#0F172A] placeholder-[#64748B] tracking-wide focus:outline-none focus:ring-2 focus:ring-[#102B70]/20 focus:border-[#102B70] transition-all shadow-2xs">

        <!-- Action Buttons Container (Search, Clear & Add side-by-side) -->
        <div class="absolute right-1.5 top-1.5 bottom-1.5 flex items-center gap-1.5 z-10">
        <div class="absolute right-2 top-2 bottom-2 flex items-center gap-1.5 z-10">
            <!-- Loading Spinner (Shown only when Livewire is loading) -->
            <div wire:loading.flex class="items-center justify-center px-3 gap-2 text-[#102B70] h-8 bg-white rounded-lg">
                <svg class="animate-spin-custom h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <div wire:loading.flex class="items-center justify-center px-3 gap-2 text-[#102B70] h-10 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl">
                <svg class="animate-spin h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Processing...</span>
                <span class="text-xs font-bold uppercase tracking-wider text-[#475569]">Processing...</span>
            </div>

            <!-- Action Buttons (Hidden when loading) -->
            <div wire:loading.remove class="flex items-center gap-1.5">
                <!-- Clear Button (Client-side reset and focus recovery) -->
                <button type="button"
                        x-show="queryVal && queryVal.length > 0"
                        @click="queryVal = ''; $nextTick(() => { $refs.checkinInput.focus(); });"
                        class="h-8 w-8 flex items-center justify-center bg-white border border-[#E2E8F0] hover:border-[#102B70] text-[#64748B] hover:text-[#B91C1C] rounded-lg transition-colors shadow-sm focus:outline-none"
                        class="h-10 w-10 flex items-center justify-center bg-[#F8FAFC] border border-[#E2E8F0] hover:border-[#102B70] text-[#64748B] hover:text-[#B91C1C] rounded-xl transition-colors shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#102B70]/20"
                        aria-label="Clear input"
                        title="Clear input"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Clickable Blue/Navy Add/Submit Button -->
                <!-- Clickable Navy Add/Search Entity Button -->
                <button type="button"
                        @click="openingAdd = true; $dispatch('open-search-modal')"
                        :disabled="openingAdd"
                        class="h-8 px-3.5 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-lg text-[10px] font-bold tracking-wider uppercase flex items-center gap-1.5 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-[#102B70]/30 disabled:opacity-60 disabled:cursor-not-allowed">
                        class="h-10 px-4 bg-[#102B70] hover:bg-[#0B225E] text-white rounded-xl text-xs font-bold tracking-wider uppercase flex items-center gap-2 transition-colors shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#102B70]/20 disabled:opacity-60 disabled:cursor-not-allowed">
                    <!-- Plus Icon (Hidden when loading) -->
                    <svg x-show="!openingAdd" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <svg x-show="!openingAdd" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>

                    <!-- Spinner (Shown when loading) -->
                    <svg x-show="openingAdd" class="animate-spin-custom h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <svg x-show="openingAdd" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <span x-text="openingAdd ? 'Loading...' : 'Add'">Add</span>
                </button>
            </div>
        </div>
    </div>
</div>
