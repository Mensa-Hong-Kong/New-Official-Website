<?php

namespace App\Channels\Whatsapp;

use Twilio\Rest\Client;

class Channel
{
    public $twilio;

    public $from;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        $this->from = config('services.twilio.whatsapp.from');
    }
}
