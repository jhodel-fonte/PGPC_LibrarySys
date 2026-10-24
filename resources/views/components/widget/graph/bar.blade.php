@props([
    'series' => [],
    'labels' => [],
    'palette' => [
        '#0284c7', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#94a3b8'
    ],
    'grid' => false,
    'legend' => false,
    'height' => 190,
    'svgWidth' => 520,
    'suffix' => '',
])

@php
    $renderHeight = (int) $height;
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

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

    $padLeft = $grid ? 32 : 12;
    $padRight = 16;
    $padTop = 14;
    $padBottom = 26;

    $plotW = $svgWidth - $padLeft - $padRight;
    $plotH = $renderHeight - $padTop - $padBottom;

    $numCats = max(1, count($rawLabels));
    $slotW = $plotW / $numCats;
    $numSeries = max(1, count($barSeries));
    $barW = min(12, max(3, ($slotW * 0.72) / $numSeries));
    $barGap = 2;
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
                    class="transition-all duration-200 hover:opacity-75 cursor-pointer"
                    @mouseenter="showTooltip($event, '{{ addslashes($label) }}', '{{ addslashes($bs['name']) }}', '{{ $val }} {{ $suffix }}', '{{ $bs['color'] }}')"
                    @mousemove="moveTooltip($event)"
                    @mouseleave="hideTooltip()"
                >
                    <title>{{ $bs['name'] }}: {{ $val }}</title>
                </rect>
            @endforeach

            <!-- X-Axis Label -->
            <text 
                x="{{ $padLeft + ($catIdx * $slotW) + ($slotW / 2) }}" 
                y="{{ $renderHeight - 8 }}" 
                text-anchor="middle" 
                class="{{ $numCats > 8 ? 'text-[10px]' : 'text-[11px]' }} font-semibold fill-slate-500"
            >
                {{ $label }}
            </text>
        @endforeach
    </svg>

    <!-- Bottom Legend -->
    @if ($legend && count($barSeries) > 0)
        <div class="flex items-center justify-center flex-wrap gap-4 sm:gap-5 mt-3 pt-2 border-t border-slate-50">
            @foreach ($barSeries as $bs)
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 shrink-0">
                    <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background-color: {{ $bs['color'] }}"></span>
                    <span class="truncate">{{ $bs['name'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
