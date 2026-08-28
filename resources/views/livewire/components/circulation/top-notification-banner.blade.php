@php
    // Determine colors based on type
    switch ($type) {
        case 'success':
            $bg = 'bg-[#F0FDF4]';
            $border = 'border-[#BBF7D0]';
            $iconBg = 'bg-white';
            $iconBorder = 'border-[#BBF7D0]';
            $iconColor = 'text-[#16A34A]';
            $titleColor = 'text-[#15803D]';
            $textColor = 'text-[#14532D]';
            $confirmBg = 'bg-[#16A34A] hover:bg-[#15803D] text-white';
            $cancelBg = 'border-[#E2E8F0] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]';
            break;
        case 'info':
            $bg = 'bg-[#EFF6FF]';
            $border = 'border-[#BFDBFE]';
            $iconBg = 'bg-white';
            $iconBorder = 'border-[#BFDBFE]';
            $iconColor = 'text-[#3B82F6]';
            $titleColor = 'text-[#1D4ED8]';
            $textColor = 'text-[#1E3A8A]';
            $confirmBg = 'bg-[#3B82F6] hover:bg-[#1D4ED8] text-white';
            $cancelBg = 'border-[#E2E8F0] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]';
            break;
        case 'warning':
        case 'danger':
        default:
            $bg = 'bg-[#FEF2F2]';
            $border = 'border-[#FCA5A5]';
            $iconBg = 'bg-white';
            $iconBorder = 'border-[#FECACA]';
            $iconColor = 'text-[#EF4444]';
            $titleColor = 'text-[#B91C1C]';
            $textColor = 'text-[#7F1D1D]';
            $confirmBg = 'bg-[#EF4444] hover:bg-[#B91C1C] text-white';
            $cancelBg = 'border-[#E2E8F0] bg-white text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]';
            break;
    }
@endphp

<div class="p-4 {{ $bg }} border {{ $border }} rounded-3xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-[0_4px_20px_rgba(0,0,0,0.02)] shrink-0 animate-fade-in">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-full {{ $iconBg }} border {{ $iconBorder }} flex items-center justify-center shrink-0 {{ $iconColor }} shadow-sm">
            @if($type === 'success')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            @elseif($type === 'info')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            @if($title)
                <h4 class="text-xs font-bold {{ $titleColor }} uppercase tracking-wider">{{ $title }}</h4>
            @endif
            <p class="text-xs {{ $textColor }} font-semibold mt-1 leading-relaxed">
                {!! $message !!}
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
        @if($cancelAction)
            <button type="button" 
                    wire:click="$parent.{{ $cancelAction }}()" 
                    class="h-9 px-4 border rounded-xl text-xs font-bold transition-all shadow-sm {{ $cancelBg }}">
                {{ $cancelLabel }}
            </button>
        @endif
        @if($confirmAction)
            <button type="button" 
                    wire:click="$parent.{{ $confirmAction }}()" 
                    class="h-9 px-4 rounded-xl text-xs font-bold transition-all shadow-sm {{ $confirmBg }}">
                {{ $confirmLabel }}
            </button>
        @endif
    </div>
</div>
