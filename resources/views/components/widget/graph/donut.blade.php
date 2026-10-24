@props([
    'series' => [],
    'labels' => [],
    'palette' => [
        '#0284c7', '#10b981', '#f59e0b', '#f97316', '#8b5cf6', '#94a3b8'
    ],
    'centerLabel' => null,
    'legend' => false,
    'suffix' => '',
])

<x-widget.graph.pie 
    :series="$series" 
    :labels="$labels" 
    :palette="$palette" 
    :centerLabel="$centerLabel" 
    :donut="true" 
    :legend="$legend" 
    :suffix="$suffix" 
    {{ $attributes }}
/>
