@props([
    'series' => [],
    'labels' => [],
    'colors' => null,
    'palette' => null,
    'pie' => false,
    'donut' => false,
    'legend' => false,
    'legendPosition' => 'bottom', // 'bottom', 'right', 'top'
    'height' => 240,
    'centerLabel' => null,
    'centerValueSize' => 'text-2xl sm:text-[28px]',
    'centerSubSize' => 'text-[11px] sm:text-xs',
    'legendTextSize' => 'text-xs sm:text-sm',
    'legendValueSize' => 'text-xs sm:text-sm',
    'sliceLabelSize' => 'text-[11px] sm:text-xs',
    'showSliceLabels' => false,
    'showTooltip' => true,
    'animated' => true,
    'suffix' => '',
])

@php
    // Determine whether chart is donut or pie
    $isDonut = $donut || (!$pie && $centerLabel !== null);
    if ($pie) {
        $isDonut = false;
    }

    // Color name mapping for standard Tailwind colors or Hex
    $colorMap = [
        'indigo' => '#6366F1',
        'sky' => '#0284C7',
        'blue' => '#3B82F6',
        'navy' => '#102B70',
        'gold' => '#FCC719',
        'amber' => '#F59E0B',
        'emerald' => '#10B981',
        'teal' => '#14B8A6',
        'rose' => '#F43F5E',
        'slate' => '#64748B',
        'purple' => '#8B5CF6',
        'gray' => '#94A3B8',
        'cyan' => '#06B6D4',
        'violet' => '#7C3AED',
    ];

    // Standard default palette following PGPC Library brand tokens
    $defaultPalette = ['#102B70', '#3B82F6', '#FCC719', '#10B981', '#F43F5E', '#8B5CF6'];

    $rawColors = $colors ?? $palette ?? $defaultPalette;
    if (!is_array($rawColors)) {
        $rawColors = [$rawColors];
    }

    $activePalette = [];
    foreach ($rawColors as $c) {
        $lower = strtolower(trim((string)$c));
        $activePalette[] = $colorMap[$lower] ?? $c;
    }
    if (empty($activePalette)) {
        $activePalette = $defaultPalette;
    }

    // Parse series and labels
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

    $values = [];
    foreach ($rawSeries as $s) {
        if (is_array($s) && isset($s['data'])) {
            $values[] = is_array($s['data']) ? ($s['data'][0] ?? 0) : $s['data'];
        } elseif (is_numeric($s)) {
            $values[] = (float) $s;
        }
    }

    $totalSum = array_sum($values);
    if ($totalSum <= 0) $totalSum = 1;

    // SVG Geometry
    $cx = 100;
    $cy = 100;
    $outerR = 86;
    $innerR = $isDonut ? 56 : 0;
    $startAngle = -M_PI / 2; // Start from top 12 o'clock

    $slices = [];
    $countValues = count($values);

    foreach ($values as $idx => $val) {
        $percentage = ($val / $totalSum) * 100;
        $angleSpan = ($val / $totalSum) * (2 * M_PI);
        $endAngle = $startAngle + $angleSpan;
        $midAngle = $startAngle + ($angleSpan / 2);

        // Arc points
        $x1 = $cx + $outerR * cos($startAngle);
        $y1 = $cy + $outerR * sin($startAngle);
        $x2 = $cx + $outerR * cos($endAngle);
        $y2 = $cy + $outerR * sin($endAngle);

        $x3 = $cx + $innerR * cos($endAngle);
        $y3 = $cy + $innerR * sin($endAngle);
        $x4 = $cx + $innerR * cos($startAngle);
        $y4 = $cy + $innerR * sin($startAngle);

        $largeArc = $angleSpan > M_PI ? 1 : 0;

        if ($countValues === 1 || $angleSpan >= (2 * M_PI - 0.001)) {
            if ($isDonut) {
                $pathD = "M " . ($cx - $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx + $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx - $outerR) . " $cy M " . ($cx - $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx + $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx - $innerR) . " $cy";
            } else {
                $pathD = "M " . ($cx - $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx + $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx - $outerR) . " $cy";
            }
        } else {
            if ($isDonut) {
                $pathD = sprintf(
                    "M %.2f %.2f A %d %d 0 %d 1 %.2f %.2f L %.2f %.2f A %d %d 0 %d 0 %.2f %.2f Z",
                    $x1, $y1, $outerR, $outerR, $largeArc, $x2, $y2,
                    $x3, $y3, $innerR, $innerR, $largeArc, $x4, $y4
                );
            } else {
                $pathD = sprintf(
                    "M %d %d L %.2f %.2f A %d %d 0 %d 1 %.2f %.2f Z",
                    $cx, $cy, $x1, $y1, $outerR, $outerR, $largeArc, $x2, $y2
                );
            }
        }

        // Midpoint coordinates for on-slice label
        $labelRadius = $isDonut ? (($outerR + $innerR) / 2) : ($outerR * 0.65);
        $labelX = $cx + $labelRadius * cos($midAngle);
        $labelY = $cy + $labelRadius * sin($midAngle);

        $slices[] = [
            'index' => $idx,
            'd' => $pathD,
            'color' => $activePalette[$idx % count($activePalette)],
            'label' => $rawLabels[$idx] ?? "Category " . ($idx + 1),
            'val' => $val,
            'percentage' => round($percentage),
            'labelX' => round($labelX, 2),
            'labelY' => round($labelY, 2),
            'showLabel' => $percentage >= 8,
        ];

        $startAngle = $endAngle;
    }

    // Center Label text & subtitle
    $centerVal = is_array($centerLabel) ? ($centerLabel['value'] ?? number_format($totalSum)) : ($centerLabel ?? number_format($totalSum));
    $centerSub = is_array($centerLabel) ? ($centerLabel['label'] ?? 'Total') : 'Total';

    // Height sizing
    $renderHeight = (int) $height;
@endphp

<div 
    x-data="{
        inView: false,
        hoveredIndex: null,
        tooltipVisible: false,
        tooltipTitle: '',
        tooltipPct: '',
        tooltipValue: '',
        tooltipColor: '',
        tooltipX: 0,
        tooltipY: 0,
        init() {
            if (!{{ $animated ? 'true' : 'false' }}) {
                this.inView = true;
                return;
            }
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.inView = true;
                return;
            }
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(() => { this.inView = true; }, 80);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });
                observer.observe(this.$el);
            } else {
                this.inView = true;
            }
        },
        showTooltip(e, title, pct, value, color, idx) {
            this.hoveredIndex = idx;
            this.tooltipTitle = title;
            this.tooltipPct = pct;
            this.tooltipValue = value;
            this.tooltipColor = color;
            this.tooltipVisible = true;
            this.moveTooltip(e);
        },
        moveTooltip(e) {
            const rect = this.$el.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            this.tooltipX = Math.max(65, Math.min(rect.width - 65, x));
            this.tooltipY = Math.max(15, y);
        },
        hideTooltip() {
            this.tooltipVisible = false;
            this.hoveredIndex = null;
        }
    }"
    @mousemove="if (tooltipVisible) moveTooltip($event)"
    @mouseleave="hideTooltip()"
    {{ $attributes->merge(['class' => 'relative w-full min-w-0 flex flex-col items-center justify-center select-none']) }}
    style="min-height: {{ $renderHeight }}px;"
>
    <!-- Floating Interactive Tooltip -->
    @if ($showTooltip)
        <div 
            x-cloak
            x-show="tooltipVisible"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-none absolute z-50 bg-white/95 text-slate-900 rounded-xl shadow-[0_12px_30px_-4px_rgba(15,23,42,0.18),0_4px_10px_-2px_rgba(15,23,42,0.06)] px-3.5 py-2.5 border border-slate-200/90 backdrop-blur-md min-w-[150px] whitespace-nowrap will-change-transform"
            :style="tooltipY < 75 
                ? `left: ${tooltipX}px; top: ${tooltipY + 16}px; transform: translate(-50%, 0);` 
                : `left: ${tooltipX}px; top: ${tooltipY - 14}px; transform: translate(-50%, -100%);`"
        >
            <div class="pb-1.5 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs" :style="`background-color: ${tooltipColor}`"></span>
                    <span class="font-bold text-xs sm:text-[13px] text-[#0F172A]" x-text="tooltipTitle"></span>
                </div>
                <span class="text-xs font-black text-[#102B70]" x-text="tooltipPct"></span>
            </div>
            <div class="pt-1.5 flex items-center justify-between gap-4 text-xs">
                <span class="text-slate-500 font-medium">Count</span>
                <span class="font-bold text-[#0F172A] tabular-nums" x-text="tooltipValue"></span>
            </div>
        </div>
    @endif

    <!-- Chart & Legend Container -->
    <div class="flex {{ $legendPosition === 'right' ? 'flex-col sm:flex-row items-center justify-center gap-6 sm:gap-8' : 'flex-col items-center justify-center gap-4' }} w-full min-w-0">
        
        <!-- SVG Canvas with Center Label & Slice Animation -->
        <div 
            class="relative shrink-0 flex items-center justify-center transition-all duration-700 ease-out will-change-[transform,opacity]"
            :class="inView ? 'scale-100 rotate-0 opacity-100' : 'scale-85 -rotate-12 opacity-0'"
            style="width: min({{ $renderHeight }}px, 100%); height: min({{ $renderHeight }}px, 100%); max-width: 260px; max-height: 260px;"
        >
            <svg viewBox="0 0 200 200" class="w-full h-full drop-shadow-xs overflow-visible">
                @if ($isDonut)
                    <!-- Background subtle ring track -->
                    <circle cx="100" cy="100" r="{{ ($outerR + $innerR) / 2 }}" fill="none" stroke="#F1F5F9" stroke-width="{{ $outerR - $innerR }}" />
                @endif

                <!-- Slices with dynamic hover pop & dimming of other slices -->
                @foreach ($slices as $slice)
                    <path 
                        d="{{ $slice['d'] }}" 
                        fill="{{ $slice['color'] }}" 
                        stroke="#FFFFFF" 
                        stroke-width="2.5" 
                        stroke-linejoin="round"
                        class="cursor-pointer transition-all duration-200"
                        :class="hoveredIndex === {{ $slice['index'] }} ? 'scale-[1.045] opacity-100 filter drop-shadow-md' : (hoveredIndex !== null ? 'opacity-50 scale-[0.98]' : 'opacity-100')"
                        style="transform-origin: 100px 100px;"
                        @mouseenter="showTooltip($event, '{{ addslashes($slice['label']) }}', '{{ $slice['percentage'] }}%', '{{ number_format($slice['val']) }}{{ $suffix ? ' ' . $suffix : '' }}', '{{ $slice['color'] }}', {{ $slice['index'] }})"
                        @mousemove="moveTooltip($event)"
                        @mouseleave="hideTooltip()"
                    />

                    @if ($showSliceLabels && $slice['showLabel'])
                        <text 
                            x="{{ $slice['labelX'] }}" 
                            y="{{ $slice['labelY'] }}" 
                            text-anchor="middle" 
                            dominant-baseline="central" 
                            fill="#FFFFFF" 
                            class="{{ $sliceLabelSize }} font-bold pointer-events-none drop-shadow-xs transition-opacity duration-200"
                            :class="hoveredIndex !== null && hoveredIndex !== {{ $slice['index'] }} ? 'opacity-40' : 'opacity-100'"
                        >
                            {{ $slice['percentage'] }}%
                        </text>
                    @endif
                @endforeach
            </svg>

            @if ($isDonut)
                <!-- Donut Center Label Overlay with customizable font sizes -->
                <div 
                    class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center transition-all duration-700 delay-150 ease-out"
                    :class="inView ? 'opacity-100 scale-100' : 'opacity-0 scale-90'"
                >
                    <span class="{{ $centerValueSize }} font-black tracking-tight text-[#0F172A] leading-none">
                        {{ $centerVal }}
                    </span>
                    <span class="{{ $centerSubSize }} font-semibold text-slate-500 mt-1 max-w-[85px] leading-tight truncate">
                        {{ $centerSub }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Legend (Configurable Position & Sizes) -->
        @if ($legend)
            <div 
                class="{{ $legendPosition === 'right' 
                    ? 'w-full sm:w-auto shrink-0 space-y-1.5' 
                    : 'flex flex-wrap items-center justify-center gap-x-5 gap-y-2 w-full pt-1' }} transition-all duration-700 delay-150 ease-out will-change-[transform,opacity]"
                :class="inView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
            >
                @foreach ($slices as $slice)
                    <div 
                        class="flex items-center gap-2.5 py-1 px-2.5 rounded-lg transition-all duration-150 cursor-pointer"
                        :class="hoveredIndex === {{ $slice['index'] }} ? 'bg-slate-100/90 ring-1 ring-slate-200 scale-102' : (hoveredIndex !== null ? 'opacity-50' : 'hover:bg-slate-50')"
                        @mouseenter="showTooltip($event, '{{ addslashes($slice['label']) }}', '{{ $slice['percentage'] }}%', '{{ number_format($slice['val']) }}{{ $suffix ? ' ' . $suffix : '' }}', '{{ $slice['color'] }}', {{ $slice['index'] }})"
                        @mousemove="moveTooltip($event)"
                        @mouseleave="hideTooltip()"
                    >
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $slice['color'] }}"></span>
                        <span class="font-semibold text-slate-700 {{ $legendTextSize }}" title="{{ $slice['label'] }}">
                            {{ $slice['label'] }}
                        </span>
                        <span class="font-bold text-[#102B70] tabular-nums {{ $legendValueSize }} ml-1">
                            {{ $slice['percentage'] }}%
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
