@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'subtext' => null,
    'changeColor' => 'emerald',
    'titleSize' => 'text-xs',
    'valueSize' => 'text-2xl',
    'subtextColor' => '#4763a7',
    'changeSize' => 'text-[14px]',
])

@php
    $badgeClasses = match($changeColor) {
        'emerald' => 'text-emerald-600',
        'amber' => 'text-amber-700',
        'red' => 'text-red-700',
        'slate' => 'text-slate-600',
        default => 'text-blue-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-4 flex flex-col justify-between hover:border-[#102B70]/30 transition-all min-w-0']) }}>
    <div class="flex items-center justify-between min-w-0">
        <span class="{{ $titleSize }} font-bold text-[#000000] uppercase tracking-wider truncate" title="{{ $title }}">{{ $title }}</span>
        @if(isset($action) || isset($icon))
            <div class="shrink-0">
                {{ $action ?? $icon }}
            </div>
        @endif
    </div>
    <div class="mt-1">
        <div class="flex flex-wrap items-baseline gap-2">
            <span class="{{ $valueSize }} font-bold text-[#0d2c74] tabular-nums tracking-tight">{{ $value }}</span>
        </div>
        @if($subtext)
            <div class="border-t border-slate-100 my-1"></div>
            <div class="flex items-center gap-1">
                @if($change !== null && $change !== '')
                    <span class="{{ $changeSize }} font-bold {{ $badgeClasses }} inline-block">
                        {{ $change }}
                    </span>
                @endif
                <p class="text-[13px] font-medium text-[#4763a7] truncate">{{ $subtext }}</p>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>