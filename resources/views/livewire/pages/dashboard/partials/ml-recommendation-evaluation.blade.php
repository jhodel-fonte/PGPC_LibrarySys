@if(is_array($evaluation) && is_array($evaluation['strategies'] ?? null))
    <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" aria-labelledby="recommendation-evaluation-heading">
        <h2 id="recommendation-evaluation-heading" class="text-sm font-bold text-[#0F172A]">Standalone top-10 recommendation evaluation</h2>
        <p class="mt-1 text-xs text-[#64748B]">Each strategy ranks the indexed catalog independently. Higher precision, recall, NDCG, and coverage are generally better.</p>
        <div class="admin-mobile-table-scroll mt-4 overflow-x-auto">
            <table class="admin-mobile-table min-w-full text-left text-xs">
                <thead class="border-b border-[#E2E8F0] text-[#64748B]">
                    <tr>
                        <th scope="col" class="px-3 py-2">Strategy</th>
                        <th scope="col" class="px-3 py-2 text-right">Users</th>
                        <th scope="col" class="px-3 py-2 text-right">Precision@10</th>
                        <th scope="col" class="px-3 py-2 text-right">Recall@10</th>
                        <th scope="col" class="px-3 py-2 text-right">NDCG@10</th>
                        <th scope="col" class="px-3 py-2 text-right">Catalog coverage</th>
                        <th scope="col" class="px-3 py-2 text-right">Available rate</th>
                        <th scope="col" class="px-3 py-2 text-right">Novel item rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-[#334155]">
                    @foreach([
                        'svd_only' => 'SVD only',
                        'content_only' => 'Item content only',
                        'program_year_only' => 'Program/year only',
                        'popularity_only' => 'Popularity only',
                        'hybrid_score' => 'Hybrid score',
                        'hybrid_with_availability' => 'Hybrid + availability',
                        'hybrid_availability_novelty' => 'Hybrid + availability + novelty',
                    ] as $key => $label)
                        @if(is_array($evaluation['strategies'][$key] ?? null))
                            @php($strategy = $evaluation['strategies'][$key])
                            <tr>
                                <th scope="row" data-label="Strategy" class="px-3 py-3 font-semibold">{{ $label }}</th>
                                <td data-label="Users" class="px-3 py-3 text-right tabular-nums">{{ $formatCount($strategy['users_evaluated'] ?? null) }}</td>
                                @foreach(['precision_at_10', 'recall_at_10', 'ndcg_at_10', 'catalog_coverage', 'availability_rate_at_10', 'novel_item_rate_at_10'] as $metric)
                                    <td data-label="{{ str_replace('_', ' ', $metric) }}" class="px-3 py-3 text-right tabular-nums">{{ $formatMetric($strategy[$metric] ?? null) }}</td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-xs leading-5 text-[#64748B]">
            Catalog: {{ $formatCount($evaluation['candidate_catalog_books'] ?? null) }} books;
            held-out relevant items: {{ $formatCount($evaluation['held_out_relevant_items'] ?? null) }}.
            {{ $evaluation['method'] ?? '' }}
        </p>
    </section>
@endif
