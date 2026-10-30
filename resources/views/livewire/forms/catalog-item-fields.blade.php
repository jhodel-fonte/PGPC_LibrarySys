<div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4">
    <div>
        <h2 class="text-base font-bold text-[#102B70]">Catalog item setup</h2>
        <p class="mt-1 text-xs leading-relaxed text-slate-500">Choose the type of catalog item you want to manage.</p>
    </div>
    @if(isset($periodicalTitles))
        <fieldset>
            <legend class="text-sm font-semibold text-slate-700">Item type</legend>
            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach(['book' => 'Book', 'equipment' => 'Physical Item', 'magazine' => 'Magazine', 'journal' => 'Journal'] as $kind => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="catalogKind" value="{{ $kind }}" class="peer sr-only">
                        <span class="flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-700 transition-colors hover:border-[#102B70] peer-checked:border-[#102B70] peer-checked:bg-[#102B70] peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 peer-focus-visible:ring-offset-2">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @else
        <label class="block text-sm font-semibold text-slate-700">Item type
            <select wire:model.live="catalogKind" class="mt-1 w-full rounded-lg border-slate-300">
                <option value="book">Book</option><option value="equipment">Physical Item</option><option value="magazine">Magazine</option><option value="journal">Journal</option>
                @if(in_array($catalogKind, ['multimedia', 'periodical', 'specimen'], true))<option value="{{ $catalogKind }}" hidden>Existing {{ ucfirst($catalogKind) }}</option>@endif
            </select>
        </label>
    @endif

    @if(in_array($catalogKind, ['magazine', 'journal'], true))
        @if(isset($periodicalTitles))
            <label class="block text-sm font-semibold">Existing title (optional)
                <select wire:model.live="periodicalTitleId" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Create a new title</option>
                    @foreach($periodicalTitles as $periodicalTitle)
                        <option value="{{ $periodicalTitle->id }}">{{ $periodicalTitle->book_title }}</option>
                    @endforeach
                </select>
            </label>
            <p class="text-xs text-slate-500">Choose an existing title to add another issue.</p>
            @error('periodicalTitleId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        @endif
        <label class="block text-sm font-semibold">ISSN (optional)
            <input wire:model="issn" maxlength="15" class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        @error('issn') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="text-sm font-semibold">Volume <input wire:model="issueVolume" maxlength="50" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-semibold">Issue number <input wire:model="issueNumber" maxlength="50" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-semibold">Issue label <input wire:model="issueLabel" maxlength="255" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-semibold">Publication date <input type="date" wire:model="issueDate" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-semibold">Loan period in days (optional) <input type="number" min="1" max="3650" wire:model="issueLoanDays" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" wire:model="issueBorrowable"> Allow borrowing and reservations</label>
        </div>
        @foreach(['issueVolume', 'issueNumber', 'issueLabel', 'issueDate', 'issueLoanDays'] as $issueField)
            @error($issueField) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        @endforeach
    @endif
    <label class="block text-sm font-semibold text-slate-700">Overdue fine per calendar day (₱)
        <input type="number" min="0" step="0.01" wire:model="dailyFine" placeholder="Use library default" class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    @error('dailyFine') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    <label class="block text-sm font-semibold text-slate-700">Online resource link (optional)
        <input type="url" wire:model="resourceUrl" maxlength="255" placeholder="https://example.org/resource" class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    <p class="text-xs text-slate-500">Add a link to the publisher or online version of this title.</p>
    @error('resourceUrl') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>
