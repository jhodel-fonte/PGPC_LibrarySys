@props([
    'series' => [],
    'labels' => [],
    'palette' => [
        '#0284c7', '#102B70', '#f59e0b', '#94a3b8'
    ],
    'grid' => false,
    'legend' => false,
    'markers' => false,
    'height' => 150,
    'svgWidth' => 480,
    'suffix' => '',
    'compact' => false,
])

@php
    $renderHeight = (int) $height;
    $svgWidth = (int) ($svgWidth ?? 480);
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
    $maxVal = 4;
    foreach ($lineSeries as $ls) {
        foreach ($ls['data'] as $v) {
            if ($v > $maxVal) $maxVal = $v;
        }
    }
    if ($maxVal <= 4) $maxScale = 4;
    elseif ($maxVal <= 10) $maxScale = 10;
    elseif ($maxVal <= 25) $maxScale = 25;
    elseif ($maxVal <= 50) $maxScale = 50;
    elseif ($maxVal <= 100) $maxScale = 100;
    else $maxScale = ceil($maxVal / 50) * 50;

    // 4 clean ticks (Max, 2/3, 1/3, 0) like modern Material dashboards
    if ($maxScale <= 4) {
        $ticks = [4, 2, 0];
    } else {
        $ticks = [
            $maxScale,
            round($maxScale * 0.66),
            round($maxScale * 0.33),
            0
        ];
    }

    $padLeft = $grid ? 38 : 14;
    $padRight = 16;
    $padTop = 14;
    $padBottom = 26;

    $plotW = $svgWidth - $padLeft - $padRight;
    $plotH = $renderHeight - $padTop - $padBottom;

    $numPoints = max(1, count($rawLabels));

    // Precalculate all points and series data for smooth proximity detection
    $allSeriesPoints = [];
    foreach ($lineSeries as $sIdx => $ls) {
        $pts = [];
        $dataCount = count($ls['data']);
        foreach ($ls['data'] as $ptIdx => $val) {
            $x = $padLeft + ($ptIdx / max(1, $dataCount - 1)) * $plotW;
            $y = $padTop + (1 - ($val / $maxScale)) * $plotH;
            $pts[] = [
                'x' => round($x, 1),
                'y' => round($y, 1),
                'val' => $val,
                'ptIdx' => $ptIdx,
                'label' => $rawLabels[$ptIdx] ?? ''
            ];
        }
        $allSeriesPoints[] = [
            'name' => $ls['name'],
            'color' => $ls['color'],
            'suffix' => $suffix,
            'points' => $pts,
        ];
    }
@endphp

<div class="relative w-full overflow-x-auto min-w-0">
    <svg 
        x-ref="lineSvg"
        viewBox="0 0 {{ $svgWidth }} {{ $renderHeight }}" 
        class="w-full h-auto overflow-visible cursor-crosshair select-none"
        @mousemove="checkColumnHover($event, $refs.lineSvg, {{ json_encode($allSeriesPoints) }}, {{ $svgWidth }}, {{ $renderHeight }}, {{ $padLeft }}, {{ $padRight }})"
        @mouseleave="hideTooltip()"
    >
        <!-- Grid Lines & Left Y-Axis Ticks -->
        @if ($grid)
            @foreach ($ticks as $tick)
                @php
                    $yPos = $padTop + (1 - ($tick / $maxScale)) * $plotH;
                    $isZero = ($tick === 0);
                @endphp
                <line 
                    x1="{{ $padLeft }}" 
                    y1="{{ $yPos }}" 
                    x2="{{ $svgWidth - $padRight }}" 
                    y2="{{ $yPos }}" 
                    stroke="{{ $isZero ? '#000000' : '#F1F5F9' }}" 
                    stroke-width="{{ $isZero ? '1.5' : '1.2' }}" 
                />
                <text 
                    x="{{ $padLeft - 8 }}" 
                    y="{{ $yPos + 4 }}" 
                    text-anchor="end" 
                    style="font-size: {{ $compact ? '10px' : '13px' }}; font-weight: 600;"
                    class="fill-black select-none"
                >
                    {{ $tick }}
                </text>
            @endforeach
        @endif

        <!-- Active Column Guide Line & Points Indicator (Shows all series points in that column) -->
        <g x-show="activeColumn && tooltipVisible" class="pointer-events-none transition-opacity duration-75">
            <!-- Vertical Guide Line for Column -->
            <line 
                :x1="activeColumn ? activeColumn.x : -100" 
                :y1="{{ $padTop }}" 
                :x2="activeColumn ? activeColumn.x : -100" 
                :y2="{{ $renderHeight - $padBottom }}" 
                stroke="#94A3B8" 
                stroke-width="1.2" 
                stroke-dasharray="3 3"
            />
            <!-- Glowing dots on both/all series in this column without template x-for scoping issues -->
            @foreach ($allSeriesPoints as $sIdx => $ls)
                <g x-show="activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]">
                    <circle 
                        :cx="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].x : -100" 
                        :cy="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].y : -100" 
                        r="8" 
                        :fill="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].color : '{{ $ls['color'] }}'" 
                        opacity="0.25" 
                    />
                    <circle 
                        :cx="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].x : -100" 
                        :cy="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].y : -100" 
                        r="4.5" 
                        :fill="(activeColumn && activeColumn.dots && activeColumn.dots[{{ $sIdx }}]) ? activeColumn.dots[{{ $sIdx }}].color : '{{ $ls['color'] }}'" 
                        stroke="#FFFFFF" 
                        stroke-width="2" 
                    />
                </g>
            @endforeach
        </g>

        <!-- Series Curves & Markers with Smooth Sweep Reveal on Scroll -->
        <g 
            class="transition-all duration-1000 ease-out will-change-[clip-path,opacity]"
            :style="(typeof inView !== 'undefined' ? inView : true) ? 'clip-path: inset(0 0% 0 0); opacity: 1;' : 'clip-path: inset(0 100% 0 0); opacity: 0;'"
        >
            @foreach ($allSeriesPoints as $sIdx => $ls)
                @php
                    $points = $ls['points'];

                    // Generate Sharp Point-to-Point Line (eliminates curve sagging below baseline)
                    $pathD = '';
                    if (count($points) > 0) {
                        $pathD = "M " . $points[0]['x'] . " " . $points[0]['y'];
                        for ($i = 1; $i < count($points); $i++) {
                            $pathD .= " L " . $points[$i]['x'] . " " . $points[$i]['y'];
                        }
                    }
                @endphp

                <!-- Visible Line Path -->
                <path 
                    d="{{ $pathD }}" 
                    fill="none" 
                    stroke="{{ $ls['color'] }}" 
                    stroke-width="2.5" 
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="transition-all duration-150 select-none pointer-events-none"
                />

                <!-- Visible Base Markers (like the green line chart in sample dashboard) -->
                @php
                    $markerR = count($points) > 15 ? 2.2 : 3.5;
                    $strokeW = count($points) > 15 ? 1.2 : 1.8;
                @endphp
                @foreach ($points as $p)
                    <circle 
                        cx="{{ $p['x'] }}" 
                        cy="{{ $p['y'] }}" 
                        r="{{ $markerR }}" 
                        fill="{{ $ls['color'] }}" 
                        stroke="#FFFFFF" 
                        stroke-width="{{ $strokeW }}" 
                        class="pointer-events-none transition-all duration-150"
                    />
                @endforeach
            @endforeach
        </g>

        @php
            $totalLabels = count($rawLabels);
            $indicesToShow = [];
            if ($totalLabels <= 8) {
                $indicesToShow = array_flip(range(0, $totalLabels - 1));
            } else {
                $targetCount = 6;
                for ($i = 0; $i < $targetCount; $i++) {
                    $idx = (int) round(($i / ($targetCount - 1)) * ($totalLabels - 1));
                    $indicesToShow[$idx] = true;
                }
            }
        @endphp

        <!-- X-Axis Labels (Evenly sampled when there are many dates) -->
        @foreach ($rawLabels as $lblIdx => $label)
            @if (isset($indicesToShow[$lblIdx]))
                @php
                    $isFirst = ($lblIdx === 0);
                    $isLast = ($lblIdx === $totalLabels - 1);
                    $xPos = $padLeft + ($lblIdx / max(1, $totalLabels - 1)) * $plotW;
                    $anchor = $isFirst ? 'start' : ($isLast ? 'end' : 'middle');
                @endphp
                <text 
                    x="{{ $xPos }}" 
                    y="{{ $renderHeight - 5 }}" 
                    text-anchor="{{ $anchor }}" 
                    style="font-size: {{ $compact ? '10px' : '12.5px' }}; font-weight: 600;"
                    class="fill-black select-none"
                >
                    {{ $label }}
                </text>
            @endif
        @endforeach
    </svg>

    <!-- Bottom Legend (Clean & Compact) -->
    @if ($legend && count($lineSeries) > 0)
        <div class="flex items-center justify-center flex-wrap gap-4 mt-2 pt-2 border-t border-slate-100 {{ $compact ? 'text-[10px]' : 'text-[12px]' }} font-semibold text-slate-800">
            @foreach ($lineSeries as $ls)
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $ls['color'] }}"></span>
                    <span class="truncate">{{ $ls['name'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
