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
    #[\Livewire\Attributes\Locked]
    public string $reference = '';

    public function mount(): void
    {
        $reference = request()->query('reservation');
        $this->reference = is_string($reference) ? $reference : '';
    }

    #[Computed]
    public function reservation(): ?BookReservation
    {
        if (! preg_match('/^[0-9]{1,20}$/D', $this->reference) || ! auth()->user()?->student) {
            return null;
        }

        $studentId = auth()->user()->student->id;
        return BookReservation::with([
            'book' => fn ($q) => $q->withTrashed(),
            'book.bookDetail' => fn ($q) => $q->withTrashed(),
            'book.bookDetail.bookData' => fn ($q) => $q->withTrashed(),
            'book.bookDetail.bookData.authors',
            'book.bookDetail.bookData.categories',
            'student',
            'reservationStatus',
        ])->where(function ($query) use ($studentId) {
            $query->where(function ($query) use ($studentId) {
                $query->where('student_id', $studentId)->where(function ($query) {
                    $query->whereNull('account_id')->orWhere('account_id', auth()->id());
                });
            })->orWhere(function ($query) {
                $query->whereNull('student_id')->where('account_id', auth()->id());
            });
        })->find(ltrim($this->reference, '0') ?: '0');
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
            report($e);
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
