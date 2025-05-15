<?php

namespace App\Library\Stripe\Concerns\Models;

trait Base
{
    public ?array $stripe = null;

    abstract public function stripeCreate(): array;

    abstract public function getStripe(): ?array;
}
