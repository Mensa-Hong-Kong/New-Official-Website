<?php

namespace App\Channels\WhatsApp\Messages;

class NewPassword extends Template
{
    public function __construct()
    {
        $this->templateID = config('services.twilio.whatsapp.templateIDs.newPassword');
    }
}
