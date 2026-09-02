<x-layouts.home>
    <x-home.opac-hero
        :searchValue="$search ?? request('search', '')"
        :selectedType="$selectedType ?? request('type', 'all')"
    />

    <x-home.opac-main
        :search="$search ?? request('search', '')"
        :selectedType="$selectedType ?? request('type', 'all')"
        :selectedAvailabilities="$selectedAvailabilities ?? (array) request('availability', [])"
        :selectedSubjects="$selectedSubjects ?? (array) request('subject', [])"
        :yearFrom="$yearFrom ?? request('year_from')"
        :yearTo="$yearTo ?? request('year_to')"
        :sortBy="$sortBy ?? request('sort', 'relevance')"
        :results="$results ?? []"
        :pagination="$pagination ?? null"
        :totalResults="$totalResults ?? 0"
        :availabilities="$availabilities ?? []"
        :resourceTypes="$resourceTypes ?? []"
        :subjects="$subjects ?? []"
        :isLoggedIn="$isLoggedIn ?? auth()->check()"
        :currentUser="$currentUser ?? auth()->user()"
    />
</x-layouts.home>
