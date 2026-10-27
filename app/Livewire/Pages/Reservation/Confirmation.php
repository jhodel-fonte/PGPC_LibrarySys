<?php

namespace App\Livewire\Pages\Reservation;

use App\Models\BookReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', [
    'title' => 'Reservation Confirmation',
    'subpage' => 'Reservation Slip',
    'activepageRoute' => 'student.reservations',
])]
class Confirmation extends Component
{
    public int|string|null $id = null;

    public function mount($id = null): void
    {
        $this->id = $id ?? request()->route('id');
    }

    #[Computed]
    public function reservation(): ?BookReservation
    {
        if (!$this->id) {
            return null;
        }

        return BookReservation::with([
            'book.bookDetail.bookData.authors',
            'book.bookDetail.publisher',
            'book.condition',
            'student.account',
            'reservationStatus',
        ])->find($this->id);
    }

    #[Computed]
    public function qrCodeSvg(): ?string
    {
        $res = $this->reservation;
        if (!$res) {
            return null;
        }

        $codeContent = 'PGPC-RES-' . str_pad($res->id, 5, '0', STR_PAD_LEFT) . '-' . ($res->student?->school_id_number ?? 'STUDENT');

        try {
            return \App\Services\QrCodeServices::svg($codeContent);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function render()
    {
        $reservation = $this->reservation;

        if (!$reservation) {
            return view('livewire.pages.reservation.confirmation', [
                'reservation' => null,
                'qrCodeSvg' => null,
            ]);
        }

        return view('livewire.pages.reservation.confirmation', [
            'reservation' => $reservation,
            'qrCodeSvg' => $this->qrCodeSvg,
        ]);
    }
}
