<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">
        @if ($type === 'librarian')
            <!-- Add Librarian Form Component -->
            <livewire:forms.add-librarian-form :key="'add-librarian-form'" />
        @else
            <!-- Add Student Form Component -->
            <livewire:forms.add-user-form :key="'add-user-form'" />
        @endif
    </div>
</div>
