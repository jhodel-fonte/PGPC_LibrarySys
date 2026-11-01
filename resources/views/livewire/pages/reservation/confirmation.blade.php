<div class="student-page min-w-0 w-full min-h-full bg-[#F8FAFC] pb-16 font-sans text-slate-800 selection:bg-[#102B70] selection:text-white">
    <div class="mx-auto w-full max-w-[1100px] space-y-5 px-3 py-4 sm:px-6 lg:px-8 lg:py-6">

        @if(!$reservation)
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center space-y-3 ">
                <h2 class="text-xl font-bold text-[#102B70]">Reservation Not Found</h2>
                <p class="text-sm text-slate-500">The requested reservation voucher could not be located or has expired.</p>
                <a href="{{ route('student.reservations') }}" wire:navigate class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-[#102B70] text-white text-sm font-bold">
                    Browse Available Books
                </a>
            </div>
        @else
            @php $statusId = (int) $reservation->reservation_status_id; @endphp
            <!-- Success Banner -->
            <div class="rounded-xl bg-white border border-[#E2E8F0] p-4 sm:p-5 flex items-center gap-4 print:hidden  animate-fade-in">
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-[#102B70]">{{ match ($statusId) { 1 => 'Reservation Pending Approval', 2 => 'Ready for Pickup', 3 => 'Reservation Fulfilled', 4 => 'Reservation Cancelled', 5 => 'Reservation Expired', default => 'Book Reservation' } }}</h1>
                    <p class="text-base text-slate-600 mt-0.5">
                        {{ match ($statusId) { 1 => 'Your request is awaiting library approval.', 2 => 'Present this slip or your School ID at the circulation desk.', 3 => 'This reservation has been fulfilled.', 4 => 'This reservation has been cancelled.', 5 => 'This reservation has expired.', default => 'See the reservation details below.' } }}
                    </p>
                </div>
            </div>

            @include('livewire.pages.reservation.receipt', ['receiptModal' => false])

        @endif

    </div>
</div>

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #reservation-slip, #reservation-slip * {
        visibility: visible;
    }
    #reservation-slip {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 1px solid #CBD5E1 !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endpush
