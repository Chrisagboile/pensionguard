<?php

namespace App\Modules\PensionAccountGuard\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class PensionGuardResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('pension-guard.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your Pension Account Guard password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received a request to reset your Pension Account Guard password.')
            ->action('Reset Password', $url)
            ->line('This link expires in 60 minutes.')
            ->line('If you did not request this reset, you can ignore this email.');
    }
}
