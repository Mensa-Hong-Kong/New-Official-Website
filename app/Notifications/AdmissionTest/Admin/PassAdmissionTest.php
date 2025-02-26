<?php

namespace App\Notifications\AdmissionTest\Admin;

use App\Channels\Whatsapp\Message as Channel;
use App\Channels\Whatsapp\Messages\Message;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PassAdmissionTest extends Notification
{
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
            ->line('Further to the supervised Mensa entry test, which you took at Zetland Hall on {{date}} at {{time}}, we are pleased to inform you that based on your test score, your intelligence quotient (IQ) appears to be in the top 2% of the population.')
            ->line('')
            ->line('We are pleased to invite you to join Mensa Hong Kong, which is part of the worldwide Mensa organisation that currently has around 150,000 members in over 50 countries.')
            ->line('')
            ->line('INFORMATION ABOUT MENSA HK')
            ->line('Mensa Hong Kong was established in 1987 and currently has over 3,200 members covering a wide range of ages, occupations, nationalities, and interests.')
            ->line('')
            ->line('All members are subject to the constitution of Mensa Hong Kong – a copy of which is uploaded at: https://www.mensa.org.hk/constitution. You are also encouraged to read the constitution in order to know more about the operations of Mensa Hong Kong.')
            ->line('')
            ->line('Members\' Privileges')
            ->line('As a member of Mensa Hong Kong, you will receive monthly newsletters with details on Mensa activities, as well as news from fellow members in various locations around the world. Please visit our website for the list of the privileges offered to members of Mensa Hong Kong: https://www.mensa.org.hk/privileges, as well as some of the special interest groups (SIG) which members can join: https://www.mensa.org.hk/list-of-sigs.')
            ->line('If you enjoy travelling, there are numerous SIGHT (\'Service of Information, Guidance and Hospitality to Travellers\') officers around the world. Mensans can also join international gatherings hosted by Mensa groups in other countries, connect with the Mensa International Community via Workplace, and meet Mensans from other countries!')
            ->line('')
            ->line('Members\' Activities')
            ->line('Membership activities include talks and presentations on various topics of interest, ‘dim sum’ lunches and dinners, hikes and excursions, regular board game gatherings, and “Last Friday of the Month” social gatherings. Mensa Hong Kong is run entirely by unpaid volunteers from within its membership. All members are encouraged to participate in and organise events – if there is an event you want to happen, the best way of achieving this is to volunteer to organise it!')
            ->line('')
            ->line('Membership')
            ->line('Members are required to pay annual subscriptions as set by the Board. In order to become a fully paid-up member, those members who successfully pass our admissions test part way through a year must pay their membership dues as requested in order to become an active member.')
            ->line('')
            ->line('Please note that new members will normally be deleted from our membership database if they fail to pay their membership dues within two months of passing the admissions test.')
            ->line('')
            ->line('The membership fee for 2024/2025 is HK$ 400 ($200 if you are under 21 years old).')
            ->line('')
            ->line('You can pay for membership online at the portal: https://www.mensa.org.hk/auth/login')
            ->line('')
            ->line('You can refer to this page for more information on activating your membership:-')
            ->line('https://www.mensa.org.hk/payment-of-membership-for-new-members')
            ->line('')
            ->line('Here is basic but important information for new members (for active members only - requires login):')
            ->line('https://www.mensa.org.hk/information-for-active-members-only')
            ->line('')
            ->line('Please e-mail the Membership Secretary at membership@mensa.org.hk if you have any questions.')
            ->line('')
            ->line('Finally, following your success, please encourage your friends and colleagues to take the Mensa admission test, which is held every month throughout the year.  We look forward to welcoming you as a member of Mensa Hong Kong.');
    }

    public function toWhatsApp(object $notifiable)
    {
        return (new Message)
            ->content(
                implode(
                    "\n", [
                        'Mensa HK - Admission Test Result.',
                        'Further to the supervised Mensa entry test, which you took at Zetland Hall on {{date}} at {{time}}, we are pleased to inform you that based on your test score, your intelligence quotient (IQ) appears to be in the top 2% of the population.',
                        '',
                        'We are pleased to invite you to join Mensa Hong Kong, which is part of the worldwide Mensa organisation that currently has around 150,000 members in over 50 countries.',
                        '',
                        'INFORMATION ABOUT MENSA HK',
                        'Mensa Hong Kong was established in 1987 and currently has over 3,200 members covering a wide range of ages, occupations, nationalities, and interests.',
                        '',
                        'All members are subject to the constitution of Mensa Hong Kong – a copy of which is uploaded at: https://www.mensa.org.hk/constitution. You are also encouraged to read the constitution in order to know more about the operations of Mensa Hong Kong.',
                        '',
                        'Members\' Privileges',
                        'As a member of Mensa Hong Kong, you will receive monthly newsletters with details on Mensa activities, as well as news from fellow members in various locations around the world. Please visit our website for the list of the privileges offered to members of Mensa Hong Kong: https://www.mensa.org.hk/privileges, as well as some of the special interest groups (SIG) which members can join: https://www.mensa.org.hk/list-of-sigs.',
                        'If you enjoy travelling, there are numerous SIGHT (\'Service of Information, Guidance and Hospitality to Travellers\') officers around the world. Mensans can also join international gatherings hosted by Mensa groups in other countries, connect with the Mensa International Community via Workplace, and meet Mensans from other countries!',
                        '',
                        'Members\' Activities',
                        'Membership activities include talks and presentations on various topics of interest, ‘dim sum’ lunches and dinners, hikes and excursions, regular board game gatherings, and “Last Friday of the Month” social gatherings. Mensa Hong Kong is run entirely by unpaid volunteers from within its membership. All members are encouraged to participate in and organise events – if there is an event you want to happen, the best way of achieving this is to volunteer to organise it!',
                        '',
                        'Membership',
                        'Members are required to pay annual subscriptions as set by the Board. In order to become a fully paid-up member, those members who successfully pass our admissions test part way through a year must pay their membership dues as requested in order to become an active member.',
                        '',
                        'Please note that new members will normally be deleted from our membership database if they fail to pay their membership dues within two months of passing the admissions test.',
                        '',
                        'The membership fee for 2024/2025 is HK$ 400 ($200 if you are under 21 years old).',
                        '',
                        'You can pay for membership online at the portal: https://www.mensa.org.hk/auth/login',
                        '',
                        'You can refer to this page for more information on activating your membership:-',
                        'https://www.mensa.org.hk/payment-of-membership-for-new-members',
                        '',
                        'Here is basic but important information for new members (for active members only - requires login):',
                        'https://www.mensa.org.hk/information-for-active-members-only',
                        '',
                        'Please e-mail the Membership Secretary at membership@mensa.org.hk if you have any questions.',
                        '',
                        'Finally, following your success, please encourage your friends and colleagues to take the Mensa admission test, which is held every month throughout the year.  We look forward to welcoming you as a member of Mensa Hong Kong.',
                    ]
                ),
            );
    }
}
