<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReligiousPasswordReset extends Notification
{
    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('استعادة حساب حق')
            ->greeting('استعادة كلمة المرور')
            ->line('أدخل رمز الاستعادة التالي داخل تطبيق حق. تنتهي صلاحيته بعد 30 دقيقة.')
            ->line($this->token)
            ->line('إذا لم تطلب استعادة الحساب، تجاهل هذه الرسالة.');
    }
}
