@props([
    'series' => [],
    'labels' => [],
    'colors' => null,
    'palette' => null,
    'pie' => false,
    'donut' => false,
    'legend' => false,
    'legendPosition' => 'bottom',
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

<x-charts.pie 
    :series="$series" 
    :labels="$labels" 
    :colors="$colors" 
    :palette="$palette" 
    :pie="$pie"
    :donut="$donut" 
    :legend="$legend" 
    :legendPosition="$legendPosition" 
    :height="$height" 
    :centerLabel="$centerLabel" 
    :centerValueSize="$centerValueSize" 
    :centerSubSize="$centerSubSize" 
    :legendTextSize="$legendTextSize" 
    :legendValueSize="$legendValueSize" 
    :sliceLabelSize="$sliceLabelSize" 
    :showSliceLabels="$showSliceLabels" 
    :showTooltip="$showTooltip" 
    :animated="$animated" 
    :suffix="$suffix" 
    {{ $attributes }}
/>
