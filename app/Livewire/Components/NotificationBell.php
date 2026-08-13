<?php

namespace App\Livewire\Components;

use App\Models\BorrowingTransaction;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount()
    {
        $this->checkNotifications();
    }

    public function checkNotifications()
    {
        $this->unreadCount = BorrowingTransaction::whereNull('return_date')
            ->where('due_date', '<', now())
            ->count();
    }

    public function render()
    {
        return view('livewire.components.notification-bell');
    }
}
