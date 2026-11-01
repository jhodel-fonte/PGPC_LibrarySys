@if(is_array($signals))
    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" aria-labelledby="signal-evaluation-heading">
        <h2 id="signal-evaluation-heading" class="text-sm font-bold text-[#0F172A]">Recommendation signal evaluation</h2>
        <p class="mt-1 text-xs text-[#64748B]">Diagnostic results for held-out interactions. Signals can cover different interactions, so their scores are not directly comparable.</p>
        <div class="admin-mobile-table-scroll mt-4 overflow-x-auto">
            <table class="admin-mobile-table min-w-full text-left text-xs">
                <thead class="border-b border-[#E2E8F0] text-[#64748B]">
                    <tr><th scope="col" class="px-3 py-2">Signal</th><th scope="col" class="px-3 py-2 text-right">Interactions</th><th scope="col" class="px-3 py-2 text-right">RMSE</th><th scope="col" class="px-3 py-2 text-right">MAE</th><th scope="col" class="px-3 py-2 text-right">NDCG@10</th></tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-[#334155]">
                    @foreach(['hybrid' => 'Combined model', 'svd' => 'Collaborative filtering (SVD)', 'content' => 'Content matching', 'year_program' => 'Year and program'] as $key => $label)
                        @if(is_array($signals[$key] ?? null))
                            <tr>
                                <th scope="row" data-label="Signal" class="px-3 py-3 font-semibold">{{ $label }}</th>
                                <td data-label="Interactions" class="px-3 py-3 text-right tabular-nums">{{ $formatCount($signals[$key]['interactions'] ?? null) }}</td>
                                @foreach(['rmse', 'mae', 'ndcg_at_10'] as $metric)
                                    <td data-label="{{ strtoupper($metric) }}" class="px-3 py-3 text-right tabular-nums">{{ $formatMetric($signals[$key][$metric] ?? null) }}</td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(is_array($signals['coverage'] ?? null))
            <h3 class="mt-5 text-xs font-bold text-[#334155]">Data coverage</h3>
            <dl class="mt-2 grid grid-cols-2 gap-3 text-xs sm:grid-cols-3">
                @foreach(['content_scored_interactions' => 'Content-scored interactions', 'year_program_scored_interactions' => 'Year/program-scored interactions', 'popularity_scored_books' => 'Books with popularity data', 'availability_listed_books' => 'Books with availability data', 'candidate_books' => 'Candidate books', 'test_interactions' => 'Test interactions'] as $key => $label)
                    <div><dt class="text-[#64748B]">{{ $label }}</dt><dd class="mt-1 font-semibold tabular-nums text-[#102B70]">{{ $formatCount($signals['coverage'][$key] ?? null) }}</dd></div>
                @endforeach
            </dl>
        @endif
        @if(is_string($signals['method'] ?? null))
            <p class="mt-4 text-xs leading-5 text-[#64748B]">{{ $signals['method'] }}</p>
        @endif
    </section>
@endif
