<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class StatusCard extends Component
{
    public $user;
    public ?string $statusName = null;
    public ?string $statusLower = null;
    public ?string $roleName = null;

    public function mount(
        $user = null,
        ?string $statusName = null,
        ?string $statusLower = null,
        ?string $roleName = null
    ) {
        $this->user = $user;
        $this->statusName = $statusName ?? ($user?->status?->status_name ?? 'Active');
        $this->statusLower = $statusLower ?? strtolower($this->statusName);
        $this->roleName = $roleName ?? ($user?->role?->name ?? 'User');
    }

    public function openStatusModal()
    {
        $this->dispatch('open-status-modal');
    }

    public function render()
    {
        return view('livewire.components.usermanager.status-card');
    }
}
