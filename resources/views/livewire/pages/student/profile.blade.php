<div class="student-page min-w-0 w-full min-h-full bg-[#F8FAFC] pb-16 font-sans text-base text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-5 lg:px-8 lg:py-6">
        @php
            $signedInUser = auth()->user();
            $isLibrarian = in_array($signedInUser?->role?->name, ['Librarian', 'Head Librarian', 'Admin']) || (bool) $signedInUser?->librarian;
        @endphp

        @if ($isLibrarian)
            <livewire:components.usermanager.librarian-detail :userId="$signedInUser?->id" :isProfile="true" :key="'profile-librarian-'.($signedInUser?->id ?? 'guest')" />
        @else
            @if($signedInUser?->student && ! $signedInUser->student->phone_verified_at)
                <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3">
                    <div><p class="font-semibold text-slate-900">Set up SMS password recovery</p><p class="mt-1 text-sm text-slate-600">Verify your registered mobile number to receive recovery codes.</p></div>
                    <a href="{{ route('student.contact.verify') }}" class="inline-flex min-h-11 items-center rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white hover:bg-[#0B225E]">Verify mobile number</a>
                </div>
            @endif
            <livewire:pages.dashboard.student-detail :userId="$signedInUser?->id" :isProfile="true" :key="'profile-student-'.($signedInUser?->id ?? 'guest')" />
        @endif
    </div>
</div>
