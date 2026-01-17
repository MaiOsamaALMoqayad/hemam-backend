<?php

namespace App\Notifications;

use App\Models\ActivityRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewActivityRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ActivityRequest $request
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('طلب انضمام جديد لنشاط - موقع همم')
            ->greeting('مرحباً!')
            ->line('لديك طلب انضمام جديد لأحد أنشطة موقع همم.')
            ->line('**الاسم:** ' . $this->request->name)
            ->line('**العمر:** ' . $this->request->age)
            ->line('**رقم الجوال:** ' . $this->request->phone)
            ->line('**الجنس:** ' . ($this->request->gender === 'male' ? 'ذكر' : 'أنثى'))
            ->line('**النشاط:** ' . $this->request->activity->getTranslated('title'))
            ->action('مراجعة الطلب في لوحة التحكم', url('/admin/activity-requests/' . $this->request->id))
            ->line('شكراً لاستخدامك نظام همم!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'name' => $this->request->name,
            'activity_id' => $this->request->activity_id,
        ];
    }
}
