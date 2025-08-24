<?php

namespace App\Notifications\AdmissionTest;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupHTML;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ScheduleAdmissionTest extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        private $test
    ) {}

    private function qrCode($user)
    {
        $options = new QROptions;

        $options->version = 5;
        $options->outputInterface = QRMarkupHTML::class;
        $options->cssClass = 'qrcode';
        $options->moduleValues = [
            // finder
            QRMatrix::M_FINDER_DARK => '#A71111', // dark (true)
            QRMatrix::M_FINDER_DOT => '#A71111', // finder dot, dark (true)
            QRMatrix::M_FINDER => '#FFBFBF', // light (false)
            // alignment
            QRMatrix::M_ALIGNMENT_DARK => '#A70364',
            QRMatrix::M_ALIGNMENT => '#FFC9C9',
        ];

        $out = (new QRCode($options))->render(
            route(
                'admin.admission-tests.candidates.show', [
                    'admission_test' => $this->test,
                    'candidate' => $user,
                ]
            )
        );

        return $out;
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
            ->subject('you are scheduled a admission test.')
            ->line('Date: '.$this->test->testing_at->format('Y-m-d'))
            ->line('Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'))
            ->line('Location: '.$this->test->location->name)
            ->line("Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}")
            ->line('Ticket:')
            ->line(new HtmlString('<img src="'.$this->qrCode($notifiable).'">'))
            ->line('')
            ->line('Remember:')
            ->line('1. Please bring your own pencil.')
            ->line('2. Please bring your own ticket QR code.')
            ->line('3. Please bring your own Hong Kong/Macau/(Mainland) Resident ID.')
            ->line('4. Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.');
    }

    public function toWhatsApp(object $notifiable)
    {
        // content maximum 1600 character
        return (new Message)
            ->content(
                implode(
                    "\n", [
                        'You are scheduled a admission test.',
                        'Date: '.$this->test->testing_at->format('Y-m-d'),
                        'Time: '.$this->test->testing_at->format('H:i').' - '.$this->test->expect_end_at->format('H:i'),
                        'Location: '.$this->test->location->name,
                        "Address: {$this->test->address->address}, {$this->test->address->district->name}, {$this->test->address->district->area->name}",
                        '',
                        'Remember:',
                        '1. Please bring your own pencil.',
                        '2. Please bring your own ticket QR code.',
                        '3. Please bring your own Hong Kong/Macau/(Mainland) Resident ID.',
                        '4. Candidates should arrive 20 minutes before the test session. Latecomers may be denied entry.',
                    ]
                ),
            )->mediaUrl(
                'https://quickchart.io/qr?caption=Ticket&text='.urlencode(
                    route(
                        'admin.admission-tests.candidates.show', [
                            'admission_test' => $this->test,
                            'candidate' => $notifiable,
                        ]
                    )
                )
            );
    }
}
