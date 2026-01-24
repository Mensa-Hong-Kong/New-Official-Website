<?php

namespace App\Library\Stripe\Concerns\Models;

trait Base
{
    public ?array $stripeData = null;

    abstract public function getStripe(): ?array;
}
