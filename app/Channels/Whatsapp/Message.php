<?php

namespace App\Channels\Whatsapp;

use Illuminate\Notifications\Notification;

class Message extends Channel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);

        $to = $notifiable->routeNotificationFor('WhatsApp');

        return $this->twilio->messages->create('whatsapp:'.$to, [
            'from' => 'whatsapp:'.$this->from,
            'body' => $message->content,
        ]);
    }
}
