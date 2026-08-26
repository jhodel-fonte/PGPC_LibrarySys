<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class ScanSummary extends Component
{
    #[Reactive]
    public $member = null;
    
    #[Reactive]
    public $stats = [
        'total' => 0,
        'returned' => 0,
        'remaining' => 0,
        'overdue' => 0,
        'return_date' => ''
    ];

    public function render()
    {
        return view('livewire.components.circulation.scan-summary');
    }
}
