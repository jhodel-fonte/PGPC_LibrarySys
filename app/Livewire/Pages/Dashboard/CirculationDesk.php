<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk'])]
class CirculationDesk extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard.circulation-desk');
    }
}
