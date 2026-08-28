<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;

class TopNotificationBanner extends Component
{
    public $type = 'warning'; // success, info, warning/danger
    public $title = '';
    public $message = '';
    public $confirmAction = '';
    public $cancelAction = '';
    public $confirmLabel = 'Confirm';
    public $cancelLabel = 'Cancel';

    public function render()
    {
        return view('livewire.components.circulation.top-notification-banner');
    }
}
