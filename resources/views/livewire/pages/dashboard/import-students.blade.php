<div class="mx-auto w-full max-w-[1600px] p-3 sm:p-4 lg:p-6 space-y-5" @if($import?->status === 'importing' || $import?->rows()->where('email_status', 'pending')->exists()) wire:poll.5s @endif>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-[#102B70]">Import Students</h1><p class="mt-1 text-sm text-slate-600">Upload an Excel or CSV file, review the columns, and import student accounts.</p></div>
        <a href="{{ route(\App\Services\EmployeeRoleService::getRoutePrefix().'user-management') }}" wire:navigate class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-[#102B70]">Back to students</a>
    </div>
    @if($message)<div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ $message }}</div>@endif
    <section class="rounded-xl border border-slate-200 bg-white p-5 space-y-4"
        x-data="{ progress: 0, uploading: false, uploadError: false }"
        x-on:livewire-upload-start="uploading = true; uploadError = false; progress = 0"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false; uploadError = true"
        x-on:livewire-upload-error="uploading = false; uploadError = true">
        <h2 class="font-bold text-slate-800">1. Upload file</h2>
        <p class="text-sm text-slate-600">Include Student ID, First Name, and Last Name headings; Email is optional. Format IDs as text to preserve leading zeros. Age cannot be used as a date of birth.</p>
        <input type="file" wire:model="spreadsheet" accept=".csv,.xlsx,.xls" aria-label="Student spreadsheet" class="block w-full text-sm">
        @error('spreadsheet')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
        <div x-show="uploading" x-cloak><label for="student-upload-progress" class="text-sm text-slate-600">Uploading: <span x-text="progress"></span>%</label><progress id="student-upload-progress" max="100" :value="progress" class="block w-full"></progress></div>
        <p x-show="uploadError" x-cloak role="alert" class="text-sm text-red-700">Upload failed or was cancelled. Select the file and try again.</p>
        <button type="button" wire:click="inspect" wire:loading.attr="disabled" class="rounded-lg bg-[#102B70] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Detect columns</button>
    </section>
    @if($import)
        @if(in_array($import->status, ['reviewing', 'reviewed']))
            <section class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="font-bold text-slate-800">2. Check column mapping</h2>
                <p class="text-sm text-slate-600">Student ID, first name, and last name are required. Students without an email must add and verify one when signing in. Date of birth, if provided, must use MM-YYYY.</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($import->headers as $index => $header)
                        <label class="min-w-0 text-sm font-medium text-slate-700"><span class="block break-words">{{ $header ?: 'Column '.($index + 1) }}</span>
                            <select wire:model="mapping.{{ $index }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Ignore column</option>@foreach($fields as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
                        </label>
                    @endforeach
                </div>
                <button type="button" wire:click="review" wire:loading.attr="disabled" class="rounded-lg bg-[#102B70] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Validate rows</button>
                <span wire:loading wire:target="review" class="text-sm text-slate-600">Reading and validating rows…</span>
            </section>
        @endif
        @if($import->status !== 'reviewing')
            <section class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="font-bold text-slate-800">3. Review and import</h2>
                <p class="text-sm text-slate-700">Status: <strong>{{ ucfirst($import->status) }}</strong> · Ready: {{ $counts['pending'] ?? 0 }} · Imported: {{ $counts['imported'] ?? 0 }} · Invalid: {{ $counts['invalid'] ?? 0 }} · Failed: {{ $counts['failed'] ?? 0 }}</p>
                @if($emailCounts->isNotEmpty())<p class="text-sm text-slate-600">Invitations — pending: {{ $emailCounts['pending'] ?? 0 }} · sent: {{ $emailCounts['sent'] ?? 0 }} · failed: {{ $emailCounts['failed'] ?? 0 }} · email required: {{ $emailCounts['email_required'] ?? 0 }} · email verified: {{ $emailCounts['verified'] ?? 0 }}</p>@endif
                @php($rowTotal = $counts->sum())
                @if($rowTotal > 0 && $import->status !== 'reviewed')<label for="student-import-progress" class="block text-sm text-slate-600">Rows processed: {{ $rowTotal - ($counts['pending'] ?? 0) }} / {{ $rowTotal }}</label><progress id="student-import-progress" class="w-full" max="{{ $rowTotal }}" value="{{ $rowTotal - ($counts['pending'] ?? 0) }}"></progress>@endif
                @if($import->error)<p role="alert" class="text-sm text-red-700">{{ $import->error }}</p>@endif
                <div class="admin-mobile-table-scroll overflow-x-auto"><table class="admin-mobile-table w-full min-w-[550px] text-left text-sm"><thead><tr class="border-b text-slate-600"><th class="p-2">Row</th><th class="p-2">Student ID</th><th class="p-2">Name</th><th class="p-2">Email</th><th class="p-2">Result</th></tr></thead><tbody>
                    @foreach($rows as $row)<tr class="border-b align-top"><td data-label="Row" class="p-2">{{ $row->row_number }}</td><td data-label="Student ID" class="p-2">{{ $row->data['school_id_number'] ?? '—' }}</td><td data-label="Name" class="p-2"><span>{{ $row->data['first_name'] ?? '' }} {{ $row->data['last_name'] ?? '' }}</span></td><td data-label="Email" class="p-2">{{ $row->data['email'] ?? '—' }}</td><td data-label="Result" class="p-2"><div>{{ ucfirst($row->status) }} @if($row->email_status) · Email: {{ $row->email_status }} @endif @if($row->error)<p class="text-red-700">{{ $row->error }}</p>@endif</div></td></tr>@endforeach
                </tbody></table></div><p class="text-xs text-slate-500">Showing the first 25 rows. Download errors to see all invalid or failed rows.</p>
                <div class="flex flex-wrap gap-2">
                    @if($import->status === 'reviewed' && ($counts['pending'] ?? 0))<button type="button" wire:click="start" class="rounded-lg bg-[#102B70] px-4 py-2 text-sm font-semibold text-white">Import valid students</button>@endif
                    @if(in_array($import->status, ['reviewed', 'importing']))<button type="button" wire:click="cancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel import</button>@endif
                    @if(($counts['invalid'] ?? 0) + ($counts['failed'] ?? 0) > 0)<button type="button" wire:click="downloadErrors" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Download errors</button>@endif
                </div>
                @foreach($failedEmails as $row)<div class="flex flex-wrap items-center gap-3 text-sm"><span>Invitation failed: {{ $row->data['email'] ?? '' }}</span><button type="button" wire:click="retryInvitation({{ $row->id }})" class="text-[#102B70] underline">Retry email</button></div>@endforeach
            </section>
        @endif
    @endif
</div>
