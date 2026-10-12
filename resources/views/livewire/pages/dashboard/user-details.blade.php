<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">
        @php
            $user = $this->user;
            $isLibrarian = in_array($user?->role?->name, ['Librarian', 'Head Librarian']) || (bool) $user?->librarian;
        @endphp

        @if ($isLibrarian)
            <livewire:components.usermanager.librarian-detail :userId="$user->id" :key="'librarian-detail-'.$user->id" />
        @else
            <livewire:pages.dashboard.student-detail :userId="$user->id" :key="'student-detail-'.$user->id" />
        @endif
    </div>
</div>
