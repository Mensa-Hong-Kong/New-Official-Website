<?php

namespace App\Library\Stripe;

class Client
{
    public static function customers()
    {
        return new Customer;
    }
}
