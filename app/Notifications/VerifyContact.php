<?php

namespace App\Notifications;

use App\Channels\Whatsapp\Messages\VerificationCode;
use App\Channels\Whatsapp\Template;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyContact extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        private $type,
        private $code
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
            ->subject('Verify Email Address')
            ->line("Your verify code is {$this->code}");
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new VerificationCode)
            ->variables(['1' => $this->code]);
    }
}
