@if($modalLoan && $lostModalMode)
    @php
        $isReport = $lostModalMode === 'report';
        $fine = $isReport && !$modalLoan->isInsideLibrary() && $modalLoan->due_date
            ? app(\App\Services\FineService::class)->overdueAmount($modalLoan->due_date, $modalLoan->book?->bookDetail) : 0;
    @endphp
    <div class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="lost-modal-title" x-data x-trap.inert.noscroll="true" x-init="$nextTick(() => $refs.note.focus())" x-on:keydown.escape.window="$wire.closeLostModal()">
        <div class="my-auto w-full max-w-lg rounded-2xl bg-white p-5 shadow-xl sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="lost-modal-title" class="text-xl font-bold text-[#102B70]">{{ $isReport ? 'Report book lost' : ($lostModalMode === 'found' ? 'Book found' : 'Resolve lost-book case') }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $isReport ? 'Confirm the loan details before recording the report.' : 'Record the outcome and inspect the copy if it was found.' }}</p>
                </div>
                <button type="button" wire:click="closeLostModal" aria-label="Close dialog" class="rounded-lg px-2 py-1 text-xl text-slate-600 hover:bg-slate-100">&times;</button>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4 text-sm">
                <div class="col-span-2"><dt class="text-slate-500">Book</dt><dd class="font-semibold text-slate-900">{{ $modalLoan->book?->bookDetail?->bookData?->book_title ?? 'Book unavailable' }} · {{ $modalLoan->book?->accession_number }}</dd></div>
                <div class="col-span-2"><dt class="text-slate-500">Student</dt><dd class="font-semibold text-slate-900">{{ $scannedMember['name'] ?? '' }} · {{ $scannedMember['school_id'] ?? '' }}</dd></div>
                <div><dt class="text-slate-500">Borrowed</dt><dd class="font-semibold">{{ $modalLoan->issued_date?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Due</dt><dd class="font-semibold">{{ $modalLoan->due_date?->format('M d, Y') ?? '—' }}</dd></div>
                @if($isReport)<div class="col-span-2"><dt class="text-slate-500">Overdue charge through today</dt><dd class="font-semibold">₱{{ number_format($fine, 2) }}</dd></div>@endif
            </dl>
            @if($isReport)<p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">The copy will be marked lost and borrowing will be blocked until the case is resolved.</p>@endif
            @if($isReport && $modalReservationCount)<p class="mt-3 rounded-lg bg-blue-50 p-3 text-sm text-blue-900">{{ $modalReservationCount }} active {{ \Illuminate\Support\Str::plural('reservation', $modalReservationCount) }} on this copy will be reassigned or moved to the waitlist according to request order. Affected students will be notified.</p>@endif
            <form wire:submit="{{ $isReport ? 'reportLost' : 'resolveLost' }}({{ $modalLoan->id }})" class="mt-4 space-y-4">
                <div>
                    <label for="lost-modal-note" class="block text-sm font-semibold text-slate-700">{{ $isReport ? 'Report note' : 'Resolution or recovery note' }} <span aria-hidden="true">*</span></label>
                    <textarea id="lost-modal-note" x-ref="note" required maxlength="500" rows="3" wire:model="{{ $isReport ? 'lostNotes' : 'resolutionNotes' }}.{{ $modalLoan->id }}" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm focus:border-[#102B70] focus:ring-[#102B70]"></textarea>
                </div>
                @unless($isReport)
                    <div>
                        <label for="lost-modal-condition" class="block text-sm font-semibold text-slate-700">{{ $lostModalMode === 'found' ? 'Recovered book condition' : 'If found, select its condition' }}</label>
                        <select id="lost-modal-condition" wire:model="recoveredConditions.{{ $modalLoan->id }}" @if($lostModalMode === 'found') required @endif class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm">
                            <option value="">{{ $lostModalMode === 'found' ? 'Select condition' : 'Not found' }}</option>
                            @foreach($recoveryConditions as $condition)<option value="{{ $condition->id }}">{{ $condition->status }}</option>@endforeach
                        </select>
                    </div>
                @endunless
                @if($lostModalError)<p role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $lostModalError }}</p>@endif
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" wire:click="closeLostModal" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-[#102B70] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{{ $isReport ? 'Confirm lost report' : ($lostModalMode === 'found' ? 'Confirm recovery' : 'Save resolution') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif
