<div wire:init="loadRecommendations" x-on:ml-recommendation-response="console.log('[ML API response]', $event.detail.response)">
    @if (! $loaded)
        <div role="status" class="rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">Loading recommendations…</div>
    @elseif ($books && $catalogList)
        <section aria-label="Recommended books" class="space-y-4">
            <div>
                <h2 class="text-lg font-extrabold text-[#0B2454]">Recommended for you</h2>
                <p class="mt-1 text-sm text-slate-600">Books selected based on your library activity.</p>
            </div>
            @foreach ($books as $book)
                <x-home.book-query-card :book="$book" :is-logged-in="true" />
            @endforeach
        </section>
    @elseif ($books)
        <x-recommendation.book-view-recommendation title="Picked for you" subtitle="Recommendations based on your library activity" :books="$books" :limit="10" />
    @endif
</div>
