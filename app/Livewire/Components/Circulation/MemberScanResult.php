<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class MemberScanResult extends Component
{
    #[Reactive]
    public $member = null;

    public function render()
    {
        return view('livewire.components.circulation.member-scan-result');
    }
}
