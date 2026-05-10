<?php

namespace Tests\Feature\Library\Stripe\Webhooks;

use App\Library\Stripe\Http\Controllers\WebHooks\Controller;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;

class HandleTest extends TestCase
{
    public function test_unexpect_type(): void
    {
        $request = new Request(request: ['type' => 'setup_intent.created']);
        $spy = $this->spy(Controller::class)
            ->makePartial();
        $this->app->make(Controller::class)->handle($request);
        $spy->shouldNotHaveReceived('customerDeleted');
        $response = (new Controller)->handle($request);
        $this->assertEquals('', $response);
    }

    public function test_customer_deleted_handle(): void
    {
        $request = new Request(request: ['type' => 'customer.deleted']);
        $spy = $this->spy(Controller::class)
            ->makePartial();
        $spy->shouldAllowMockingProtectedMethods()
            ->shouldReceive('customerDeleted');
        $this->app->make(Controller::class)->handle($request);
        $spy->shouldHaveReceived('customerDeleted');
    }
}
