<?php


namespace App\Channels\WhatsApp\Messages;

abstract class Template
{
    public $templateID;
    public $variables;

    public function variables($variables)
    {
        $this->variables = $variables;

        return $this;
    }
}
