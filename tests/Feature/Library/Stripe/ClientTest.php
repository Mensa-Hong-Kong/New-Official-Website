<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use App\Library\Stripe\Customer;
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
}
