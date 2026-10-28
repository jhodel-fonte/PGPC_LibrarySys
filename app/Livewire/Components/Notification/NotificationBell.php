<?php

declare(strict_types=1);

namespace App\Livewire\Components\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    #[Computed]
    public function unreadCount(): int
    {
        return Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
    }

    #[On('notification-read')]
    #[On('notifications-updated')]
    public function checkNotifications(): void
    {
        // Triggers re-computation of unread count
    }

    public function render()
    {
        return view('livewire.components.notification.notification-bell', [
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
