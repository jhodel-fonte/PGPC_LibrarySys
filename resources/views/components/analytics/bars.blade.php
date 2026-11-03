@props(['title', 'description', 'rows', 'money' => false, 'chart' => 'bar'])
<section {{ $attributes->class(['min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-6']) }}>
    <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $description }}</p>
    @php
        $max = max(1, (float) $rows->max('total'));
        $colors = ['#102B70', '#0284c7', '#ca8a04', '#64748b', '#0f766e', '#9a3412'];
    @endphp
    @if ($rows->isEmpty())
        <p class="py-8 text-sm text-slate-600">No recorded data for this view.</p>
    @else
        @if ($rows->sum('total') == 0)
            <p class="mt-4 text-sm text-slate-600">No activity in these categories for this view.</p>
        @endif
        @if ($chart === 'column')
            <div class="mt-5" aria-hidden="true">
                <x-widget.graph.main :series="[['name' => $title, 'data' => $rows->pluck('total')->map(fn ($value) => (float) $value)->all()]]" :labels="$rows->pluck('label')->map(fn ($label) => \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($label, ' - '), 14))->all()" :colors="['#2450a4']" :height="190" :svg-width="560" bar grid />
            </div>
        @elseif ($chart === 'donut' && $rows->sum('total') > 0)
            <div class="mt-5" aria-hidden="true">
                <x-widget.graph.main :series="$rows->pluck('total')->map(fn ($value) => (float) $value)->all()" :labels="$rows->pluck('label')->all()" :colors="$colors" donut :height="190" />
            </div>
        @else
            <div class="mt-5 flex justify-between border-b border-slate-200 pb-1 text-xs tabular-nums text-slate-500" aria-hidden="true"><span>0</span><span>{{ ($money ? '₱' : '').number_format($max, $money ? 2 : 0) }}</span></div>
        @endif
        <ul class="mt-4 space-y-4" aria-label="{{ $title }} data">
            @foreach ($rows as $row)
                <li>
                    <div class="flex justify-between gap-3 text-sm"><span class="min-w-0 break-words text-slate-700">@if ($chart === 'donut' && $rows->sum('total') > 0)<span class="mr-2 inline-block h-3 w-3 rounded-sm" style="background-color: {{ $colors[$loop->index % count($colors)] }}" aria-hidden="true"></span>@endif{{ $row->label }}</span><strong class="shrink-0 tabular-nums text-[#102B70]">{{ ($money ? '₱' : '').number_format($row->total, $money ? 2 : 0) }}</strong></div>
                    @if ($chart === 'bar' || ($chart === 'donut' && $rows->sum('total') <= 0))
                        <div class="mt-2 h-5 overflow-hidden rounded bg-slate-100" aria-hidden="true"><div class="h-full rounded bg-[#2450a4]" style="width: {{ max(0, min(100, ($row->total / $max) * 100)) }}%"></div></div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
