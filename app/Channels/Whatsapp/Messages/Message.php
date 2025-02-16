<?php

namespace App\Channels\WhatsApp\Messages;

class Message
{
    public $content;

    public $mediaUrl = null;

    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    public function mediaUrl($mediaUrl)
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }
}
