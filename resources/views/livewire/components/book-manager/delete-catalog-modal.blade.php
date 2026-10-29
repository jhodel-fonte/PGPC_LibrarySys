<div>
    @if($open)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true" aria-labelledby="catalog-delete-title">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h2 id="catalog-delete-title" class="text-lg font-bold text-[#102B70]">Delete {{ $type === 'title' ? 'title' : 'copy' }}?</h2>
                <p class="mt-3 text-sm text-slate-700">{{ $label }}{{ count($ids) > 1 ? ' and '.(count($ids) - 1).' other selected items' : '' }} will be moved to Deleted.</p>
                @if($type === 'title')
                    <p class="mt-2 text-sm text-slate-700">This title and its {{ $impact['copies'] }} active copies will be deleted. Copies deleted earlier remain deleted.</p>
                @else
                    <p class="mt-2 text-sm text-slate-700">Only the selected physical {{ count($ids) === 1 ? 'copy' : 'copies' }} will be deleted. The title stays available.</p>
                @endif
                @if($impact['requires_confirmation'])
                    <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-950">
                        <p class="font-semibold">This selection has linked activity or unavailable copies.</p>
                        <p class="mt-1">Active loans: {{ $impact['loans'] }}. Active reservations: {{ $impact['reservations'] }}. Copies not available: {{ $impact['other'] }}.</p>
                        <p class="mt-1">Loan records: {{ $impact['loan_records'] }}. Reservation records: {{ $impact['reservation_records'] }}. Waiting requests: {{ $impact['waitlist'] }}.</p>
                        <p class="mt-1">Loan and fine records will be preserved. Borrowed copies can still be returned.</p>
                        @if($impact['reservations'] || $impact['waitlist'])
                            <p class="mt-1">Active reservations and waiting requests will remain in the database, but deleted copies cannot be collected or allocated.</p>
                        @endif
                    </div>
                    <label for="catalog-delete-confirmation" class="mt-4 block text-sm font-semibold text-slate-700">Type DELETE to confirm</label>
                    <input id="catalog-delete-confirmation" type="text" wire:model="confirmation" autocomplete="off" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-blue-100">
                @endif
                @error('confirmation') <p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="$set('open', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="button" wire:click="confirm" wire:loading.attr="disabled" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-50">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
