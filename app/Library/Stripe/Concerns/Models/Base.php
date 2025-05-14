<?php

namespace App\Library\Stripe\Concerns\Models;

trait Base
{
    public null|array $stripe = null;

    public abstract function stripeCreate(): array;

    public abstract function getStripe(): array|null;
}
