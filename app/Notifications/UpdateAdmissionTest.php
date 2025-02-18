<?php

namespace App\Notifications;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpdateAdmissionTest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private $from,
        private $to
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $return = [];
        if ($notifiable->defaultEmail) {
            $return[] = 'mail';
        }
        if ($notifiable->defaultMobile) {
            $return[] = Channel::class;
        }

        return $return;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We changed assigned admission test some detail(s).')
            ->line('')
            ->line('form:')
            ->line("Date: {$this->from['testing_date']}")
            ->line("Time: {$this->from['testing_time']} - {$this->from['expect_end_time']}")
            ->line("Location: {$this->from['location']}")
            ->line("Address: {$this->from['address']}")
            ->line('')
            ->line('To:')
            ->line("Date: {$this->to['testing_date']}")
            ->line("Time: {$this->to['testing_time']} - {$this->to['expect_end_time']}")
            ->line("Location: {$this->to['location']}")
            ->line("Address: {$this->to['address']}")
            ->line('')
            ->line('If you need to reschedule, please contact "test@mensa.org.hk".');
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new Message)
            ->content(
                implode(
                    "\n", [
                        'We changed assigned admission test some detail(s).',
                        'form:',
                        "Date: {$this->from['testing_date']}",
                        "Time: {$this->from['testing_time']} - {$this->from['expect_end_time']}",
                        "Location: {$this->from['location']}",
                        "Address: {$this->from['address']}",
                        '',
                        'To:',
                        "Date: {$this->to['testing_date']}",
                        "Time: {$this->to['testing_time']} - {$this->to['expect_end_time']}",
                        "Location: {$this->to['location']}",
                        "Address: {$this->to['address']}",
                        '',
                        'If you need to reschedule, please contact "test@mensa.org.hk".',
                    ]
                ),
            );
    }
}
