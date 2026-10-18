<?php

namespace App\Livewire\Components\Notification;

use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;

    public function checkNotifications()
    {
        // Placeholder logic or query for unread notifications
    }

    public function render()
    {
        return view('livewire.components.notification.notification-bell');
    }
}
