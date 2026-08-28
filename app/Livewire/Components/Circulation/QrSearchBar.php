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
        return config('pgpc', []);
    }

    #[On('code-scanned')]
    public function handleCodeScanned($code)
    {
        $code = trim($code);
        if (empty($code)) return;

        // Security Hardening: Sanitize input values from scan streams
        $code = strip_tags($code);
        $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $code = substr($code, 0, 50);

        $config = $this->getQrConfig();
        
        $isMember = false;
        $isBarcode = false;
        
        if (!empty($config['accepted_formats'])) {
            $memberConf = $config['accepted_formats']['member'];
            $bookConf = $config['accepted_formats']['book'];
            
            // Match member patterns
            if (!empty($memberConf['patterns'])) {
                foreach ($memberConf['patterns'] as $pattern) {
                    if (preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $code)) {
                        $isMember = true;
                        break;
                    }
                }
            }
            // Match member keywords
            if (!$isMember && !empty($memberConf['keywords'])) {
                foreach ($memberConf['keywords'] as $keyword) {
                    if (str_contains(strtolower($code), strtolower($keyword))) {
                        $isMember = true;
                        break;
                    }
                }
            }
            
            // Match book patterns
            if (!empty($bookConf['patterns'])) {
                foreach ($bookConf['patterns'] as $pattern) {
                    if (preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $code)) {
                        $isBarcode = true;
                        break;
                    }
                }
            }
        } else {
            // Fallback default patterns if config load fails
            $isMember = preg_match('/^(?:LIB-|SA-|20\d{2}-)/i', $code) || str_contains(strtolower($code), 'member') || str_contains(strtolower($code), 'student');
            $isBarcode = preg_match('/^\d+$/', $code) || preg_match('/^(?:BK-|ISBN-)/i', $code);
        }

        if ($isMember || $isBarcode) {
            $this->value = $code;
            // Notify the parent CheckInBook component of the scan
            $this->dispatch('search-code', code: $code);
        }
    }

    public function submit($manualCode = null)
    {
        if ($manualCode !== null) {
            $this->value = $manualCode;
        }

        $code = trim($this->value);
        if (empty($code)) return;

        $code = strip_tags($code);
        $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $code = substr($code, 0, 50);

        $this->value = $code;
        $this->dispatch('search-code', code: $code);
    }

    public function render()
    {
        return view('livewire.components.circulation.qr-search-bar');
    }
}
