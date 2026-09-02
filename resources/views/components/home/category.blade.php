@props([
    'opacUrl' => Route::has('opac.index') ? route('opac.index') : url('/#opac'),
    'eJournalsUrl' => url('/#online-resources'),
    'reservationsUrl' => url('/#reservations'),
    'myAccountUrl' => url('/#account'),
])

<section id="services" class="relative w-full py-14 sm:py-16 lg:py-20 select-none">
    <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">

        <!-- Centered Section Header -->
        <div class=" max-w-2xl mb-10 sm:mb-12">

            <!-- Main Heading -->
            <h2 class="text-3xl sm:text-4xl lg:text-[38px] xl:text-[42px] font-extrabold tracking-tight text-[#0F172A] leading-tight">
                Library Services
            </h2>

            <!-- Subtitle -->
            <p class="mt-2 text-[14.5px] sm:text-[15.5px] text-slate-500 leading-relaxed">
                Quickly access the tools and resources available through the PGPC Library.
            </p>
        </div>

        <!-- 4 Cards Grid (Equal height, destination cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">

            <!-- Card 1: OPAC Search -->
            <x-home.category-card
                title="OPAC Search"
                description="Find books, theses & resources."
                action="Explore catalog"
                :url="$opacUrl">
            </x-home.category-card>

            <!-- Card 2: E-Journals -->
            <x-home.category-card
                title="E-Journals"
                description="Browse academic journals & articles."
                action="Explore journals"
                :url="$eJournalsUrl">
            </x-home.category-card>

            <!-- Card 3: Reservations -->
            <x-home.category-card
                title="Reservations"
                description="Reserve available resources."
                action="Reserve items"
                :url="$reservationsUrl">
            </x-home.category-card>

            <!-- Card 4: My Account -->
            <x-home.category-card
                title="My Account"
                description="Check your loans and history."
                action="View account"
                :url="$myAccountUrl">

            </x-home.category-card>

        </div>

    </div>
</section>
