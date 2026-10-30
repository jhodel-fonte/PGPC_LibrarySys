@props(['svg', 'filename' => 'library-qr', 'label' => 'Library QR code'])

<div class="w-full min-w-0 max-w-xs mx-auto space-y-3" x-data="{
    saving: false, error: '',
    async save() {
        this.saving = true; this.error = '';
        try { await window.saveQrImage(this.$refs.qr.innerHTML, {{ Js::from($filename) }}); }
        catch (error) { this.error = 'Could not save the QR image. Please try again.'; }
        finally { this.saving = false; }
    }
}">
    <div x-ref="qr" role="img" aria-label="{{ $label }}" class="aspect-square w-full rounded-xl border border-slate-200 bg-white p-4 sm:p-5 [&>svg]:block [&>svg]:h-full [&>svg]:w-full">
        {!! $svg !!}
    </div>
    <button type="button" @click="save()" :disabled="saving" class="min-h-11 w-full rounded-xl bg-[#102B70] px-4 py-3 text-sm font-semibold text-white hover:bg-[#0B225E] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70] disabled:opacity-50 print:hidden">
        <span x-text="saving ? 'Preparing image…' : 'Save QR as image'">Save QR as image</span>
    </button>
    <p x-show="error" x-text="error" x-cloak role="alert" class="text-sm text-rose-700 print:hidden"></p>
</div>
