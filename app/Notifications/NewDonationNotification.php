<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDonationNotification extends Notification
{
    use Queueable;

    public function __construct(public Donation $donation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تبرع جديد عبر موقع همم')
            ->greeting('مرحباً!')
            ->line('وصل طلب تبرع جديد.')
            ->line('**الاسم:** ' . $this->donation->full_name)
            ->line('**الدولة:** ' . $this->donation->country)
            ->line('**الجوال:** ' . $this->donation->phone)
            ->line('**البريد الإلكتروني:** ' . $this->donation->email)
            ->line('**المبلغ:** ' . $this->donation->amount . ' ' . $this->donation->currency)
            ->when($this->donation->message, function ($mail) {
                return $mail->line('**الرسالة:**')->line($this->donation->message);
            })
            ->action('عرض التبرعات', url('http://localhost:3000/admin/dashboard'))
            ->line('نظام همم');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'donation_id' => $this->donation->id,
            'name' => $this->donation->full_name,
        ];
    }
}
