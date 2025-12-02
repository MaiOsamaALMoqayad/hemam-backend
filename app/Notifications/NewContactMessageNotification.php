<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ContactMessage $contactMessage
    ) {}

    /**
     * Get the notification's delivery channels.
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
        return (new MailMessage)
            ->subject('رسالة تواصل جديدة من موقع همم')
            ->greeting('مرحباً!')
            ->line('لديك رسالة تواصل جديدة من موقع همم.')
            ->line('**من:** ' . $this->contactMessage->name)
            ->line('**البريد الإلكتروني:** ' . $this->contactMessage->email)
            ->line('**الجوال:** ' . $this->contactMessage->phone)
            ->line('**الموضوع:** ' . $this->contactMessage->subject)
            ->line('**الرسالة:**')
            ->line($this->contactMessage->message)
            ->action('عرض في لوحة التحكم', url('/admin'))
            ->line('شكراً لاستخدامك نظام همم!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'contact_message_id' => $this->contactMessage->id,
            'name' => $this->contactMessage->name,
            'email' => $this->contactMessage->email,
        ];
    }
}
