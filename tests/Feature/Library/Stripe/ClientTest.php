<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Checkout;
use App\Library\Stripe\Client;
use App\Library\Stripe\Customer;
use App\Library\Stripe\Price;
use App\Library\Stripe\Product;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_customers_statice_function()
    {
        $this->assertEquals(
            new Customer,
            Client::customers()
        );
    }

    public function test_products_statice_function()
    {
        $this->assertEquals(
            new Product,
            Client::products()
        );
    }

    public function test_prices_statice_function()
    {
        $this->assertEquals(
            new Price,
            Client::prices()
        );
    }

    public function test_checkouts_statice_function()
    {
        $this->assertEquals(
            new Checkout,
            Client::checkouts()
        );
    }
}
