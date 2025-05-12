<?php

namespace App\Library\Stripe;

class Client
{
    public static function customers()
    {
        return new Customer;
    }

    public static function products()
    {
        return new Product;
    }

    public static function prices()
    {
        return new Price;
    }

    public static function checkouts()
    {
        return new Checkout;
    }
}
