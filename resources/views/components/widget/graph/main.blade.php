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
    'svgWidth' => 780,
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
        '#102B70',
        '#f59e0b', // Amber / Gold
        '#94a3b8', // Slate / Gray
    ];

    // Standardize series to collection or array
    $rawSeries = is_array($series) ? $series : (method_exists($series, 'toArray') ? $series->toArray() : (array) $series);
    $rawLabels = is_array($labels) ? $labels : (method_exists($labels, 'toArray') ? $labels->toArray() : (array) $labels);

    // Height & Width
    $renderHeight = (int) $height;
    $svgWidth = (int) ($svgWidth ?? 780);
@endphp

<div 
    x-data="{
        inView: false,
        tooltipVisible: false,
        tooltipTitle: '',
        tooltipSeries: '',
        tooltipValue: '',
        tooltipColor: '',
        tooltipItems: [],
        tooltipX: 0,
        tooltipY: 0,
        activeColumn: null,
        isSystemLoaderVisible() {
            if (window.__pgpcPreloaderDone === true) return false;

            // Check full-page preloader
            const preloader = document.querySelector('[role=status]');
            if (preloader) {
                const isHidden = preloader.classList.contains('invisible') || 
                                 preloader.classList.contains('opacity-0') ||
                                 preloader.style.display === 'none' ||
                                 preloader.style.visibility === 'hidden';
                if (!isHidden) return true;
            }

            // Check top navigation progress bar if actively loading
            const topLoader = document.querySelector('.fixed.top-0.left-0');
            if (topLoader && topLoader.classList.contains('opacity-100') && !topLoader.classList.contains('w-full') && !topLoader.classList.contains('w-0')) {
                return true;
            }

            return false;
        },
        triggerWhenReady() {
            if (!this.isSystemLoaderVisible()) {
                this.inView = true;
                return;
            }

            let fired = false;
            const execute = () => {
                if (!fired) {
                    fired = true;
                    // Short 120ms pause after preloader finishes fading out for a clean entrance
                    setTimeout(() => {
                        this.inView = true;
                    }, 120);
                }
            };

            // Listen for preloader-hidden or navigation events
            window.addEventListener('preloader-hidden', execute, { once: true });
            window.addEventListener('livewire:navigated', execute, { once: true });

            // Polling fallback to check if loader disappeared
            const checkTimer = setInterval(() => {
                if (!this.isSystemLoaderVisible()) {
                    clearInterval(checkTimer);
                    execute();
                }
            }, 80);

            // Hard ceiling fallback so the graph is never stuck hidden
            setTimeout(() => {
                clearInterval(checkTimer);
                execute();
            }, 3500);
        },
        init() {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.inView = true;
                return;
            }
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.triggerWhenReady();
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -25px 0px'
                });
                observer.observe(this.$el);
            } else {
                this.triggerWhenReady();
            }
        },
        showTooltip(e, title, series, value, color, items = []) {
            this.tooltipTitle = title;
            this.tooltipSeries = series;
            this.tooltipValue = value;
            this.tooltipColor = color;
            this.tooltipItems = Array.isArray(items) ? items : [];
            this.tooltipVisible = true;
            this.moveTooltip(e);
        },
        moveTooltip(e) {
            const rect = this.$el.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            this.tooltipX = Math.max(70, Math.min(rect.width - 70, x));
            this.tooltipY = Math.max(10, y);
        },
        hideTooltip() {
            this.tooltipVisible = false;
            this.activeColumn = null;
            this.tooltipItems = [];
        },
        checkColumnHover(e, svgEl, lineData, svgWidth, renderHeight, padLeft = 36, padRight = 20) {
            if (!lineData || !lineData.length || !svgEl) return;
            const rect = svgEl.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;

            const mouseX = (e.clientX - rect.left) * (svgWidth / rect.width);
            const mouseY = (e.clientY - rect.top) * (renderHeight / rect.height);

            // Bounds check
            if (mouseX < padLeft - 25 || mouseX > (svgWidth - padRight) + 25) {
                this.hideTooltip();
                return;
            }

            const firstSeries = lineData.find(s => s.points && s.points.length);
            if (!firstSeries) return;

            const pts = firstSeries.points;
            let closestIdx = 0;
            let minXDist = Infinity;

            for (let i = 0; i < pts.length; i++) {
                const dist = Math.abs(pts[i].x - mouseX);
                if (dist < minXDist) {
                    minXDist = dist;
                    closestIdx = i;
                }
            }

            // Collect all series data in this column
            const colTitle = pts[closestIdx].label || '';
            const items = [];
            const dots = [];
            const colX = pts[closestIdx].x;

            for (let s = 0; s < lineData.length; s++) {
                const series = lineData[s];
                if (series.points && series.points[closestIdx]) {
                    const p = series.points[closestIdx];
                    const valDisplay = p.val + (series.suffix ? ' ' + series.suffix : '');
                    items.push({
                        name: series.name,
                        value: valDisplay,
                        color: series.color
                    });
                    dots.push({
                        x: p.x,
                        y: p.y,
                        color: series.color
                    });
                }
            }

            this.activeColumn = {
                x: colX,
                dots: dots
            };

            this.showTooltip(
                e,
                colTitle,
                items[0]?.name || '',
                items[0]?.value || '',
                items[0]?.color || '#3B82F6',
                items
            );
        },
        checkLineProximity(e, svgEl, lineData, svgWidth, renderHeight) {
            this.checkColumnHover(e, svgEl, lineData, svgWidth, renderHeight);
        }
    }"
    @mousemove="if (tooltipVisible) moveTooltip($event)"
    @mouseleave="hideTooltip()"
    {{ $attributes->merge(['class' => 'relative w-full min-w-0 flex flex-col justify-between select-none transition-all duration-700 ease-out will-change-[transform,opacity]']) }}
    :class="inView ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-3 scale-[0.99]'"
>
    <!-- Floating Interactive Tooltip (Lighter theme with increased size & readability) -->
    <div 
        x-cloak
        x-show="tooltipVisible"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="pointer-events-none absolute z-40 bg-white/95 text-slate-900 rounded-xl shadow-[0_12px_30px_-4px_rgba(15,23,42,0.15),0_4px_10px_-2px_rgba(15,23,42,0.06)] px-4 py-3 border border-slate-200/90 backdrop-blur-md min-w-[175px] whitespace-nowrap will-change-transform"
        :style="tooltipY < 85 
            ? `left: ${tooltipX}px; top: ${tooltipY + 18}px; transform: translate(-50%, 0);` 
            : `left: ${tooltipX}px; top: ${tooltipY - 14}px; transform: translate(-50%, -100%);`"
    >
        <!-- Column Title / Header -->
        <div class="pb-2 border-b border-slate-100 font-bold text-xs sm:text-[13px] text-[#0F172A] flex items-center justify-between gap-3">
            <span x-text="tooltipTitle"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-[#102B70]"></span>
        </div>

        <!-- Multiple items in that column (e.g. Both Borrowed and Returned) -->
        <template x-if="tooltipItems && tooltipItems.length > 0">
            <div class="pt-2.5 space-y-2">
                <template x-for="(item, idx) in tooltipItems" :key="idx">
                    <div class="flex items-center justify-between gap-6 text-xs sm:text-[13px]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm" :style="`background-color: ${item.color}`"></span>
                            <span class="text-slate-600 font-medium truncate" x-text="item.name"></span>
                        </div>
                        <span class="font-bold text-[#0F172A] tabular-nums shrink-0" x-text="item.value"></span>
                    </div>
                </template>
            </div>
        </template>

        <!-- Fallback for single item -->
        <template x-if="!tooltipItems || tooltipItems.length === 0">
            <div class="pt-2.5 flex items-center justify-between gap-6 text-xs sm:text-[13px]">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm" :style="`background-color: ${tooltipColor || '#102B70'}`"></span>
                    <span class="text-slate-600 font-medium truncate" x-text="tooltipSeries"></span>
                </div>
                <span class="font-bold text-[#0F172A] tabular-nums shrink-0" x-text="tooltipValue"></span>
            </div>
        </template>
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
            :height="$renderHeight"
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