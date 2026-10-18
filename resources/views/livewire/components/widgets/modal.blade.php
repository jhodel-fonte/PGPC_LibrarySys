<x-modal id="modal" wire="isOpen" blur center="md" :persistent="$type === 'loading' || ($isPersistent ?? false)">
    @if($title)
        <x-slot:title>
            <div class="flex items-center gap-3">
                <!-- Icon based on Alert Type -->
                @if($type === 'loading')
                    <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5 animate-spin text-[#102B70]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                @elseif($type === 'success')
                    <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] border border-[#DCFCE7] text-[#10B981] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @elseif($type === 'warning')
                    <div class="w-10 h-10 rounded-xl bg-[#FFF9E6] border border-[#FFEAA7] text-[#FCC719] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                @elseif($type === 'danger' || $type === 'error')
                    <div class="w-10 h-10 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#EF4444] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-[#DBEAFE] text-[#3B82F6] flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @endif
                <span class="text-base font-bold text-[#102B70] tracking-tight leading-snug">{{ $title }}</span>
            </div>
        </x-slot:title>
    @endif

    @if($type === 'loading')
        <div 
            x-data="{
                stepIndex: 0,
                steps: [
                    'Extracting MARC21 bibliographic records...',
                    'Mapping tags (Title, Author, ISBN, Call Numbers)...',
                    'Generating unique accession sequence numbers...',
                    'Saving and committing books into catalog...'
                ],
                progress: 20,
                timer: null,
                closeTimer: null
            }"
            x-init="
                timer = setInterval(() => {
                    if (stepIndex < steps.length - 1) {
                        stepIndex++;
                        progress = Math.min(95, progress + 25);
                    }
                }, 450);
                @if($autoCloseMs)
                    closeTimer = setTimeout(() => {
                        clearInterval(timer);
                        progress = 100;
                        $wire.finishLoading();
                    }, {{ $autoCloseMs }});
                @endif
                $watch('$wire.isOpen', value => {
                    if (!value) {
                        clearInterval(timer);
                        clearTimeout(closeTimer);
                    }
                });
            "
            class="py-3 px-1 space-y-4"
        >
            <div class="text-sm font-medium text-slate-700 leading-relaxed">
                {{ $message ?: 'Please wait while the library database processes and catalogs the imported books...' }}
            </div>

            <!-- Dynamic step indicator card -->
            <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3.5 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-[#102B70] flex items-center gap-2 min-w-0 truncate">
                        <svg class="w-3.5 h-3.5 animate-spin text-[#102B70] shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="steps[stepIndex]" class="truncate"></span>
                    </span>
                    <span class="text-slate-500 font-bold tabular-nums shrink-0 ml-2" x-text="`${progress}%`"></span>
                </div>

                <!-- Animated Progress Bar -->
                <div class="w-full bg-slate-200/80 rounded-full h-2 overflow-hidden p-0.5">
                    <div 
                        class="bg-gradient-to-r from-[#102B70] via-[#3B82F6] to-[#FCC719] h-full rounded-full transition-all duration-500 ease-out"
                        :style="`width: ${progress}%`"
                    ></div>
                </div>
            </div>

            <p class="text-[11px] text-center text-slate-400 font-medium">
                Please do not close or leave this page while saving is in progress.
            </p>
        </div>
    @else
        <div class="text-xs text-[#64748B] font-semibold leading-relaxed mt-2.5 px-1">
            @if(!empty($message))
                {{ $message }}
            @else
                {{ $slot ?? '' }}
            @endif
        </div>
    @endif

    @if($type !== 'loading' && ($showConfirmButton || $showCancelButton))
        <x-slot:footer>
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 w-full">
                @if($showCancelButton)
                    <button type="button"
                            wire:click="cancel"
                            class="w-full sm:w-auto h-11 px-5 bg-slate-100 hover:bg-slate-200 text-[#64748B] font-bold rounded-2xl text-xs uppercase tracking-wider transition-colors focus:outline-none"
                    >
                        {{ $cancelButtonText }}
                    </button>
                @endif

                @if($showConfirmButton)
                    <button type="button"
                            wire:click="confirm"
                            class="w-full sm:w-auto h-11 px-5 text-white font-bold rounded-2xl text-xs uppercase tracking-wider transition-colors shadow-sm focus:outline-none 
                                   {{ $type === 'danger' || $type === 'error' ? 'bg-[#EF4444] hover:bg-[#DC2626]' : ($type === 'success' ? 'bg-[#10B981] hover:bg-[#059669]' : 'bg-[#102B70] hover:bg-[#0B225E]') }}"
                    >
                        {{ $confirmButtonText }}
                    </button>
                @endif
            </div>
        </x-slot:footer>
    @endif
</x-modal>
