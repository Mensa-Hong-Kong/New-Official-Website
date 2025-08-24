<?php

namespace App\Notifications\AdmissionTest\Admin;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use App\Models\AdmissionTest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailAdmissionTest extends Notification
{
    private $date;

    private $time;

    /**
     * Create a new notification instance.
     */
    public function __construct(AdmissionTest $test)
    {
        $this->date = $test->testing_at->format('Y-m-d');
        $this->time = $test->testing_at->format('H:i');
    }

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
            ->subject('Mensa HK - Admission Test Result.')
            ->line("Thank you for taking the Mensa Admission Test on $this->date at $this->time.")
            ->line('')
            ->line('To qualify for membership, applicants must score in the top 2% of the population on the admission test. Based on the result of your recent test, we cannot offer you membership at this time. ')
            ->line('')
            ->line('If this is your first time taking the Mensa Admission Test, you can sign up for a retest in the portal 6 - 18 months after your first attempt (https://www.mensa.org.hk/profile). Please contact the Membership Secretary at membership@mensa.org.hk if you have any questions.');
    }

    public function toWhatsApp(object $notifiable)
    {
        // content maximum 1600 character
        return (new Message)
            ->content(
                implode(
                    "\n", [
                        'Mensa HK - Admission Test Result.',
                        "Thank you for taking the Mensa Admission Test on $this->date at $this->time.",
                        '',
                        'To qualify for membership, applicants must score in the top 2% of the population on the admission test. Based on the result of your recent test, we cannot offer you membership at this time. ',
                        '',
                        'If this is your first time taking the Mensa Admission Test, you can sign up for a retest in the portal 6 - 18 months after your first attempt (https://www.mensa.org.hk/profile). Please contact the Membership Secretary at membership@mensa.org.hk if you have any questions.',
                    ]
                ),
            );
    }
}
