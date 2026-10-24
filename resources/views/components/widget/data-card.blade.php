@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'subtext' => null,
    'changeColor' => 'emerald',
])

@php
    $badgeClasses = match($changeColor) {
        'blue' => 'text-blue-600 bg-blue-50',
        'amber' => 'text-amber-700 bg-amber-50',
        'red' => 'text-red-700 bg-red-50',
        'slate' => 'text-slate-600 bg-slate-50',
        default => 'text-emerald-700 bg-emerald-50',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/90 shadow-[0_1px_2px_rgba(15,23,42,0.04)] p-4 flex flex-col justify-between hover:border-[#102B70]/30 transition-all min-w-0']) }}>
    <div class="flex items-center justify-between gap-2 min-w-0">
        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider truncate" title="{{ $title }}">{{ $title }}</span>
        @if(isset($action) || isset($icon))
            <div class="shrink-0">
                {{ $action ?? $icon }}
            </div>
        @endif
    </div>
    <div class="mt-3">
        <div class="flex flex-wrap items-baseline gap-2">
            <span class="text-2xl font-extrabold text-[#1a326b] tabular-nums tracking-tight">{{ $value }}</span>
            @if($change)
                <span class="inline-flex items-center text-[11px] font-bold {{ $badgeClasses }} px-1.5 py-0.5 rounded-md shrink-0">
                    {{ $change }}
                </span>
            @endif
        </div>
        @if($subtext)
            <p class="text-[11px] font-medium text-[#4763a7] mt-1 break-words">{{ $subtext }}</p>
        @endif
        {{ $slot }}
    </div>
</div>