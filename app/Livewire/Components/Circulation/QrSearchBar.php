<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use Livewire\Attributes\On;

class QrSearchBar extends Component
{
    public $label = 'Member ID / Code';
    public $placeholder = 'Enter or scan member ID or code';
    public $value = '';

    public function mount($label = null, $placeholder = null, $value = null)
    {
        if ($label !== null) $this->label = $label;
        if ($placeholder !== null) $this->placeholder = $placeholder;
        if ($value !== null) {
            $this->placeholder = $value;
            $this->value = '';
        }
    }

    public function getQrConfig()
    {
        return config('settings', []);
    }

    #[On('code-scanned')]
    public function handleCodeScanned($code)
    {
        $code = trim((string) $code);
        if (empty($code)) return;

        $code = strip_tags($code);
        $this->value = $code;
        $this->dispatch('search-code', code: $code);
    }

    public function submit($manualCode = null)
    {
        $input = $manualCode !== null ? $manualCode : $this->value;
        $code = trim((string) $input);
        if (empty($code)) return;

        $code = strip_tags($code);
        $this->value = $code;
        $this->dispatch('search-code', code: $code);
    }

    public function render()
    {
        return view('livewire.components.circulation.qr-search-bar');
    }
}
