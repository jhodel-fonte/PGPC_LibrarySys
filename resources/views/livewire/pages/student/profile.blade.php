<div class="min-h-full bg-[#eef3f7]">
    <div class="mx-auto w-full max-w-[1200px] p-4 lg:p-6 space-y-5">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#102B70] text-white flex items-center justify-center text-xl font-bold border-2 border-white shadow-sm overflow-hidden shrink-0">
                        @if($student?->image_url)
                            <img src="{{ $student->image_url }}" alt="Profile" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($student?->first_name ?? $user?->name ?? 'S', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight">
                            {{ $student ? ($student->first_name . ' ' . $student->last_name) : ($user?->name ?? 'Student Profile') }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            {{ $student?->program ?? 'Student Patron' }} • {{ $student?->year_level ?? '1st Year' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                        {{ $student?->libraryStatus?->status ?? 'Active Patron' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Student Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- Academic Information -->
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 lg:p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100">
                    Academic Details
                </h2>

                <dl class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Student ID Number</dt>
                        <dd class="font-bold text-slate-900 font-mono tracking-wider">{{ $student?->school_id_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Degree / Program</dt>
                        <dd class="font-semibold text-slate-900 text-right max-w-[240px]">{{ $student?->program ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Year Level</dt>
                        <dd class="font-semibold text-slate-900">{{ $student?->year_level ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Enrollment Status</dt>
                        <dd class="font-semibold text-slate-900">{{ $student?->enrollment_status ?? 'Enrolled' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Contact & Personal Details -->
            <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-5 lg:p-6">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-100">
                    Personal & Contact Information
                </h2>

                <dl class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Registered Email</dt>
                        <dd class="font-semibold text-slate-900">{{ $user?->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Contact Number</dt>
                        <dd class="font-semibold text-slate-900">{{ $student?->contact_num ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Gender</dt>
                        <dd class="font-semibold text-slate-900 capitalize">{{ $student?->gender ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <dt class="text-slate-500">Username</dt>
                        <dd class="font-semibold text-slate-900 font-mono">{{ $user?->username ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

        </div>

    </div>
</div>
