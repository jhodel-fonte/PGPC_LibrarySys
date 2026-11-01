@php
    $formatMetric = fn ($value) => $value === null ? '—' : number_format((float) $value, 4);
    $formatCount = fn ($value) => $value === null ? '—' : number_format((int) $value);
    $ranking = $latestRun?->payload['recommendation_evaluation'] ?? null;
@endphp

<div class="min-h-full bg-[#F8FAFC]">
    <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-4 border-b border-[#DDE5EF] pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#64748B]">Recommendation system</p>
                <h1 class="mt-1 text-2xl font-bold text-[#0F172A] sm:text-3xl">Evaluation results</h1>
                <p class="mt-2 max-w-2xl text-sm text-[#475569]">Compare how each recommendation strategy ranks books and review the data behind the latest evaluation.</p>
            </div>
            <button type="button" wire:click="refreshEvaluation" wire:loading.attr="disabled" wire:target="refreshEvaluation" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#102B70] px-5 text-sm font-semibold text-white hover:bg-[#0B225E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70] disabled:opacity-60">
                <span wire:loading.remove wire:target="refreshEvaluation">Refresh results</span><span wire:loading wire:target="refreshEvaluation">Refreshing…</span>
            </button>
        </header>

        <div role="status" class="rounded-lg border px-4 py-3 text-sm {{ $evaluation['available'] ? 'border-[#BFDBFE] bg-[#EFF6FF] text-[#1E3A8A]' : 'border-[#FECACA] bg-[#FEF2F2] text-[#991B1B]' }}">
            @if($evaluation['available'])
                Service connected · checked {{ $checkedAt->format('M j, Y g:i A') }}
            @else
                {{ $evaluation['error'] }} @if($latestRun) Showing the most recent saved evaluation. @else No saved evaluation is available yet. @endif
            @endif
        </div>

        @if($latestRun)
            <section aria-labelledby="dataset-heading" class="overflow-hidden rounded-lg border border-[#DDE5EF] bg-white">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-[#E2E8F0] px-5 py-4">
                    <div><h2 id="dataset-heading" class="font-bold text-[#0F172A]">Latest evaluation</h2><p class="mt-1 text-xs text-[#64748B]">Evaluated {{ $latestRun->evaluated_at->format('M j, Y g:i A') }}</p></div>
                    <span class="text-xs font-semibold text-[#64748B]">Training and held-out interactions</span>
                </div>
                <dl class="grid grid-cols-2 divide-x divide-y divide-[#E2E8F0] sm:grid-cols-4 sm:divide-y-0">
                    @foreach(['Training interactions' => 'training_interactions', 'Training users' => 'training_users', 'Test interactions' => 'test_interactions', 'Test users' => 'test_users'] as $label => $key)
                        <div class="px-5 py-4"><dt class="text-xs text-[#64748B]">{{ $label }}</dt><dd class="mt-1 text-xl font-bold tabular-nums text-[#102B70]">{{ $formatCount($evaluationDetails[$key] ?? null) }}</dd></div>
                    @endforeach
                </dl>
            </section>
        @endif

        @include('livewire.pages.dashboard.partials.ml-recommendation-evaluation', ['evaluation' => $ranking])
        @if(!is_array($ranking) || !is_array($ranking['strategies'] ?? null))
            <section class="rounded-lg border border-dashed border-[#CBD5E1] bg-white px-5 py-10 text-center" role="status">
                <h2 class="font-semibold text-[#0F172A]">No strategy comparison yet</h2>
                <p class="mt-2 text-sm text-[#64748B]">Run the updated recommender evaluation, then refresh this page.</p>
            </section>
        @endif

        @include('livewire.pages.dashboard.partials.ml-signal-evaluation', ['signals' => $latestRun?->payload['signal_evaluation'] ?? null])

        <section class="overflow-hidden rounded-lg border border-[#DDE5EF] bg-white" aria-labelledby="history-heading">
            <div class="border-b border-[#E2E8F0] px-5 py-4"><h2 id="history-heading" class="font-bold text-[#0F172A]">Evaluation history</h2><p class="mt-1 text-xs text-[#64748B]">Previous saved runs, newest first.</p></div>
            @if($history->isEmpty())
                <p class="px-5 py-8 text-sm text-[#64748B]">No evaluation runs have been saved yet.</p>
            @else
                <div class="overflow-x-auto"><table class="min-w-full text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs text-[#475569]"><tr><th scope="col" class="px-5 py-3">Evaluated</th><th scope="col" class="px-4 py-3 text-right">Training / test</th>@foreach($metricColumns as $label => $column)<th scope="col" class="whitespace-nowrap px-4 py-3 text-right">{{ $label }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-[#E2E8F0]">@foreach($history as $run)
                        @php($runDetails = $run->evaluationDetails())
                        <tr wire:key="ml-run-{{ $run->id }}"><th scope="row" class="whitespace-nowrap px-5 py-3 font-semibold text-[#0F172A]">{{ $run->evaluated_at->format('M j, Y g:i A') }}</th><td class="whitespace-nowrap px-4 py-3 text-right text-[#64748B]">{{ $formatCount($runDetails['training_interactions']) }} / {{ $formatCount($runDetails['test_interactions']) }}</td>@foreach($metricColumns as $label => $column)<td class="px-4 py-3 text-right tabular-nums text-[#334155]">{{ $formatMetric($run->{$column}) }}</td>@endforeach</tr>
                    @endforeach</tbody>
                </table></div>
                @if($history->hasPages())<div class="border-t border-[#E2E8F0] px-5 py-3">{{ $history->links('components.widget.cursor-pagination') }}</div>@endif
            @endif
        </section>
    </div>
</div>
