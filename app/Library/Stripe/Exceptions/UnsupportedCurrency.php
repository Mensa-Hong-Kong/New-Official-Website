<?php

namespace App\Library\Stripe\Exceptions;

use Exception;

class UnsupportedCurrency extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(string $currency)
    {
        parent::__construct("The currency '".strtolower($currency)."' is not supported.");
    }
}
