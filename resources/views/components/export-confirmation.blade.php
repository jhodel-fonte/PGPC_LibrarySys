<div x-data>
    <button type="button" @click="$refs.exportDialog.showModal()" class="min-h-10 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-[#102B70] hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70]">Export Excel</button>
    <dialog x-ref="exportDialog" aria-labelledby="export-confirm-title" aria-describedby="export-confirm-description" class="w-[calc(100%-2rem)] max-w-md rounded-xl p-0 shadow-xl backdrop:bg-slate-900/50">
        <div class="space-y-4 p-6">
            <h2 id="export-confirm-title" class="text-lg font-bold text-[#102B70]">Confirm Excel export</h2>
            <p id="export-confirm-description" class="text-sm text-slate-700">{{ $description }} The current filters will be applied. This creates a spreadsheet copy, not a full database backup.</p>
            <p class="text-sm text-slate-600">The export runs in the background. Check notifications for its progress and download link.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$refs.exportDialog.close()" class="min-h-10 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700">Cancel</button>
                <button type="button" @click="$refs.exportDialog.close(); $wire.exportExcel()" class="min-h-10 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white hover:bg-[#0B225E]">Confirm export</button>
            </div>
        </div>
    </dialog>
</div>
