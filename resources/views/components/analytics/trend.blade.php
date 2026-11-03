@props(['title', 'description', 'rows', 'money' => false])
<section {{ $attributes->class(['min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-6']) }}>
    <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
    {{ $slot }}
    @if ($rows->isEmpty())
        <p class="py-8 text-sm text-slate-600">No recorded data for this view.</p>
    @else
        @if ($rows->sum('total') == 0)<p class="mt-4 text-sm text-slate-600">No recorded activity in this period.</p>@endif
        <div class="mt-4"><div>
            <x-widget.graph.main :series="[['name' => $title, 'data' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all()]]" :labels="$rows->pluck('label')->all()" :colors="['#2450a4']" :height="190" :svg-width="560" :suffix="$money ? ' PHP' : ''" line grid markers />
        </div></div>
        <details class="mt-3 text-sm"><summary class="cursor-pointer font-semibold text-[#102B70]">View chart data</summary>
            <table class="mt-2 w-full text-left"><thead><tr><th scope="col">Period</th><th scope="col" class="text-right">{{ $money ? 'Amount' : 'Count' }}</th></tr></thead><tbody>
                @foreach ($rows as $row)<tr class="border-b border-slate-100"><th scope="row" class="py-1 font-normal">{{ $row->label }}</th><td class="text-right tabular-nums">{{ ($money ? '₱' : '').number_format($row->total, $money ? 2 : 0) }}</td></tr>@endforeach
            </tbody></table>
        </details>
    @endif
</section>
