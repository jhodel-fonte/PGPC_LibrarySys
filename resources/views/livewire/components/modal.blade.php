<x-modal id="modal" wire="isOpen" blur center="md">
    @if($title)
        <x-slot:title>
            <div class="flex items-center gap-3">
                <!-- Icon based on Alert Type -->
                @if($type === 'success')
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

    <div class="text-xs text-[#64748B] font-semibold leading-relaxed mt-2.5 px-1">
        @if(!empty($message))
            {{ $message }}
        @else
            {{ $slot ?? '' }}
        @endif
    </div>

    @if($showConfirmButton || $showCancelButton)
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
