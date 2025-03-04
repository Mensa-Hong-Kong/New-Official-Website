<?php

namespace App\Notifications\AdmissionTest\Admin;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use App\Models\AdmissionTest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CanceledAdmissionTest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private AdmissionTest $test
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
            ->subject('We are canceled admission test.')
            ->line('We are canceled admission test, you can reschedule to other admission test on out website.')
            ->line('Date: '.$this->test->testing_at->format('Y-m-d'))
            ->line('Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'))
            ->line('Location: '.$this->test->location->name)
            ->line("Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}");
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new Message)
            ->content(
                implode(
                    "\n", [
                        'We are canceled admission test.',
                        'You can reschedule to other admission test on out website.',
                        'Date: '.$this->test->testing_at->format('Y-m-d'),
                        'Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'),
                        'Location: '.$this->test->location->name,
                        "Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}",
                    ]
                ),
            );
    }
}
