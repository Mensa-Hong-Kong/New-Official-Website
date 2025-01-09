<?php

namespace App\Notifications;

use App\Channels\Whatsapp\Messages\NewPassword;
use App\Channels\Whatsapp\Template;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        private $type,
        private $newPassword
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        switch ($this->type) {
            case 'email':
                return ['email'];
            case 'mobile':
                return [Template::class];
            default:
                return [];
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->line("Your new password is {$this->newPassword}. Please change you password by edit profile As Soon As Possible!");
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new NewPassword)
            ->variables(['1' => $this->newPassword]);
    }
}
