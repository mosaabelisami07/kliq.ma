<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
     * Get the mail representation of the notification, with every detail the
     * visitor submitted (see resources/views/mail/new-inquiry.blade.php).
     */
    public function toMail(object $notifiable): MailMessage
    {
        $inquiry = $this->inquiry;

        return (new MailMessage)
            ->subject('New inquiry from '.$inquiry->name.($inquiry->company ? " ({$inquiry->company})" : ''))
            ->replyTo($inquiry->email, $inquiry->name)
            ->markdown('mail.new-inquiry', ['inquiry' => $inquiry]);
    }
}
