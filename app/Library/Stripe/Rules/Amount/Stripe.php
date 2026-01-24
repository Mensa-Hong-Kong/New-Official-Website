<?php

namespace App\Library\Stripe\Rules\Amount;

use App\Library\Stripe\Amount;

class Stripe extends Base
{
    public function __construct()
    {
        $this->priceDecimal = Amount::getActualDecimal();
        $this->minimum = config('stripe.minimum_amount', 4);
        parent::__construct();
    }
}
