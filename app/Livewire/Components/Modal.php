<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

class Modal extends Component
{
    public $isOpen = false;
    
    // Modal properties
    public $title = '';
    public $message = '';
    public $type = 'info'; // info, success, warning, danger
    
    // Configurations
    public $showConfirmButton = true;
    public $showCancelButton = true;
    public $confirmButtonText = 'Confirm';
    public $cancelButtonText = 'Cancel';
    
    // Event callbacks
    public $confirmEvent = null;
    public $confirmParams = [];
    public $cancelEvent = null;
    public $cancelParams = [];

    /**
     * Listen to global open event
     */
    #[On('open-modal')]
    public function openAlert($title, $message, $type = 'info', $options = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        
        $this->showConfirmButton = $options['showConfirmButton'] ?? true;
        $this->showCancelButton = $options['showCancelButton'] ?? true;
        $this->confirmButtonText = $options['confirmButtonText'] ?? 'Confirm';
        $this->cancelButtonText = $options['cancelButtonText'] ?? 'Cancel';
        
        $this->confirmEvent = $options['confirmEvent'] ?? null;
        $this->confirmParams = $options['confirmParams'] ?? [];
        $this->cancelEvent = $options['cancelEvent'] ?? null;
        $this->cancelParams = $options['cancelParams'] ?? [];
        
        $this->isOpen = true;
    }

    /**
     * Close the modal
     */
    #[On('close-modal')]
    public function closeModal()
    {
        $this->isOpen = false;
    }

    /**
     * Handle confirm button click
     */
    public function confirm()
    {
        $this->isOpen = false;
        
        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, ...$this->confirmParams);
        }
    }

    /**
     * Handle cancel button click
     */
    public function cancel()
    {
        $this->isOpen = false;
        
        if ($this->cancelEvent) {
            $this->dispatch($this->cancelEvent, ...$this->cancelParams);
        }
    }

    public function render()
    {
        return view('livewire.components.modal');
    }
}

