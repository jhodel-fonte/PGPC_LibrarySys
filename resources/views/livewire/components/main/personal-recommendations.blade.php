<div wire:init="loadRecommendations" x-on:ml-recommendation-response="console.log('[ML API response]', $event.detail.response)">
    @if ($loaded && count($books))
        <section class="mt-8 rounded-2xl border border-[#DDE5EF] bg-white p-5 sm:p-7 shadow-sm" aria-labelledby="personal-recommendations-heading">
            <div class="mb-5">
                <h2 id="personal-recommendations-heading" class="text-xl font-bold text-[#0B2454]">Picked for you</h2>
                <p class="mt-1 text-sm text-slate-600">Books recommended based on this title and your library activity.</p>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($books as $recommendedBook)
                    <a href="{{ route('opac.book.detail', $recommendedBook['id']) }}" wire:key="recommendation-{{ $recommendedBook['id'] }}"
                        class="group min-w-0 rounded-xl border border-slate-200 p-3 transition hover:border-[#102B70] hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">
                        <div class="flex h-40 items-center justify-center overflow-hidden rounded-lg bg-slate-50 sm:h-44">
                            <img src="{{ $recommendedBook['cover_url'] ?: asset('images/' . config('settings.book_covers.default', 'book-cover.webp')) }}"
                                alt="Cover of {{ $recommendedBook['title'] }}" loading="lazy" class="h-full max-w-full object-contain"
                                onerror="this.onerror=null; this.src='{{ asset('images/book-cover.webp') }}'">
                        </div>
                        <h3 class="mt-3 line-clamp-2 text-sm font-bold leading-snug text-[#0B2454] group-hover:text-[#102B70]">{{ $recommendedBook['title'] }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $recommendedBook['author'] }}</p>
                        <p class="mt-2 text-xs font-semibold {{ $recommendedBook['status_color'] }}">{{ $recommendedBook['status_label'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
