<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailNotification extends VerifyEmail
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => __('notifications.types.email_verification'),
            'mensaje' => __('notifications.mail.email_verification'),
            'action_url' => route('verification.notice'),
            'icon' => 'fa-envelope-open-text',
        ];
    }
}
