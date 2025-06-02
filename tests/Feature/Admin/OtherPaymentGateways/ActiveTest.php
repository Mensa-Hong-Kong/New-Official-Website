<?php

namespace Tests\Feature\Admin\OtherPaymentGateways;

use App\Models\ModulePermission;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Other Payment Gateway');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => OtherPaymentGateway::inRandomOrder()->first()]
            ),
            ['status' => 1]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Other Payment Gateway')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => OtherPaymentGateway::inRandomOrder()->first()]
            ),
            ['status' => 1]
        );
        $response->assertForbidden();
    }

    public function test_payment_gateway_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => 0]
            ),
            ['status' => 1]
        );
        $response->assertNotFound();
    }

    public function test_missing_status()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => OtherPaymentGateway::inRandomOrder()->first()]
            )
        );
        $response->assertInvalid(['status' => 'The status field is required. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_status_is_not_boolean()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => OtherPaymentGateway::inRandomOrder()->first()]
            ),
            ['status' => 'abc']
        );
        $response->assertInvalid(['status' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $otherPaymentGateway = OtherPaymentGateway::inRandomOrder()->first();
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.active.update',
                ['other_payment_gateway' => $otherPaymentGateway]
            ),
            ['status' => true]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The payment gateway of $otherPaymentGateway->name changed to be active.",
            'status' => true,
        ]);
    }
}
