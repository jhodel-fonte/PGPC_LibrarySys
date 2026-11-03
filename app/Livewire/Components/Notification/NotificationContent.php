<?php

declare(strict_types=1);

namespace App\Livewire\Components\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationContent extends Component
{
    public function unreadCount(): int
    {
        return Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
    }

    public function page(?string $cursor = null): array
    {
        abort_unless(Auth::check(), 401);
        $page = Auth::user()->notifications()->orderByDesc('created_at')->orderByDesc('id')
            ->cursorPaginate(15, ['*'], 'cursor', $cursor);
        return [
            'items' => $page->getCollection()->map(fn ($item) => $this->notificationData($item))->all(),
            'cursor' => $page->nextCursor()?->encode(),
        ];
    }

    public function current(array $ids): array
    {
        abort_unless(Auth::check(), 401);
        abort_if(count($ids) > 200, 422);
        return Auth::user()->notifications()->whereIn('id', $ids)->get()
            ->map(fn ($item) => $this->notificationData($item))->all();
    }

    private function notificationData($notification): array
    {
        $data = $notification->data;
        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Library Alert',
            'message' => $data['message'] ?? '',
            'book_title' => $data['book_title'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'has_action' => ! empty($data['action_url']) || ! empty($data['reservation_id']),
            'action_text' => $data['action_text'] ?? 'View',
            'status' => $data['status'] ?? null,
            'download_url' => $notification->type === \App\Jobs\ExportSpreadsheet::class && ($data['status'] ?? null) === 'completed' ? route('exports.download', $notification->id) : null,
            'time' => $notification->created_at?->diffForHumans(),
            'unread' => $notification->read_at === null,
        ];
    }

    public function markAsRead(string $id, bool $open = false)
    {
        if (!Auth::check()) {
            return;
        }

        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($open && $notification && ! empty($notification->data['reservation_id'])) {
            abort_unless(Auth::user()->status_id == 1 && (Auth::user()->isAdmin() || (Auth::user()->hasRole(['Librarian', 'Head Librarian']) && Auth::user()->canManageCirculation())), 403);
        }
        if ($notification) {
            $notification->markAsRead();

        }

        if ($open && $notification) {
            $data = $notification->data;
            if (! empty($data['reservation_id'])) {
                $prefix = Auth::user()->isAdmin() ? 'admin.' : 'librarian.';
                return $this->redirect(route($prefix . 'reservations', ['search' => $data['reservation_id']]), navigate: true);
            }
            $url = $data['action_url'] ?? '';
            if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) return $this->redirect($url, navigate: true);
            if (filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST)) return redirect()->to($url);
        }
    }

    public function markAllAsRead(): int
    {
        abort_unless(Auth::check(), 401);
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return $this->unreadCount();
    }

    public function deleteNotification(string $id): int
    {
        abort_unless(Auth::check(), 401);
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification?->type === \App\Jobs\ExportSpreadsheet::class) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete('exports/'.$notification->id.'.xlsx');
        }
        $notification?->delete();
        return $this->unreadCount();
    }

    public function render()
    {
        return view('livewire.components.notification.notification-content');
    }
}
