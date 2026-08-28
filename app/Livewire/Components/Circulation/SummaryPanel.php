<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class SummaryPanel extends Component
{
    #[Reactive]
    public $stats = [
        'total' => 0,
        'returned' => 0,
        'remaining' => 0,
        'overdue' => 0,
        'return_date' => ''
    ];

    #[Reactive]
    public $scannedMember = null;

    public function render()
    {
        return view('livewire.components.circulation.summary-panel');
    }
}
