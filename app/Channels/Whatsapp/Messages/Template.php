<?php

namespace App\Channels\WhatsApp\Messages;

abstract class Template
{
    public string $templateID;

    public string $variables;

    public function variables(array $variables)
    {
        if (count($variables)) {
            $this->variables = json_encode(array_combine(
                range(1, count($variables)),
                $variables
            ));
        }

        return $this;
    }
}
