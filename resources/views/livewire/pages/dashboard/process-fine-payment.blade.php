<section class="min-h-full bg-slate-50 px-4 py-4 text-slate-800 sm:px-6" x-data="{ scannerOpen: false, scanAttempted: false, openScanner() { this.scanAttempted = false; this.scannerOpen = true; this.$nextTick(() => { this.$dispatch('start-camera'); this.$refs.closeScanner.focus(); }); }, closeScanner() { this.scannerOpen = false; this.$dispatch('stop-camera'); } }" x-on:student-qr-selected.window="closeScanner()" @code-scanned.window="scanAttempted = true">
<div class="mx-auto w-full max-w-screen-2xl">
    <h1 class="text-3xl font-bold text-[#102B70]">Process Fine Payment</h1>
    <p class="mt-2 text-sm text-slate-600">Record a cashier receipt for an overdue fine after the item has been returned.</p>
    @if($success)<p role="status" class="mt-5 rounded-lg bg-emerald-50 p-4 text-emerald-800">{{ $success }}</p>@endif
    @error('payment')<p role="alert" class="mt-5 rounded-lg bg-red-50 p-4 text-red-800">{{ $message }}</p>@enderror
    <form wire:submit="review" class="mt-7 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid lg:grid-cols-2">
            <div class="min-w-0">
        <section class="px-5 py-6 sm:px-8">
            <h3 class="text-base font-bold text-[#102B70]">Student information</h3>
            <div class="mt-4">
            <label for="student-search" class="block text-sm font-semibold">Student ID or name</label>
            <div class="mt-1 flex items-stretch gap-2">
                <input id="student-search" type="search" wire:model.live.debounce.300ms="search" x-on:keydown.enter.prevent placeholder="Enter at least 2 characters" class="min-w-0 flex-1 rounded-lg border-slate-300 focus:ring-[#102B70]">
                <button type="button" @click="openScanner()" aria-haspopup="dialog" aria-controls="student-qr-scanner" class="min-h-11 shrink-0 rounded-lg bg-[#102B70] px-3 text-sm font-bold text-white hover:bg-[#0B225E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70] sm:px-4">Scan QR code</button>
            </div>
            @if(strlen(trim($search)) >= 2)
                <ul class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200">
                @forelse($students as $result)
                    <li><button type="button" wire:click="selectStudent({{ $result->id }})" class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50"><strong>{{ $result->first_name }} {{ $result->last_name }}</strong> &middot; {{ $result->school_id_number }}</button></li>
                @empty <li class="p-4 text-sm text-slate-600">No students found.</li> @endforelse
                </ul>
            @endif
            @if($student)<p class="mt-4 text-sm"><strong>{{ $student->first_name }} {{ $student->last_name }}</strong> &middot; {{ $student->school_id_number }} &middot; {{ $fines->count() }} unpaid returned-item {{ Str::plural('fine', $fines->count()) }}</p>@endif
            </div>
        </section>
        @if($student)
        <section class="border-t border-slate-200 px-5 py-6 sm:px-8">
            <h3 class="text-base font-bold text-[#102B70]">Unreturned books</h3>
            <p class="mt-1 text-sm text-slate-600">Process the return before paying a fine for these books.</p>
            @forelse($activeLoans as $loan)
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">
                    <strong class="block">{{ $loan->book?->bookDetail?->bookData?->book_title ?? 'Book unavailable' }}</strong>
                    <span class="mt-1 block">Accession: {{ $loan->book?->accession_number ?? '—' }} &middot; Due {{ $loan->due_date?->format('M d, Y') ?? '—' }}</span>
                    <span class="mt-1 block font-semibold text-amber-800">Not yet returned{{ $loan->due_date && $loan->due_date->isBefore(now()->startOfDay()) ? ' · Overdue' : '' }}</span>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-600">This student has no unreturned books.</p>
            @endforelse
        </section>
        @endif
        <section class="border-t border-slate-200 px-5 py-6 sm:px-8">
            <h3 class="text-base font-bold text-[#102B70]">Select a fine</h3>
            @if(!$student)<p class="mt-3 text-sm text-slate-600">Find a student to see unpaid fines for returned items.</p>
            @elseif($fines->isEmpty())<p class="mt-3 text-sm text-slate-600">This student has no unpaid overdue fines for returned items.</p>
            @else <div class="mt-4 space-y-2">
                @foreach($fines as $item)
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 has-[:checked]:border-[#102B70] has-[:checked]:bg-blue-50">
                    <input type="radio" wire:model.live="fineId" value="{{ $item->id }}" class="mt-1 text-[#102B70]">
                    <span class="min-w-0 flex-1 text-sm"><strong class="block">{{ $item->transaction?->book?->bookDetail?->bookData?->book_title ?? 'Book unavailable' }}</strong><span class="mt-1 block text-slate-600">Due {{ $item->transaction->due_date?->format('M d, Y') ?? '—' }} &middot; Returned {{ $item->transaction->return_date?->format('M d, Y') }}</span></span>
                    <span class="font-bold tabular-nums">&#8369;{{ number_format($item->amount, 2) }}</span>
                </label>
                @endforeach
            </div>@endif
            @error('fineId')<p role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
        </section>
        <section class="border-t border-slate-200 px-5 py-6 sm:px-8">
            <h3 class="text-base font-bold text-[#102B70]">Payment details</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><label for="receipt" class="block text-sm font-semibold">OR / receipt number</label><input id="receipt" type="text" wire:model.live.debounce.300ms="receiptNumber" maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-[#102B70]">@error('receiptNumber')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                <div><label for="payment-date" class="block text-sm font-semibold">Date paid at cashier</label><input id="payment-date" type="date" wire:model.live="paymentDate" max="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg border-slate-300 focus:ring-[#102B70]">@error('paymentDate')<p role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
            </div>
        </section>
            </div>
            <aside class="border-t border-slate-200 bg-slate-50 px-5 py-6 sm:px-8 lg:border-l lg:border-t-0" aria-labelledby="summary-heading" aria-live="polite">
                <h3 id="summary-heading" class="text-base font-bold text-[#102B70]">Payment summary</h3>
                <p class="mt-1 text-sm text-slate-600">Review the selected fine and cashier receipt before continuing.</p>
                <div class="mt-5 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Fine and payment details</caption>
                        <tbody class="divide-y divide-slate-100">
                            <tr><th scope="row" class="w-2/5 px-4 py-3 font-semibold text-slate-600">Student</th><td class="break-words px-4 py-3 text-slate-900">{{ $student ? $student->first_name . ' ' . $student->last_name . ' · ' . $student->school_id_number : '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Book</th><td class="break-words px-4 py-3 text-slate-900">{{ $fine?->transaction?->book?->bookDetail?->bookData?->book_title ?? '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Due date</th><td class="px-4 py-3">{{ $fine?->transaction?->due_date?->format('M d, Y') ?? '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Return date</th><td class="px-4 py-3">{{ $fine?->transaction?->return_date?->format('M d, Y') ?? '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">OR / receipt number</th><td class="break-all px-4 py-3">{{ $receiptNumber ?: '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Date paid</th><td class="px-4 py-3">{{ $calculation ? \Carbon\Carbon::parse($paymentDate)->format('M d, Y') : '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Overdue days</th><td class="px-4 py-3 tabular-nums">{{ $calculation ? $calculation['days'] : '—' }}</td></tr>
                            <tr><th scope="row" class="px-4 py-3 font-semibold text-slate-600">Daily rate</th><td class="px-4 py-3 tabular-nums">{{ $calculation ? '₱' . number_format($calculation['rate'], 2) : '—' }}</td></tr>
                            <tr class="bg-[#F1F5F9]"><th scope="row" class="px-4 py-4 font-bold text-[#102B70]">Amount to record</th><td class="px-4 py-4 text-lg font-bold tabular-nums text-[#102B70]">{{ $calculation ? '₱' . number_format($calculation['amount'], 2) : '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-slate-600">Days after the cashier payment date or return date are excluded.</p>
                <div class="mt-6 flex justify-end"><button type="submit" wire:loading.attr="disabled" class="min-h-11 rounded-lg bg-[#102B70] px-5 text-sm font-bold text-white hover:bg-[#0B225E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]">Review Payment</button></div>
            </aside>
        </div>
    </form>
</div>
<div id="student-qr-scanner" x-show="scannerOpen" x-cloak x-trap.inert.noscroll="scannerOpen" @keydown.escape.window="if (scannerOpen) closeScanner()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4" role="dialog" aria-modal="true" aria-labelledby="scanner-title" aria-describedby="scanner-instruction">
    <div class="my-auto w-full max-w-md rounded-xl bg-white p-5 shadow-xl sm:p-6">
        <h2 id="scanner-title" class="text-xl font-bold text-[#102B70]">Scan student QR code</h2>
        <p id="scanner-instruction" class="mt-1 text-sm text-slate-600">Position the student's QR code inside the frame.</p>
        <div class="mt-5 [&_.workstation-camera-view]:aspect-square [&_.workstation-camera-view]:h-auto">
            <livewire:components.circulation.live-camera :auto-start="false" />
        </div>
        @error('scan')<p x-show="scanAttempted" role="alert" class="mt-3 text-sm text-red-700">{{ $message }}</p>@enderror
        <div class="mt-5 flex justify-end"><button x-ref="closeScanner" type="button" @click="closeScanner()" class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-[#102B70] hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]">Close</button></div>
    </div>
</div>
@if($reviewing && $fine && $calculation)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4" role="dialog" aria-modal="true" aria-labelledby="review-title" x-data x-init="$nextTick(() => $refs.back.focus())" x-on:keydown.escape.window="$wire.set('reviewing', false)">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
        <h2 id="review-title" class="text-xl font-bold text-[#102B70]">Review payment</h2><p class="mt-1 text-sm text-slate-600">Check the receipt details before confirming.</p>
        <dl class="mt-5 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt>Student</dt><dd class="text-right font-semibold">{{ $student->first_name }} {{ $student->last_name }} &middot; {{ $student->school_id_number }}</dd></div><div class="flex justify-between gap-4"><dt>Book</dt><dd class="text-right">{{ $fine->transaction?->book?->bookDetail?->bookData?->book_title ?? 'Book unavailable' }}</dd></div><div class="flex justify-between gap-4"><dt>Due date</dt><dd>{{ $fine->transaction->due_date->format('M d, Y') }}</dd></div><div class="flex justify-between gap-4"><dt>Receipt number</dt><dd>{{ $receiptNumber }}</dd></div><div class="flex justify-between gap-4"><dt>Paid at cashier</dt><dd>{{ \Carbon\Carbon::parse($paymentDate)->format('M d, Y') }}</dd></div><div class="flex justify-between gap-4"><dt>Overdue calculation</dt><dd>{{ $calculation['days'] }} days &times; &#8369;{{ number_format($calculation['rate'], 2) }}</dd></div><div class="flex justify-between gap-4"><dt>Library processing date</dt><dd>{{ now()->format('M d, Y') }}</dd></div><div class="flex justify-between gap-4 border-t border-slate-200 pt-3 text-base font-bold"><dt>Amount paid</dt><dd>&#8369;{{ number_format($calculation['amount'], 2) }}</dd></div></dl>
        @error('payment')<p role="alert" class="mt-3 text-sm text-red-700">{{ $message }}</p>@enderror
        <div class="mt-6 flex flex-wrap justify-end gap-3"><button x-ref="back" type="button" wire:click="$set('reviewing', false)" class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold">Back to Edit</button><button type="button" wire:click="confirm" wire:loading.attr="disabled" class="min-h-11 rounded-lg bg-[#102B70] px-4 text-sm font-bold text-white">Confirm Payment</button></div>
    </div>
</div>
@endif
</section>
