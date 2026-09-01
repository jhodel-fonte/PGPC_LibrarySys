<?php

namespace App\Livewire\Components\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class CheckOutSummary extends Component
{
    #[Reactive]
    public $stats = [];

    #[Reactive]
    public $scannedMember = null;

    #[Reactive]
    public $checkoutBooks = [];

    public function render()
    {
        return view('livewire.components.dashboard.check-out-summary');
    }
}
