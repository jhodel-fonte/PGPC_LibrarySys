<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;

class LiveCamera extends Component
{
    public $state = 'ready'; // 'ready', 'scanning', 'success_member', 'success_book', 'error'
    public $message = 'Scan a barcode or QR code';
    public $detail = '';

    public function setScanState($state, $message = '', $detail = '')
    {
        $this->state = $state;
        $this->message = $message;
        $this->detail = $detail;
    }

    public function getQrConfig()
    {
        return config('settings', []);
    }

    public function handleScan($code)
    {
        $code = trim((string) $code);
        if (empty($code)) {
            return;
        }

        $code = strip_tags($code);
        $this->state = 'scanning';
        $this->detail = $code;
        $this->message = 'Processing scan...';

        $this->dispatch('code-scanned', code: $code);
    }

    public function render()
    {
        return view('livewire.components.circulation.live-camera');
    }
}
