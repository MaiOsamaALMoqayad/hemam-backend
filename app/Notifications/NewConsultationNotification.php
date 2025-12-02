<?php

namespace App\Notifications;

use App\Models\ExpertConsultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConsultationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ExpertConsultation $consultation
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
            ->subject('طلب استشارة خبير جديد - موقع همم')
            ->greeting('مرحباً!')
            ->line('لديك طلب استشارة خبير جديد من موقع همم.')
            ->line('**الاسم:** ' . $this->consultation->name)
            ->line('**رقم الواتساب:** ' . $this->consultation->country_code . $this->consultation->whatsapp)
            ->line('**نوع الاستشارة:** ' . $this->getConsultationTypeLabel())
            ->line('**تفاصيل الاستشارة:**')
            ->line($this->consultation->consultation_details)
            ->when($this->consultation->notes, function ($mail) {
                return $mail->line('**ملاحظات إضافية:**')
                    ->line($this->consultation->notes);
            })
            ->action('مراجعة الطلب في لوحة التحكم', url('/admin'))
            ->line('شكراً لاستخدامك نظام همم!');
    }

    /**
     * Get consultation type label in Arabic.
     */
    private function getConsultationTypeLabel(): string
    {
        return match($this->consultation->consultation_type) {
            'educational' => 'تربوية',
            'administrative' => 'إدارية',
            'leadership' => 'قيادية',
            'personal' => 'شخصية',
            default => $this->consultation->consultation_type,
        };
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'consultation_id' => $this->consultation->id,
            'name' => $this->consultation->name,
            'type' => $this->consultation->consultation_type,
        ];
    }
}
