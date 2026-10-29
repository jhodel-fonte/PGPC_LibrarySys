@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'subtext' => null,
    'changeColor' => 'emerald',
    'titleSize' => 'text-xs',
    'valueSize' => 'text-2xl',
    'subtextColor' => 'text-[#64748B]',
    'changeSize' => 'text-xs',
])

@php
    $badgeClasses = match($changeColor) {
        'emerald' => 'text-emerald-700 bg-emerald-50 border border-emerald-200/60',
        'amber' => 'text-amber-800 bg-amber-50 border border-amber-200/60',
        'red' => 'text-[#B91C1C] bg-[#FEF2F2] border border-[#FECACA]',
        'slate' => 'text-[#334155] bg-slate-100 border border-slate-200/60',
        default => 'text-[#102B70] bg-[#EFF6FF] border border-[#DBEAFE]',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[#E2E8F0] shadow-xs p-4 sm:p-4.5 flex flex-col justify-between hover:border-[#CBD5E1] transition-all min-w-0']) }}>
    <div class="flex items-center justify-between gap-2 min-w-0">
        <span class="{{ $titleSize }} font-semibold text-[#64748B] uppercase tracking-wider truncate" title="{{ $title }}">
            {{ $title }}
        </span>
        @if(isset($action) || isset($icon))
            <div class="shrink-0 text-[#94A3B8]">
                {{ $action ?? $icon }}
            </div>
        @endif
    </div>

    <div class="mt-2">
        <div class="flex flex-wrap items-baseline gap-2">
            <span class="{{ $valueSize }} font-bold text-[#102B70] tabular-nums tracking-tight">
                {{ $value }}
            </span>
        </div>

        @if($subtext || ($change !== null && $change !== ''))
            <div class="border-t border-[#F1F5F9] my-2"></div>
            <div class="flex items-center gap-1.5 min-w-0">
                @if($change !== null && $change !== '')
                    <span class="{{ $changeSize }} font-semibold {{ $badgeClasses }} px-1.5 py-0.5 rounded shrink-0">
                        {{ $change }}
                    </span>
                @endif
                @if($subtext)
                    <p class="text-xs font-medium text-[#64748B] truncate" title="{{ $subtext }}">{{ $subtext }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
