<?php

declare(strict_types=1);

namespace App\Livewire\Components\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationContent extends Component
{
    #[Computed]
    public function notifications()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()->notifications()->latest()->take(15)->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead(string $id, ?string $redirectUrl = null)
    {
        if (!Auth::check()) {
            return;
        }

        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-read');
        }

        if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to($redirectUrl);
        } elseif ($redirectUrl && str_starts_with($redirectUrl, '/')) {
            return $this->redirect($redirectUrl, navigate: true);
        }
    }

    #[On('mark-all-notifications-read')]
    public function markAllAsRead(): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notifications-updated');
    }

    public function deleteNotification(string $id): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->notifications()->where('id', $id)->delete();
        $this->dispatch('notifications-updated');
    }

    public function render()
    {
        return view('livewire.components.notification.notification-content', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
