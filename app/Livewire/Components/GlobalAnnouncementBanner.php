<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\GlobalAnnouncement;
use Carbon\Carbon;

class GlobalAnnouncementBanner extends Component
{
    public $announcements = [];
    public $dismissed = [];
    public function mount()
    {
        $this->loadAnnouncements();
    }

    public function loadAnnouncements()
    {
        $now = Carbon::now();
        $this->announcements = GlobalAnnouncement::where('status', 'Enabled')
            ->where(function($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->get()
            ->toArray();
    }

    public function dismiss($id)
    {
        $this->dismissed[] = $id;
        session()->put('dismissed_announcements', $this->dismissed);
    }

    public function render()
    {
        return view('livewire.components.global-announcement-banner');
    }
}
