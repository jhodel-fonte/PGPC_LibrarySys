<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;

use Livewire\Attributes\Layout;
#[Layout('layouts.admin', ['title' => 'Circulation Desk'])]

class CheckInBook extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard.check-in-book');
    }
}
