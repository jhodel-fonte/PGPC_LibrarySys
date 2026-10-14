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

<div {{ $attributes->merge(['class' => 'w-full flex flex-col justify-between select-none']) }}>
    @if ($resolvedType === 'donut')
        @php
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
            $outerR = 76;
            $innerR = 48;
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

                // Single slice covering whole circle
                if ($angleSpan >= (2 * M_PI - 0.001)) {
                    $pathD = "M " . ($cx - $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx + $outerR) . " $cy A $outerR $outerR 0 1 0 " . ($cx - $outerR) . " $cy M " . ($cx - $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx + $innerR) . " $cy A $innerR $innerR 0 1 1 " . ($cx - $innerR) . " $cy";
                } else {
                    $pathD = sprintf(
                        "M %.2f %.2f A %d %d 0 %d 1 %.2f %.2f L %.2f %.2f A %d %d 0 %d 0 %.2f %.2f Z",
                        $x1, $y1, $outerR, $outerR, $largeArc, $x2, $y2,
                        $x3, $y3, $innerR, $innerR, $largeArc, $x4, $y4
                    );
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
        @endphp

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
            <!-- Donut SVG with Center Label -->
            <div class="relative shrink-0 flex items-center justify-center">
                <svg viewBox="0 0 180 180" class="w-40 h-40">
                    @foreach ($slices as $slice)
                        <path 
                            d="{{ $slice['d'] }}" 
                            fill="{{ $slice['color'] }}" 
                            stroke="#FFFFFF" 
                            stroke-width="2.5" 
                            stroke-linejoin="round"
                            class="transition-all duration-300 hover:opacity-90"
                        />
                    @endforeach
                </svg>

                <!-- Center Text Overlay -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 leading-none">
                        {{ $centerVal }}
                    </span>
                    <span class="text-[11px] font-semibold text-slate-500 mt-1">
                        {{ $centerSub }}
                    </span>
                </div>
            </div>

            <!-- Legend on the Right -->
            <div class="flex-1 w-full max-w-[220px] space-y-2">
                @foreach ($slices as $slice)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0 pr-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $slice['color'] }}"></span>
                            <span class="font-semibold text-slate-700 truncate" title="{{ $slice['label'] }}">
                                {{ $slice['label'] }}
                            </span>
                        </div>
                        <span class="font-bold text-slate-900 tabular-nums shrink-0">
                            {{ $slice['percentage'] }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    @elseif ($resolvedType === 'bar')
        @php
            // Normalized Series for Bar Chart
            $barSeries = [];
            foreach ($rawSeries as $sIdx => $s) {
                if (is_array($s) && isset($s['data'])) {
                    $barSeries[] = [
                        'name' => $s['name'] ?? "Series " . ($sIdx + 1),
                        'data' => (array) $s['data'],
                        'color' => $palette[$sIdx % count($palette)],
                    ];
                } elseif (is_array($s)) {
                    $barSeries[] = [
                        'name' => "Series " . ($sIdx + 1),
                        'data' => $s,
                        'color' => $palette[$sIdx % count($palette)],
                    ];
                }
            }

            // Calculate Max Value for scale
            $maxBarVal = 10;
            foreach ($barSeries as $bs) {
                foreach ($bs['data'] as $v) {
                    if ($v > $maxBarVal) $maxBarVal = $v;
                }
            }
            // Round up to clean step (e.g. 50, 40, etc.)
            if ($maxBarVal <= 10) $maxScale = 10;
            elseif ($maxBarVal <= 20) $maxScale = 20;
            elseif ($maxBarVal <= 30) $maxScale = 30;
            elseif ($maxBarVal <= 50) $maxScale = 50;
            else $maxScale = ceil($maxBarVal / 20) * 20;

            $ticks = [
                $maxScale,
                round($maxScale * 0.8),
                round($maxScale * 0.6),
                round($maxScale * 0.4),
                round($maxScale * 0.2),
                0
            ];

            $padLeft = $grid ? 28 : 10;
            $padRight = 12;
            $padTop = 14;
            $padBottom = 26;

            $plotW = $svgWidth - $padLeft - $padRight;
            $plotH = $renderHeight - $padTop - $padBottom;

            $numCats = max(1, count($rawLabels));
            $slotW = $plotW / $numCats;
            $numSeries = max(1, count($barSeries));
            $barW = min(11, ($slotW * 0.72) / $numSeries);
            $barGap = 2;
        @endphp

        <div class="relative w-full">
            <svg viewBox="0 0 {{ $svgWidth }} {{ $renderHeight }}" class="w-full h-auto overflow-visible">
                <!-- Grid Lines & Left Y-Axis Ticks -->
                @if ($grid)
                    @foreach ($ticks as $tick)
                        @php
                            $yPos = $padTop + (1 - ($tick / $maxScale)) * $plotH;
                        @endphp
                        <line 
                            x1="{{ $padLeft }}" 
                            y1="{{ $yPos }}" 
                            x2="{{ $svgWidth - $padRight }}" 
                            y2="{{ $yPos }}" 
                            stroke="#F1F5F9" 
                            stroke-width="1.2" 
                        />
                        <text 
                            x="{{ $padLeft - 8 }}" 
                            y="{{ $yPos + 3.5 }}" 
                            text-anchor="end" 
                            class="text-[10px] font-semibold fill-slate-400"
                        >
                            {{ $tick }}
                        </text>
                    @endforeach
                @endif

                <!-- Bars per category -->
                @foreach ($rawLabels as $catIdx => $label)
                    @php
                        $groupTotalW = ($numSeries * $barW) + (($numSeries - 1) * $barGap);
                        $groupStartX = $padLeft + ($catIdx * $slotW) + (($slotW - $groupTotalW) / 2);
                    @endphp

                    @foreach ($barSeries as $sIdx => $bs)
                        @php
                            $val = $bs['data'][$catIdx] ?? 0;
                            $barH = max(2, ($val / $maxScale) * $plotH);
                            $bX = $groupStartX + ($sIdx * ($barW + $barGap));
                            $bY = ($padTop + $plotH) - $barH;
                        @endphp
                        <rect 
                            x="{{ $bX }}" 
                            y="{{ $bY }}" 
                            width="{{ $barW }}" 
                            height="{{ $barH }}" 
                            rx="2.5" 
                            fill="{{ $bs['color'] }}" 
                            class="transition-all duration-300 hover:opacity-85"
                        >
                            <title>{{ $bs['name'] }}: {{ $val }}</title>
                        </rect>
                    @endforeach

                    <!-- X-Axis Label -->
                    <text 
                        x="{{ $padLeft + ($catIdx * $slotW) + ($slotW / 2) }}" 
                        y="{{ $renderHeight - 8 }}" 
                        text-anchor="middle" 
                        class="text-[11px] font-semibold fill-slate-500"
                    >
                        {{ $label }}
                    </text>
                @endforeach
            </svg>

            <!-- Bottom Legend -->
            @if ($legend && count($barSeries) > 0)
                <div class="flex items-center justify-center gap-5 mt-3 pt-2 border-t border-slate-50">
                    @foreach ($barSeries as $bs)
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $bs['color'] }}"></span>
                            <span>{{ $bs['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    @else
        {{-- Line / Multiple Series Chart --}}
        @php
            $lineSeries = [];
            foreach ($rawSeries as $sIdx => $s) {
                if (is_array($s) && isset($s['data'])) {
                    $lineSeries[] = [
                        'name' => $s['name'] ?? "Series " . ($sIdx + 1),
                        'data' => (array) $s['data'],
                        'color' => $palette[$sIdx % count($palette)],
                    ];
                } elseif (is_array($s)) {
                    $lineSeries[] = [
                        'name' => "Series " . ($sIdx + 1),
                        'data' => $s,
                        'color' => $palette[$sIdx % count($palette)],
                    ];
                }
            }

            // Calculate Max Value for scale
            $maxLineVal = 5;
            foreach ($lineSeries as $ls) {
                foreach ($ls['data'] as $v) {
                    if ($v > $maxLineVal) $maxLineVal = $v;
                }
            }
            // Round up to clean step (e.g. 20, 15, etc.)
            if ($maxLineVal <= 5) $maxScale = 5;
            elseif ($maxLineVal <= 10) $maxScale = 10;
            elseif ($maxLineVal <= 20) $maxScale = 20;
            elseif ($maxLineVal <= 30) $maxScale = 30;
            elseif ($maxLineVal <= 50) $maxScale = 50;
            else $maxScale = ceil($maxLineVal / 10) * 10;

            $ticks = [
                $maxScale,
                round($maxScale * 0.75),
                round($maxScale * 0.5),
                round($maxScale * 0.25),
                0
            ];

            $padLeft = $grid ? 28 : 10;
            $padRight = 14;
            $padTop = 14;
            $padBottom = 26;

            $plotW = $svgWidth - $padLeft - $padRight;
            $plotH = $renderHeight - $padTop - $padBottom;

            $numPoints = max(1, count($rawLabels));
        @endphp

        <div class="relative w-full">
            <svg viewBox="0 0 {{ $svgWidth }} {{ $renderHeight }}" class="w-full h-auto overflow-visible">
                <!-- Grid Lines & Left Y-Axis Ticks -->
                @if ($grid)
                    @foreach ($ticks as $tick)
                        @php
                            $yPos = $padTop + (1 - ($tick / $maxScale)) * $plotH;
                        @endphp
                        <line 
                            x1="{{ $padLeft }}" 
                            y1="{{ $yPos }}" 
                            x2="{{ $svgWidth - $padRight }}" 
                            y2="{{ $yPos }}" 
                            stroke="#F1F5F9" 
                            stroke-width="1.2" 
                        />
                        <text 
                            x="{{ $padLeft - 8 }}" 
                            y="{{ $yPos + 3.5 }}" 
                            text-anchor="end" 
                            class="text-[10px] font-semibold fill-slate-400"
                        >
                            {{ $tick }}
                        </text>
                    @endforeach
                @endif

                <!-- Series Curves & Markers -->
                @foreach ($lineSeries as $sIdx => $ls)
                    @php
                        $points = [];
                        $dataCount = count($ls['data']);
                        foreach ($ls['data'] as $ptIdx => $val) {
                            $x = $padLeft + ($ptIdx / max(1, $dataCount - 1)) * $plotW;
                            $y = $padTop + (1 - ($val / $maxScale)) * $plotH;
                            $points[] = ['x' => $x, 'y' => $y, 'val' => $val];
                        }

                        // Generate Monotone Cubic Bezier Spline
                        $pathD = '';
                        if (count($points) > 0) {
                            $pathD = "M " . round($points[0]['x'], 1) . " " . round($points[0]['y'], 1);
                            for ($i = 0; $i < count($points) - 1; $i++) {
                                $p0 = $points[max(0, $i - 1)];
                                $p1 = $points[$i];
                                $p2 = $points[$i + 1];
                                $p3 = $points[min(count($points) - 1, $i + 2)];

                                $cp1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                                $cp1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                                $cp2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                                $cp2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;

                                $pathD .= sprintf(" C %.1f %.1f, %.1f %.1f, %.1f %.1f", $cp1x, $cp1y, $cp2x, $cp2y, $p2['x'], $p2['y']);
                            }
                        }
                    @endphp

                    <!-- Curve Path -->
                    <path 
                        d="{{ $pathD }}" 
                        fill="none" 
                        stroke="{{ $ls['color'] }}" 
                        stroke-width="2.5" 
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <!-- Markers -->
                    @if ($markers)
                        @foreach ($points as $p)
                            <circle 
                                cx="{{ $p['x'] }}" 
                                cy="{{ $p['y'] }}" 
                                r="4" 
                                fill="{{ $ls['color'] }}" 
                                stroke="#FFFFFF" 
                                stroke-width="2" 
                                class="transition-transform duration-200 hover:scale-125"
                            >
                                <title>{{ $ls['name'] }}: {{ $p['val'] }}</title>
                            </circle>
                        @endforeach
                    @endif
                @endforeach

                <!-- X-Axis Labels -->
                @foreach ($rawLabels as $lblIdx => $label)
                    @php
                        $xPos = $padLeft + ($lblIdx / max(1, count($rawLabels) - 1)) * $plotW;
                    @endphp
                    <text 
                        x="{{ $xPos }}" 
                        y="{{ $renderHeight - 8 }}" 
                        text-anchor="middle" 
                        class="text-[11px] font-semibold fill-slate-500"
                    >
                        {{ $label }}
                    </text>
                @endforeach
            </svg>

            <!-- Bottom Legend -->
            @if ($legend && count($lineSeries) > 0)
                <div class="flex items-center justify-center gap-5 mt-3 pt-2 border-t border-slate-50">
                    @foreach ($lineSeries as $ls)
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $ls['color'] }}"></span>
                            <span>{{ $ls['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
