<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Alerts extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times to attempt this notification.
     */
    public int $tries = 3;

    /**
     * Create a new notification instance.
     *
     * @param string $title Alert heading / subject
     * @param string $message Main notification description
     * @param string $type Alert level: 'info', 'success', 'warning', 'danger'
     * @param string|null $actionUrl Optional redirect link
     * @param string|null $actionText Optional button/link text
     * @param array<int, string> $channels Delivery channels (e.g. ['database', 'mail'])
     * @param bool $sendSms Whether to also dispatch an SMS notification via iProgSMS
     * @param string|null $smsMessage Custom SMS text (defaults to "[PGPC Library] {title}: {message}")
     */
    public function __construct(
        public string $title = 'Library Alert',
        public string $message = 'You have a new library notification.',
        public string $type = 'info',
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public ?string $bookTitle = null,
        public ?string $bookCover = null,
        public ?string $dueDate = null,
        public array $channels = ['database', 'mail'],
        public bool $sendSms = false,
        public ?string $smsMessage = null,
        public array $details = []
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = $this->channels;

        // Ensure database channel is always used for in-app notifications if requested
        if (!in_array('database', $channels, true) && in_array('app', $channels, true)) {
            $channels[] = 'database';
        }

        // If mail is included but notifiable doesn't have an email address, skip mail
        if (in_array('mail', $channels, true) && empty($notifiable->email)) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PGPC Library - ' . $this->title)
            ->view('emails.template', [
                'title'      => $this->title,
                'message'    => $this->message,
                'bookTitle'  => $this->bookTitle,
                'dueDate'    => $this->dueDate,
                'details'    => $this->details,
                'actionUrl'  => $this->actionUrl,
                'actionText' => $this->actionText ?? 'View in Library System',
                'user'       => $notifiable->student ?? $notifiable,
            ]);
    }

    /**
     * Get the database representation of the notification for in-app display.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // If SMS was requested alongside in-app/mail, dispatch via SmsService
        if ($this->sendSms) {
            $phone = $notifiable->student?->contact_num ?? $notifiable->contact_num ?? null;
            if (!empty($phone)) {
                $bookSuffix = $this->bookTitle ? " (\"{$this->bookTitle}\")" : "";
                $smsText = $this->smsMessage ?? ("PGPC Library: {$this->title}{$bookSuffix} - " . strip_tags($this->message));
                SmsService::queue($phone, $smsText);
            }
        }

        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'action_url'  => $this->actionUrl,
            'action_text' => $this->actionText,
            'book_title'  => $this->bookTitle,
            'book_cover'  => $this->bookCover,
            'due_date'    => $this->dueDate,
            'details'     => $this->details,
            'icon'        => $this->resolveIcon(),
            'created_at'  => now()->toISOString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Map notification type to an icon identifier.
     */
    protected function resolveIcon(): string
    {
        return match ($this->type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'danger', 'error' => 'x-circle',
            default => 'information-circle',
        };
    }
}
