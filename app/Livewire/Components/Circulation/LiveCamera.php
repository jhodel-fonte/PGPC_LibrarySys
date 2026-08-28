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
        return config('pgpc', []);
    }

    public function handleScan($code)
    {
        $code = trim($code);
        if (empty($code)) {
            return;
        }

        $config = $this->getQrConfig();
        $isMember = false;
        $isBook = false;

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
                        $isBook = true;
                        break;
                    }
                }
            }
        } else {
            // Fallback default patterns if config load fails
            $isMember = preg_match('/^(?:LIB-|SA-|20\d{2}-)/i', $code) || str_contains(strtolower($code), 'member') || str_contains(strtolower($code), 'student');
            $isBook = preg_match('/^\d+$/', $code) || preg_match('/^(?:BK-|ISBN-)/i', $code);
        }

        if ($isMember) {
            $this->state = 'success_member';
            $this->detail = $code;
            $this->message = 'Member loaded successfully.';
        } elseif ($isBook) {
            $this->state = 'success_book';
            $this->detail = $code;
            $this->message = 'Book added to return list.';
        } else {
            $this->state = 'error';
            $this->detail = $code;
            $this->message = 'Invalid format scanned.';
        }

        // Only dispatch scan event if a valid member or book barcode format was matched
        if ($isMember || $isBook) {
            $this->dispatch('code-scanned', code: $code);
        }
    }

    public function render()
    {
        return view('livewire.components.circulation.live-camera');
    }
}
