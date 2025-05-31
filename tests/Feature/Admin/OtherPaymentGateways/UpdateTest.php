<?php

namespace Tests\Feature\Admin\OtherPaymentGateways;

use App\Models\ModulePermission;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
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
                'admin.other-payment-gateways.update',
                [
                    'other_payment_gateway' => OtherPaymentGateway::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
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
                'admin.other-payment-gateways.update',
                [
                    'other_payment_gateway' => OtherPaymentGateway::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
        );
        $response->assertForbidden();
    }

    public function test_permission_is_not_exist()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.other-payment-gateways.update',
                ['other_payment_gateway' => 0]
            ), ['name' => 'abc']
        );
        $response->assertNotFound();
    }

    public function test_name_is_not_string()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.update',
                [
                    'other_payment_gateway' => OtherPaymentGateway::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => ['abc']]
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.update',
                [
                    'other_payment_gateway' => OtherPaymentGateway::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => str_repeat('a', 256)]
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.other-payment-gateways.update',
                [
                    'other_payment_gateway' => OtherPaymentGateway::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The payment gateway name update success!',
            'name' => 'abc',
        ]);
    }
}
