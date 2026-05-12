<?php

namespace App\Library\Stripe\Rules\Amount;

use App\Library\Stripe\Amount;
use Illuminate\Validation\Rules\Numeric;

abstract class Base extends Numeric
{
    public function setUp(int $priceDecimal, ?int $minimum = null)
    {
        $this->max(Amount::getMaximumValidation());
        if ($minimum) {
            $this->min($minimum);
        } else {
            $this->min(1 * 10 ** (-$priceDecimal));
        }
        if ($priceDecimal > 0) {
            $this->decimal(0, $priceDecimal);
        } else {
            $this->integer();
        }
    }
}
