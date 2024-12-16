<?php

namespace App\Channels\WhatsApp\Messages;

class Message
{
    public $content;

    public function content($content)
    {
        $this->content = $content;

        return $this;
    }
}
