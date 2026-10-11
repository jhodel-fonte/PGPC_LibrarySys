<div class="min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1360px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.user-management') }}"
                    wire:navigate
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-xs transition hover:border-[#102B70]/30 hover:bg-[#EFF6FF] hover:text-[#102B70] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]"
                    title="Back to User Management"
                    aria-label="Back to User Management"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                        <a href="{{ route('admin.user-management') }}" wire:navigate class="hover:text-[#102B70] transition-colors">User Management</a>
                        <span class="text-slate-400">/</span>
                        <span class="text-[#102B70] font-bold">Add Student</span>
                    </div>
                    <h1 class="truncate text-xl font-bold tracking-tight text-[#102B70] sm:text-2xl">Student Registration</h1>
                </div>
            </div>

            <!-- Quick Info / Status Indicator -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                    <span class="w-2 h-2 rounded-full bg-[#102B70] animate-pulse"></span>
                    <span>New Member Form</span>
                </span>
            </div>
        </div>

        <!-- Add Student Form Component -->
        <livewire:forms.add-user-form />

    </div>
</div>
