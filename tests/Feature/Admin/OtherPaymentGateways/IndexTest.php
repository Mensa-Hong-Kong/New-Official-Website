<?php

namespace Tests\Feature\Admin\OtherPaymentGateways;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.other-payment-gateways.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_other_payment_gateway()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Other Payment Gateway')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.other-payment-gateways.index'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Other Payment Gateway');
        $response = $this->actingAs($user)
            ->get(route('admin.other-payment-gateways.index'));
        $response->assertSuccessful();
    }
}
