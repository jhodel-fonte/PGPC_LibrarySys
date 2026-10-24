@props([
    'series' => [],
    'labels' => [],
    'palette' => [
        '#0284c7', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#94a3b8'
    ],
    'grid' => false,
    'legend' => false,
    'markers' => false,
    'height' => 190,
    'svgWidth' => 520,
    'suffix' => '',
])

@php
    $renderHeight = (int) $height;
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

    // Normalize series
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

    // Determine scale
    $maxVal = 5;
    foreach ($lineSeries as $ls) {
        foreach ($ls['data'] as $v) {
            if ($v > $maxVal) $maxVal = $v;
        }
    }
    if ($maxVal <= 5) $maxScale = 5;
    elseif ($maxVal <= 10) $maxScale = 10;
    elseif ($maxVal <= 25) $maxScale = 25;
    elseif ($maxVal <= 50) $maxScale = 50;
    elseif ($maxVal <= 100) $maxScale = 100;
    else $maxScale = ceil($maxVal / 50) * 50;

    $ticks = [
        $maxScale,
        round($maxScale * 0.75),
        round($maxScale * 0.5),
        round($maxScale * 0.25),
        0
    ];

    $padLeft = $grid ? 32 : 12;
    $padRight = 24;
    $padTop = 14;
    $padBottom = 26;

    $plotW = $svgWidth - $padLeft - $padRight;
    $plotH = $renderHeight - $padTop - $padBottom;

    $numPoints = max(1, count($rawLabels));
@endphp

<div class="relative w-full overflow-x-auto min-w-0">
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
                    $points[] = ['x' => $x, 'y' => $y, 'val' => $val, 'ptIdx' => $ptIdx];
                }

                // Generate Sharp Point-to-Point Line (eliminates curve sagging below baseline)
                $pathD = '';
                if (count($points) > 0) {
                    $pathD = "M " . round($points[0]['x'], 1) . " " . round($points[0]['y'], 1);
                    for ($i = 1; $i < count($points); $i++) {
                        $pathD .= " L " . round($points[$i]['x'], 1) . " " . round($points[$i]['y'], 1);
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
                class="transition-all duration-200 hover:stroke-[3.5] cursor-pointer"
                @mouseenter="showTooltip($event, '{{ addslashes($ls['name']) }}', 'Trend Line', 'Total: {{ array_sum($ls['data']) }} {{ $suffix }}', '{{ $ls['color'] }}')"
                @mousemove="moveTooltip($event)"
                @mouseleave="hideTooltip()"
            />

            <!-- Markers -->
            @if ($markers)
                @foreach ($points as $p)
                    <!-- Visible dot (no CSS transform scale to prevent jumping/shifting) -->
                    <circle 
                        cx="{{ $p['x'] }}" 
                        cy="{{ $p['y'] }}" 
                        r="4" 
                        fill="{{ $ls['color'] }}" 
                        stroke="#FFFFFF" 
                        stroke-width="2" 
                        class="pointer-events-none transition-all duration-150"
                    />
                    <!-- Invisible expanded hover hit target for smooth cursor detection -->
                    <circle 
                        cx="{{ $p['x'] }}" 
                        cy="{{ $p['y'] }}" 
                        r="18" 
                        fill="transparent" 
                        class="cursor-pointer"
                        @mouseenter="showTooltip($event, '{{ addslashes($rawLabels[$p['ptIdx']] ?? '') }}', '{{ addslashes($ls['name']) }}', '{{ $p['val'] }} {{ $suffix }}', '{{ $ls['color'] }}')"
                        @mousemove="moveTooltip($event)"
                        @mouseleave="hideTooltip()"
                    />
                @endforeach
            @endif
        @endforeach

        <!-- X-Axis Labels -->
        @foreach ($rawLabels as $lblIdx => $label)
            @php
                $isFirst = ($lblIdx === 0);
                $isLast = ($lblIdx === count($rawLabels) - 1);
                $xPos = $padLeft + ($lblIdx / max(1, count($rawLabels) - 1)) * $plotW;
                $anchor = $isFirst ? 'start' : ($isLast ? 'end' : 'middle');
            @endphp
            <text 
                x="{{ $xPos }}" 
                y="{{ $renderHeight - 8 }}" 
                text-anchor="{{ $anchor }}" 
                class="text-[11px] font-semibold fill-slate-500"
            >
                {{ $label }}
            </text>
        @endforeach
    </svg>

    <!-- Bottom Legend -->
    @if ($legend && count($lineSeries) > 0)
        <div class="flex items-center justify-center flex-wrap gap-4 sm:gap-5 mt-3 pt-2 border-t border-slate-50">
            @foreach ($lineSeries as $ls)
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $ls['color'] }}"></span>
                    <span class="truncate">{{ $ls['name'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
