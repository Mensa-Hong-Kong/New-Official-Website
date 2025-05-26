<?php

namespace Tests\Feature\Webhooks\Stripe;

use App\Http\Controllers\WebHooks\StripeController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;

class HandleTest extends TestCase
{
    public function test_unexpect_type()
    {
        $request = new Request(request: ['type' => 'setup_intent.created']);
        $spy = $this->spy(StripeController::class)
            ->makePartial();
        $spy->handle($request);
        $spy->shouldNotHaveReceived('customerDeleted');
        $response = (new StripeController)->handle($request);
        $this->assertEquals('', $response);
    }

    public function test_customer_deleted_handle()
    {
        $request = new Request(request: ['type' => 'customer.deleted']);
        $spy = $this->spy(StripeController::class)
            ->makePartial();
        $spy->shouldAllowMockingProtectedMethods()
            ->shouldReceive('customerDeleted');
        $spy->handle($request);
        $spy->shouldHaveReceived('customerDeleted');
    }
}
