<?php

namespace App\Notifications\AdmissionTest\Admin;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedAdmissionTestRecord extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        private AdmissionTest $test,
        private AdmissionTestHasCandidate $pivot,
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
        $return = (new MailMessage)
            ->subject('We are removed your admission test record.')
            ->line('Date: '.$this->test->testing_at->format('Y-m-d'))
            ->line('Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'))
            ->line('Location: '.$this->test->location->name)
            ->line("Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}");
        if (in_array($this->pivot->is_pass, ['0', '1'])) {
            $return = $return->line('Result: '.($this->pivot->is_pass ? 'Pass' : 'Fail'));
        }

        return $return;
    }

    public function toWhatsApp(object $notifiable)
    {
        // content maximum 1600 character
        $message = [
            'We are removed your admission test record.',
            'Date: '.$this->test->testing_at->format('Y-m-d'),
            'Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'),
            'Location: '.$this->test->location->name,
            "Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}",
        ];
        if (in_array($this->pivot->is_pass, ['0', '1'])) {
            $message[] = 'Result: '.($this->pivot->is_pass ? 'Pass' : 'Fail');
        }

        return (new Message)->content(implode("\n", $message));
    }
}
