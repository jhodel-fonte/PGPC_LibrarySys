<div class="student-page min-w-0 w-full min-h-full bg-[#F8FAFC] pb-16 font-sans text-base text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">
        @php
            $signedInUser = auth()->user();
            $isLibrarian = in_array($signedInUser?->role?->name, ['Librarian', 'Head Librarian', 'Admin']) || (bool) $signedInUser?->librarian;
        @endphp

        @if ($isLibrarian)
            <livewire:components.usermanager.librarian-detail :userId="$signedInUser?->id" :isProfile="true" :key="'profile-librarian-'.($signedInUser?->id ?? 'guest')" />
        @else
            <livewire:pages.dashboard.student-detail :userId="$signedInUser?->id" :isProfile="true" :key="'profile-student-'.($signedInUser?->id ?? 'guest')" />
        @endif
    </div>
</div>
