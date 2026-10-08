<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class SecurityCard extends Component
{
    public $user;

    public function mount($user = null)
    {
        $this->user = $user;
    }

    public function sendPasswordReset()
    {
        $this->dispatch('send-password-reset');
    }

    public function toggleEmailVerification()
    {
        $this->dispatch('toggle-email-verification');
    }

    public function render()
    {
        return view('livewire.components.usermanager.security-card');
    }
}
