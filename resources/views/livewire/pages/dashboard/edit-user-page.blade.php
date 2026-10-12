<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">
        @if ($this->isLibrarian)
            <!-- Edit Librarian Form Component -->
            <livewire:forms.edit-librarian-form :userId="$this->user->id" :key="'edit-librarian-form-'.$this->user->id" />
        @else
            <!-- Edit Student Form Component -->
            <livewire:forms.edit-student-form :userId="$this->user->id" :key="'edit-student-form-'.$this->user->id" />
        @endif
    </div>
</div>
