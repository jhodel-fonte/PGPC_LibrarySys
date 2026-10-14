<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class StudentAccount extends Component
{
    public $user;
    public $student;

    public function mount($user = null, $student = null)
    {
        $this->user = $user;
        $this->student = $student ?? $user?->student;
    }

    public function openDeleteModal()
    {
        $this->dispatch('open-delete-modal');
    }

    public function openDisableModal()
    {
        $this->dispatch('open-disable-modal');
    }

    public function enableAccount()
    {
        $this->dispatch('enable-student-account');
    }

    public function render()
    {
        return view('livewire.components.usermanager.student-account');
    }
}
