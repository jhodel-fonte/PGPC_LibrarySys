<div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
    <div>
        <h2 class="text-base font-bold text-[#102B70]">Catalog item setup</h2>
        <p class="mt-1 text-xs leading-relaxed text-slate-500">Books use the public bibliographic catalog. Other item types use a private physical-asset record.</p>
    </div>
    <label class="block text-sm font-semibold text-slate-700">Item type
        <select wire:model.live="catalogKind" class="mt-1 w-full rounded-lg border-slate-300">
            <option value="book">Book — public catalog</option><option value="equipment">Equipment</option><option value="multimedia">Multimedia</option><option value="periodical">Periodical</option><option value="specimen">Specimen / object</option>
        </select>
    </label>

    <label class="block text-sm font-semibold text-slate-700">Overdue fine per calendar day (₱)
        <input type="number" min="0" step="0.01" wire:model="dailyFine" placeholder="Use library default" class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    @error('dailyFine') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>
