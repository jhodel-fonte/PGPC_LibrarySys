@props([
    'title' => 'Explore Online Resources',
    'subtitle' => 'Access academic journals and research resources available through the PGPC Library.',
    'viewAllUrl' => url('/#online-resources'),
])

@php
    $resources = [];

    try {
        $jsonPath = storage_path('app/public/online_resources/list.json');

        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);

            if ($content !== false) {
                $decoded = json_decode($content, true);
                $resources = $decoded['online_resources'] ?? [];
            }
        }
    } catch (\Throwable $e) {
        $resources = [];
    }
@endphp

@if (!empty($resources))
    <section id="online-resources" class="relative w-full bg-[#091b45] py-14 sm:py-16 lg:py-20 select-none">
        <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-8 sm:mb-10 lg:mb-12">
                <div>

                    <!-- Main Heading -->
                    <h2 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold tracking-tight text-white">
                        {{ $title }}
                    </h2>

                    <!-- Subtitle -->
                    <p class="mt-2 text-[14.5px] sm:text-[15.5px] text-blue-100/75 max-w-xl leading-relaxed">
                        {{ $subtitle }}
                    </p>
                </div>

                <!-- View All Action Button -->
                <a
                    href="{{ $viewAllUrl }}"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-2.5 text-[13.5px] font-semibold text-white backdrop-blur-md shadow-xs transition-all duration-200 hover:border-white hover:bg-white hover:text-[#091b45] hover:shadow-md self-start sm:self-auto"
                >
                    <span>View all e-journals</span>
                    <svg class="h-3.5 w-3.5 text-white/70 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-[#091b45]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Logo Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5 sm:gap-6">
                @foreach ($resources as $resource)
                    @php
                        $logoFile = $resource['logo'] ?? '';
                        $logoUrl = $logoFile ? asset('storage/online_resources/files/' . $logoFile) : null;
                    @endphp
                    <x-home.online-resources-card
                        :name="$resource['name'] ?? ''"
                        :url="$resource['external_url'] ?? '#'"
                        :logo="$logoUrl"
                    />
                @endforeach
            </div>

        </div>
    </section>
@endif
