<?php

namespace App\Livewire\Components\Dashboard;

use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use Livewire\Component;

class LiveAttention extends Component
{
    public function render()
    {
        return view('livewire.components.dashboard.live-attention', [
            'overdueCount' => BorrowingTransaction::whereNull('return_date')->where('due_date', '<', now())->count(),
            'pendingCount' => BookReservation::where('reservation_status_id', 1)->count(),
        ]);
    }
}
