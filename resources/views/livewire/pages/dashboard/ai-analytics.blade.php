@php
    $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
    $formatMetric = fn ($value) => $value === null ? '—' : number_format((float) $value, 4);
@endphp

<div class="min-h-full bg-[#F8FAFC]">
    <div class="mx-auto w-full max-w-[1400px] space-y-6 p-5 sm:p-6 lg:p-8">

        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-[#E2E8F0] pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <a
                    href="{{ route($routePrefix . 'analytics') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-semibold text-[#64748B] hover:text-[#102B70]"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Analytics
                </a>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#102B70]">Machine learning evaluation</h1>
                <p class="mt-1 text-sm text-[#64748B]">
                    Quality of the recommendation model used by the OPAC. Each new result is saved to the history below.
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-3">
                <span class="inline-flex items-center gap-2 text-xs font-medium text-[#64748B]">
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-[#3B82F6]' => $evaluation['available'],
                        'bg-[#EF4444]' => ! $evaluation['available'],
                    ])></span>
                    {{ $evaluation['available'] ? 'Live' : 'Unavailable' }} · checked {{ $checkedAt->format('M j, g:i A') }}
                </span>
                <button
                    type="button"
                    wire:click="refreshEvaluation"
                    wire:loading.attr="disabled"
                    wire:target="refreshEvaluation"
                    class="inline-flex h-10 items-center gap-2 rounded-2xl bg-[#102B70] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#0B225E] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#DBEAFE] disabled:opacity-60"
                >
                    <svg wire:loading.class="animate-spin" wire:target="refreshEvaluation" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Service status --}}
        @unless($evaluation['available'])
            <div class="flex items-start gap-3 rounded-2xl border border-[#FECACA] bg-[#FEF2F2] px-4 py-3 text-sm text-[#B91C1C]" role="alert">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>
                    <p class="font-semibold">{{ $evaluation['error'] }}</p>
                    <p class="mt-0.5">
                        @if($latestRun)
                            Showing the last saved result from {{ $latestRun->evaluated_at->format('M j, Y g:i A') }}.
                        @else
                            No saved results yet. Results appear here once the service responds.
                        @endif
                    </p>
                </div>
            </div>
        @endunless

        {{-- Evaluation trend --}}
        @if($evaluationTrend['labels'] && $evaluationTrend['series'])
            <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="text-sm font-bold text-[#0F172A]">Evaluation trend</h2>
                    <p class="mt-1 text-xs text-[#64748B]">How the saved model metrics changed over time.</p>
                </div>
                <x-widget.graph.main
                    :series="$evaluationTrend['series']"
                    :labels="$evaluationTrend['labels']"
                    :colors="['#102B70', '#3B82F6', '#D39B00', '#64748B', '#0F766E', '#B45309']"
                    line
                    compact
                    grid
                    legend
                    height="280"
                />
            </section>
        @endif

        {{-- Current metrics --}}
        <section wire:loading.class="opacity-60" wire:target="refreshEvaluation" class="transition-opacity">
            @if($metrics->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#CBD5E1] bg-white px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-[#0F172A]">No evaluation data</p>
                    <p class="mt-1 text-sm text-[#64748B]">Start the recommendation service, then select Refresh.</p>
                </div>
            @else
                <div class="mb-3 flex items-baseline justify-between gap-3">
                    <h2 class="text-sm font-bold text-[#0F172A]">Current results</h2>
                    @if($comparisonRun)
                        <p class="text-xs text-[#64748B]">Change compared with {{ $comparisonRun->evaluated_at->format('M j, Y g:i A') }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($metrics as $metric)
                        <article class="flex flex-col rounded-2xl border border-[#E2E8F0] bg-white p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[#334155]">{{ $metric['label'] }}</h3>
                                    @if($metric['better'])
                                        <p class="mt-0.5 text-[11px] text-[#94A3B8]">{{ $metric['better'] === 'lower' ? 'Lower is better' : 'Higher is better' }}</p>
                                    @endif
                                </div>

                                @if($metric['delta'] !== null)
                                    <span @class([
                                        'inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums',
                                        'bg-[#EFF6FF] text-[#102B70]' => $metric['trend'] === 'better',
                                        'bg-[#FEF2F2] text-[#B91C1C]' => $metric['trend'] === 'worse',
                                        'bg-[#F8FAFC] text-[#64748B]' => $metric['trend'] === 'neutral',
                                    ])>
                                        @if($metric['delta'] > 0) ▲ @elseif($metric['delta'] < 0) ▼ @endif
                                        {{ $metric['delta'] == 0 ? 'No change' : number_format(abs($metric['delta']), 4) }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-3 text-3xl font-bold tabular-nums text-[#102B70]">{{ $formatMetric($metric['value']) }}</p>
                            <p class="mt-1 text-xs leading-5 text-[#64748B]">{{ $metric['description'] }}</p>

                            <div class="mt-auto pt-4">
                                @if($metric['sparkline'])
                                    <svg viewBox="0 0 120 32" preserveAspectRatio="none" class="h-8 w-full" role="img" aria-label="{{ $metric['label'] }} trend across saved runs">
                                        <polyline
                                            points="{{ $metric['sparkline'] }}"
                                            fill="none"
                                            stroke="#102B70"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            vector-effect="non-scaling-stroke"
                                        />
                                    </svg>
                                @else
                                    <p class="text-[11px] text-[#94A3B8]">Trend appears after two saved runs.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- History --}}
        <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
            <div class="flex flex-col gap-1 border-b border-[#E2E8F0] px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between">
                <h2 class="text-sm font-bold text-[#0F172A]">Evaluation history</h2>
                <p class="text-xs text-[#64748B]">A new entry is added only when the model's results change.</p>
            </div>

            @if($history->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-[#0F172A]">No history yet</p>
                    <p class="mt-1 text-sm text-[#64748B]">The first result is saved as soon as the recommendation service responds.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#F8FAFC] text-[11px] font-semibold uppercase tracking-wide text-[#334155]">
                            <tr>
                                <th scope="col" class="whitespace-nowrap px-5 py-3">Evaluated</th>
                                @foreach($metricColumns as $label => $column)
                                    <th scope="col" class="whitespace-nowrap px-4 py-3 text-right">{{ $label }}</th>
                                @endforeach
                                <th scope="col" class="whitespace-nowrap px-5 py-3 text-right">Last confirmed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @foreach($history as $run)
                                <tr wire:key="ml-run-{{ $run->id }}" class="hover:bg-[#F8FAFC]">
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <span class="font-semibold text-[#0F172A]">{{ $run->evaluated_at->format('M j, Y') }}</span>
                                        <span class="ml-1 text-[#64748B]">{{ $run->evaluated_at->format('g:i A') }}</span>
                                        @if($latestRun && $run->is($latestRun))
                                            <span class="ml-2 rounded-full bg-[#FCC719]/20 px-2 py-0.5 text-[10px] font-bold uppercase text-[#0F172A]">Latest</span>
                                        @endif
                                    </td>
                                    @foreach($metricColumns as $label => $column)
                                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-[#334155]">{{ $formatMetric($run->{$column}) }}</td>
                                    @endforeach
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-xs text-[#64748B]">
                                        {{ ($run->last_checked_at ?? $run->evaluated_at)->format('M j, g:i A') }}
                                        <span class="text-[#94A3B8]">· {{ $run->times_checked }} {{ \Illuminate\Support\Str::plural('check', $run->times_checked) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($history->hasPages())
                    <div class="border-t border-[#E2E8F0] px-5 py-3">
                        {{ $history->links('components.widget.pagination') }}
                    </div>
                @endif
            @endif
        </section>
    </div>

    <script>
        console.log('[ML API Evaluation]', @js($evaluation));
        window.addEventListener('ml-api-evaluation', (event) => {
            console.log('[ML API Evaluation (Refreshed)]', event.detail?.result ?? event.detail);
        });
    </script>
</div>
