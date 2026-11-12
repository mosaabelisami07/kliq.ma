<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewInquiryReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Inquiry $inquiry) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $inquiry = $this->inquiry;

        return (new MailMessage)
            ->subject("New inquiry from {$inquiry->name}")
            ->replyTo($inquiry->email, $inquiry->name)
            ->greeting('New website inquiry')
            ->line("**From:** {$inquiry->name}".($inquiry->company ? " ({$inquiry->company})" : ''))
            ->line("**Email:** {$inquiry->email}")
            ->lineIf(filled($inquiry->phone), "**Phone:** {$inquiry->phone}")
            ->lineIf(filled($inquiry->service_name), "**Service:** {$inquiry->service_name}")
            ->line('**Message:**')
            ->line(new HtmlString(nl2br(e($inquiry->message), false)))
            ->action('Open in dashboard', route('admin.inquiries.show', $inquiry))
            ->salutation('— KLIQ website');
    }
}
