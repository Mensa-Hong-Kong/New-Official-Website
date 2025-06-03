<?php

namespace Tests\Feature\Admin\OtherPaymentGateways;

use App\Models\ModulePermission;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDisplayOrderTest extends TestCase
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
            route('admin.other-payment-gateways.display-order.update'),
            [
                'display_order' => OtherPaymentGateway::inRandomOrder()
                    ->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Other Payment Gateway')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            [
                'display_order' => OtherPaymentGateway::inRandomOrder()
                    ->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_display_order()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update')
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_array()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => 'abc']
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an array.']);
    }

    public function test_display_order_size_is_not_match()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => [OtherPaymentGateway::first()->id]]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_have_no_value()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => []]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_value_is_not_integer()
    {
        $IDs = OtherPaymentGateway::inRandomOrder()
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[0] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field must be an integer.']);
    }

    public function test_display_order_value_is_duplicate()
    {
        $IDs = OtherPaymentGateway::inRandomOrder()
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[] = $IDs[0];
        OtherPaymentGateway::create(['name' => 'abc']);
        $module = OtherPaymentGateway::first();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field has a duplicate value.']);
    }

    public function test_display_order_value_is_not_exists_on_database()
    {
        $IDs = OtherPaymentGateway::inRandomOrder()
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[0] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $moduleIDs = OtherPaymentGateway::inRandomOrder()
            ->get('id')
            ->pluck('id')
            ->toArray();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.other-payment-gateways.display-order.update'),
            ['display_order' => $moduleIDs]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The display order update success!',
            'display_order' => $moduleIDs,
        ]);
    }
}
