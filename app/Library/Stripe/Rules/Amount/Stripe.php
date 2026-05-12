<?php

namespace App\Library\Stripe\Rules\Amount;

use App\Library\Stripe\Amount;

class Stripe extends Base
{
    public function __construct()
    {
        $this->setUp(Amount::getActualDecimal(), config('stripe.minimum_amount', 4));
    }
}
