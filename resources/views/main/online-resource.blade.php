<x-layouts.home :title="$resource['name'] . ' · Online Resource'">
    @php($providerUrl = $resource['external_url'])
    @php($providerHost = parse_url($providerUrl, PHP_URL_HOST))

    <main class="mx-auto max-w-2xl px-5 py-12 text-slate-800 sm:px-8 sm:py-16">
        <a href="{{ url('/#online-resources') }}" class="text-sm text-slate-600 underline underline-offset-4 hover:text-[#102B70]">← Back to online resources</a>

        <article class="mt-8 space-y-6">
            <header class="space-y-2 border-b border-slate-200 pb-6">
                <p class="text-sm font-medium text-slate-600">PGPC Library · External academic resource</p>
                <h1 class="break-words text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $resource['name'] }}</h1>
            </header>

            <section class="space-y-3 text-[15px] leading-7">
                <h2 class="text-lg font-semibold text-slate-950">About this resource</h2>
                <p>This is an external academic resource linked by the PGPC Library to support study and research. It is operated by {{ $resource['name'] }}, outside the PGPC Library System.</p>
                <p>The provider manages its own content, privacy practices, availability, and sign-in requirements. Some materials or features may require an account or may be limited by the provider's access terms.</p>
                <p class="text-sm text-slate-600">Website: <span class="break-all">{{ $providerHost }}</span></p>
            </section>

            <div class="flex flex-col items-start gap-3">
                @if (!empty($resource['logo']))
                    <img src="{{ asset('storage/online_resources/files/' . $resource['logo']) }}" alt="{{ $resource['name'] }} logo" class="max-h-16 max-w-52 object-contain object-left">
                @endif
                <a href="{{ $providerUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#102B70] px-5 py-2.5 text-sm font-semibold text-white underline-offset-4 hover:bg-[#0B225E] focus:outline-none focus:ring-2 focus:ring-[#102B70] focus:ring-offset-2">
                    Continue to {{ $resource['name'] }}
                </a>
            </div>
        </article>
    </main>
</x-layouts.home>
