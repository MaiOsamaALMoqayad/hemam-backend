<?php

namespace App\Notifications;

use App\Models\TrainerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTrainerApplicationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TrainerApplication $application
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
        $trainingFields = collect($this->application->training_fields)->map(function ($field) {
            return match($field) {
                'leadership' => 'قيادة',
                'management' => 'إدارة',
                'education' => 'تربية',
                'guidance' => 'توجيه',
                'odt_activities' => 'فعاليات ODT',
                'psychological_support' => 'دعم نفسي',
                'sharia_sciences' => 'علوم شرعية',
                'other' => 'غير ذلك',
                default => $field,
            };
        })->join('، ');

        return (new MailMessage)
            ->subject('طلب انضمام مدرب جديد - موقع همم')
            ->greeting('مرحباً!')
            ->line('لديك طلب انضمام مدرب جديد من موقع همم.')
            ->line('**الاسم الكامل:** ' . $this->application->full_name)
            ->line('**العمر:** ' . $this->application->age . ' سنة')
            ->line('**البريد الإلكتروني:** ' . $this->application->email)
            ->line('**الجوال:** ' . $this->application->country_code . $this->application->phone)
            ->line('**المؤهل العلمي:** ' . $this->getQualificationLabel())
            ->line('**التخصص:** ' . $this->application->specialization)
            ->line('**سنوات الخبرة:** ' . $this->application->experience_years . ' سنة')
            ->line('**مجالات التدريب:** ' . $trainingFields)
            ->action('مراجعة الطلب في لوحة التحكم', url('/admin'))
            ->line('شكراً لاستخدامك نظام همم!');
    }

    /**
     * Get qualification label in Arabic.
     */
    private function getQualificationLabel(): string
    {
        return match($this->application->qualification) {
            'high_school' => 'ثانوية عامة',
            'bachelor' => 'بكالوريوس',
            'master' => 'ماجستير',
            'other' => $this->application->qualification_other ?? 'غير ذلك',
            default => $this->application->qualification,
        };
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'full_name' => $this->application->full_name,
            'email' => $this->application->email,
        ];
    }
}
