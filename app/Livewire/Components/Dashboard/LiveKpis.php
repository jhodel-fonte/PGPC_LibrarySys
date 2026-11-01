<?php

namespace App\Livewire\Components\Dashboard;

use App\Livewire\Pages\Dashboard\Dashboard;
use Livewire\Component;

class LiveKpis extends Component
{
    public function render()
    {
        return view('livewire.components.dashboard.live-kpis', ['metrics' => app(Dashboard::class)->getKpiMetrics()]);
    }
}
