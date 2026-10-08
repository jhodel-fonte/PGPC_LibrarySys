<?php

namespace App\Livewire\Components\Usermanager;

use Livewire\Component;

class ActivityCard extends Component
{
    public $user;
    public ?string $statusLower = null;
    public string $activeTab = 'overview';

    public function mount($user = null, ?string $statusLower = null, string $activeTab = 'overview')
    {
        $this->user = $user;
        $this->statusLower = $statusLower ?? strtolower($user?->status?->status_name ?? 'active');
        $this->activeTab = $activeTab;
    }

    public function setTab(string $tab)
    {
        $this->dispatch('set-tab', tab: $tab);
    }

    public function render()
    {
        return view('livewire.components.usermanager.activity-card');
    }
}
