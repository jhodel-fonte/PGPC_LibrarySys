@props([
    'series' => [],
    'labels' => [],
    'type' => 'line',
    'line' => false,
    'bar' => false,
    'donut' => false,
    'area' => false,
    'pie' => false,
    'grid' => false,
    'legend' => false,
    'tooltip' => false,
    'markers' => false,
    'curve' => 'smooth',
    'height' => 190,
    'colors' => null,
    'color' => null,
    'centerLabel' => null,
    'prefix' => '',
    'suffix' => '',
])

@php
    // Resolve chart type from flags or string
    $resolvedType = 'line';
    if ($donut || $type === 'donut') $resolvedType = 'donut';
    elseif ($bar || $type === 'bar') $resolvedType = 'bar';
    elseif ($area || $type === 'area') $resolvedType = 'area';
    elseif ($pie || $type === 'pie') $resolvedType = 'pie';
    elseif ($line || $type === 'line') $resolvedType = 'line';

    // Standardized color palette
    $palette = $colors ?? [
        '#0284c7', // Primary Blue
        '#10b981', // Emerald Green
        '#f59e0b', // Amber / Gold
        '#f97316', // Orange
        '#8b5cf6', // Purple
        '#94a3b8', // Slate / Gray
    ];

    // Standardize series to collection or array
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

    // Height & Width
    $renderHeight = (int) $height;
    $svgWidth = 520;
@endphp

<div 
    x-data="{
        tooltipVisible: false,
        tooltipTitle: '',
        tooltipSeries: '',
        tooltipValue: '',
        tooltipColor: '',
        tooltipX: 0,
        tooltipY: 0,
        showTooltip(e, title, series, value, color) {
            this.tooltipTitle = title;
            this.tooltipSeries = series;
            this.tooltipValue = value;
            this.tooltipColor = color;
            this.tooltipVisible = true;
            this.moveTooltip(e);
        },
        moveTooltip(e) {
            const rect = this.$el.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            this.tooltipX = Math.max(50, Math.min(rect.width - 50, x));
            this.tooltipY = Math.max(10, y);
        },
        hideTooltip() {
            this.tooltipVisible = false;
        }
    }"
    @mousemove="if (tooltipVisible) moveTooltip($event)"
    @mouseleave="hideTooltip()"
    {{ $attributes->merge(['class' => 'relative w-full min-w-0 flex flex-col justify-between select-none']) }}
>
    <!-- Floating Interactive Tooltip -->
    <div 
        x-cloak
        x-show="tooltipVisible"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="pointer-events-none absolute z-40 bg-[#0F172A]/95 text-white text-xs rounded-xl shadow-2xl px-3 py-2 border border-slate-700/60 backdrop-blur-md whitespace-nowrap will-change-transform"
        :style="tooltipY < 65 
            ? `left: ${tooltipX}px; top: ${tooltipY + 16}px; transform: translate(-50%, 0);` 
            : `left: ${tooltipX}px; top: ${tooltipY - 14}px; transform: translate(-50%, -100%);`"
    >
        <div class="flex items-center gap-1.5 pb-1 border-b border-slate-700/60 font-semibold text-[11px] text-slate-300">
            <span class="w-2 h-2 rounded-full shrink-0" :style="`background-color: ${tooltipColor || '#3B82F6'}`"></span>
            <span x-text="tooltipTitle"></span>
        </div>
        <div class="pt-1 flex items-center justify-between gap-4 text-xs">
            <span class="text-slate-400 font-medium" x-text="tooltipSeries"></span>
            <span class="font-bold text-white tabular-nums" x-text="tooltipValue"></span>
        </div>
    </div>

    @if ($resolvedType === 'donut' || $resolvedType === 'pie')
        <x-widget.graph.pie 
            :series="$rawSeries" 
            :labels="$rawLabels" 
            :palette="$palette" 
            :centerLabel="$centerLabel" 
            :donut="$resolvedType === 'donut'" 
            :legend="$legend" 
            :suffix="$suffix" 
        />
    @elseif ($resolvedType === 'bar')
        <x-widget.graph.bar 
            :series="$rawSeries" 
            :labels="$rawLabels" 
            :palette="$palette" 
            :grid="$grid" 
            :legend="$legend" 
            :height="$renderHeight" 
            :svgWidth="$svgWidth" 
            :suffix="$suffix" 
        />
    @else
        <x-widget.graph.line 
            :series="$rawSeries" 
            :labels="$rawLabels" 
            :palette="$palette" 
            :grid="$grid" 
            :legend="$legend" 
            :markers="$markers" 
            :height="$renderHeight" 
            :svgWidth="$svgWidth" 
            :suffix="$suffix" 
        />
    @endif
</div>
