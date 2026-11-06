<?php

namespace App\Modules\PensionAccountGuard\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class PensionGuardVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'pension-guard.verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Verify your Pension Account Guard email')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Thank you for creating a Pension Account Guard account.')
            ->line('Please verify your email address to activate access to your account.')
            ->action('Verify Email Address', $url)
            ->line('This verification link expires in 60 minutes.');
    }
}
