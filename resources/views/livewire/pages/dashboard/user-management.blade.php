<div class="admin-page-table bg-[#F8FAFC] flex flex-col min-h-0 flex-1 overflow-hidden">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-4 sm:gap-6 min-h-0 flex-1">
        @if ($type === 'librarian')
            <livewire:components.usermanager.librarian-table :key="'librarian-table'" />
        @else
            <livewire:components.usermanager.student-table :key="'student-table'" />
        @endif
    </div>
</div>
