<?php

namespace App\Livewire\Components\Widgets;

use Livewire\Component;
use Livewire\Attributes\On;

class Modal extends Component
{
    public $isOpen = false;
    
    // Modal properties
    public $title = '';
    public $message = '';
    public $type = 'info'; // info, success, warning, danger, loading
    public $isPersistent = false;
    
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

    // Auto-close / completion for loading tasks
    public ?int $autoCloseMs = null;
    public ?string $completeEvent = null;
    public array $completeParams = [];

    /**
     * Listen to global open event
     */
    #[On('open-modal')]
    public function openAlert($title = null, $message = null, $type = 'info', $options = [])
    {
        // Support array payload if first argument is array
        if (is_array($title)) {
            $payload = $title;
            $title = $payload['title'] ?? '';
            $message = $payload['message'] ?? '';
            $type = $payload['type'] ?? 'info';
            $options = $payload['options'] ?? $payload;
        }

        $this->title = $title ?? '';
        $this->message = $message ?? '';
        $this->type = $type ?? 'info';
        $this->isPersistent = $options['persistent'] ?? ($this->type === 'loading');
        
        if ($this->type === 'loading') {
            $this->showConfirmButton = $options['showConfirmButton'] ?? false;
            $this->showCancelButton = $options['showCancelButton'] ?? false;
        } else {
            $this->showConfirmButton = $options['showConfirmButton'] ?? true;
            $this->showCancelButton = $options['showCancelButton'] ?? true;
        }

        $this->confirmButtonText = $options['confirmButtonText'] ?? 'Confirm';
        $this->cancelButtonText = $options['cancelButtonText'] ?? 'Cancel';
        
        $this->confirmEvent = $options['confirmEvent'] ?? null;
        $this->confirmParams = $options['confirmParams'] ?? [];
        $this->cancelEvent = $options['cancelEvent'] ?? null;
        $this->cancelParams = $options['cancelParams'] ?? [];

        $this->autoCloseMs = $options['autoCloseMs'] ?? null;
        $this->completeEvent = $options['completeEvent'] ?? null;
        $this->completeParams = $options['completeParams'] ?? [];
        
        $this->isOpen = true;
    }

    /**
     * Finish loading action callback
     */
    public function finishLoading()
    {
        $this->isOpen = false;
        
        if ($this->completeEvent) {
            $this->dispatch($this->completeEvent, ...$this->completeParams);
        }
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
        return view('livewire.components.widgets.modal');
    }
}
