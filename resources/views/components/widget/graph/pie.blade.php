@props([
    'series' => [],
    'labels' => [],
    'palette' => [
        '#0284c7', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#94a3b8'
    ],
    'centerLabel' => null,
    'donut' => true,
    'legend' => false,
    'suffix' => '',
    'height' => 100,
])

@php
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

    $donutValues = [];
    foreach ($rawSeries as $s) {
        if (is_array($s) && isset($s['data'])) {
            $donutValues[] = is_array($s['data']) ? ($s['data'][0] ?? 0) : $s['data'];
        } elseif (is_numeric($s)) {
            $donutValues[] = (float) $s;
        }
    }
    $totalSum = array_sum($donutValues);
    if ($totalSum <= 0) $totalSum = 1;

    $cx = 90;
    $cy = 90;
    $outerR = 80;
    $innerR = $donut ? 54 : 0;
    $startAngle = -M_PI / 2;

    $slices = [];
    foreach ($donutValues as $idx => $val) {
        $percentage = ($val / $totalSum) * 100;
        $angleSpan = ($val / $totalSum) * (2 * M_PI);
        $endAngle = $startAngle + $angleSpan;

        // Points on outer arc
        $x1 = $cx + $outerR * cos($startAngle);
        $y1 = $cy + $outerR * sin($startAngle);
        $x2 = $cx + $outerR * cos($endAngle);
        $y2 = $cy + $outerR * sin($endAngle);

        // Points on inner arc
        $x3 = $cx + $innerR * cos($endAngle);
        $y3 = $cy + $innerR * sin($endAngle);
        $x4 = $cx + $innerR * cos($startAngle);
        $y4 = $cy + $innerR * sin($startAngle);

        $largeArc = $angleSpan > M_PI ? 1 : 0;

        if ($angleSpan >= (2 * M_PI - 0.001)) {
            if ($donut) {
                $pathD = "M " . ($cx - $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx + $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx - $outerR) . " $cy M " . ($cx - $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx + $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx - $innerR) . " $cy";
            } else {
                $pathD = "M " . ($cx - $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx + $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx - $outerR) . " $cy";
            }
        } else {
            if ($donut) {
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

        $slices[] = [
            'd' => $pathD,
            'color' => $palette[$idx % count($palette)],
            'label' => $rawLabels[$idx] ?? "Category " . ($idx + 1),
            'val' => $val,
            'percentage' => round($percentage),
        ];

        $startAngle = $endAngle;
    }

    $centerVal = is_array($centerLabel) ? ($centerLabel['value'] ?? $totalSum) : ($centerLabel ?? $totalSum);
    $centerSub = is_array($centerLabel) ? ($centerLabel['label'] ?? 'Total') : 'Total';
    $renderHeight = (int) $height;
    $svgSizeClass = match(true) {
        $renderHeight >= 220 => 'w-50 h-50 sm:w-50 sm:h-50',
        default => 'w-36 h-36 sm:w-[155px] sm:h-[155px] xl:w-[165px] xl:h-[165px]',
    };
@endphp

<div class="flex flex-col sm:flex-row items-center justify-center w-full min-w-0 gap-6 sm:gap-8 py-1 my-auto">
    <!-- SVG with Center Label -->
    <div 
        class="relative shrink-0 flex items-center justify-center transition-all duration-1000 ease-out will-change-[transform,opacity]"
        :class="(typeof inView !== 'undefined' ? inView : true) ? 'scale-100 rotate-0 opacity-100' : 'scale-80 -rotate-45 opacity-0'"
    >
        <svg viewBox="0 0 180 180" class="{{ $svgSizeClass }} drop-shadow-xs">
            @if ($donut)
                <!-- Base ring background track -->
                <circle cx="90" cy="90" r="67" fill="none" stroke="#F1F5F9" stroke-width="26" />
            @endif
            @foreach ($slices as $slice)
                <path 
                    d="{{ $slice['d'] }}" 
                    fill="{{ $slice['color'] }}" 
                    stroke="#FFFFFF" 
                    stroke-width="2.5" 
                    stroke-linejoin="round"
                    class="transition-all duration-200 hover:opacity-85 hover:stroke-[3.5] cursor-pointer"
                    @mouseenter="showTooltip($event, '{{ addslashes($slice['label']) }}', 'Category', '{{ $slice['percentage'] }}% ({{ number_format($slice['val']) }})', '{{ $slice['color'] }}')"
                    @mousemove="moveTooltip($event)"
                    @mouseleave="hideTooltip()"
                />
            @endforeach
        </svg>

        @if ($donut)
            <!-- Center Text Overlay -->
            <div 
                class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center transition-all duration-700 delay-200 ease-out"
                :class="(typeof inView !== 'undefined' ? inView : true) ? 'opacity-100 scale-100' : 'opacity-0 scale-90'"
            >
                <span class="text-2xl sm:text-[28px] font-black tracking-tight text-[#0F172A] leading-none">
                    {{ $centerVal }}
                </span>
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 mt-1 max-w-[85px] leading-tight">
                    {{ $centerSub }}
                </span>
            </div>
        @endif
    </div>

    <!-- Legend on the Right: Moved near percentage with reduced distance and larger subject text -->
    @if ($legend)
        <div 
            class="w-full sm:w-auto shrink-0 sm:min-w-[190px] sm:max-w-[230px] space-y-1 transition-all duration-700 delay-150 ease-out will-change-[transform,opacity]"
            :class="(typeof inView !== 'undefined' ? inView : true) ? 'translate-x-0 opacity-100' : 'translate-x-4 opacity-0'"
        >
            @foreach ($slices as $slice)
                <div 
                    class="flex items-center justify-between gap-3 min-w-0 py-0.5 px-2 -mx-2 rounded-lg transition-colors hover:bg-slate-50 cursor-pointer"
                    @mouseenter="showTooltip($event, '{{ addslashes($slice['label']) }}', 'Category', '{{ $slice['percentage'] }}% ({{ number_format($slice['val']) }})', '{{ $slice['color'] }}')"
                    @mousemove="moveTooltip($event)"
                    @mouseleave="hideTooltip()"
                >
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $slice['color'] }}"></span>
                        <span class="font-semibold text-slate-700 truncate text-[13px] sm:text-sm" title="{{ $slice['label'] }}">
                            {{ $slice['label'] }}
                        </span>
                    </div>
                    <span class="font-bold text-[#0F172A] tabular-nums shrink-0 text-xs sm:text-sm ml-auto pl-1">
                        {{ $slice['percentage'] }}%
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
