<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use App\Library\Stripe\Customer;
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
}
