<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Exception;

class VerifyContact extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private $contact,
        private $verificationCode
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        switch($this->contact->type->name) {
            case 'email':
                return ['mail'];
            case 'mobile':
                return [WhatsAppChannel::class];
            default:
                throw new Exception("Unexpected contact type: {$this->contact->type->name}");
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line("Your {$this->contact->type->name} verification code is: {$this->verificationCode}");
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new WhatsAppMessage)
            ->content("Your {$this->contact->type->name} verification code is: {$this->verificationCode}");
    }
}
