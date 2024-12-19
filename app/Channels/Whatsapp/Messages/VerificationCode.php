<?php

namespace App\Channels\WhatsApp\Messages;

class VerificationCode extends Template
{
    public function __construct()
    {
        $this->templateID = config('services.twilio.whatsapp.templateIDs.verificationCode');
    }
}
