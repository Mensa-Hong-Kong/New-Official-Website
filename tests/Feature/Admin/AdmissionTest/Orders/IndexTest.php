<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.admission-test.orders.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_view_and_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNotIn('name', ['View:Admission Test Order', 'Edit:Admission Test Order'])
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.orders.index'));
        $response->assertForbidden();
    }

    public function test_happy_case_when_user_only_has_view_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:Admission Test Order');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.orders.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Order');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.orders.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_have_view_and_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['View:Admission Test Order', 'Edit:Admission Test Order']);
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.orders.index'));
        $response->assertSuccessful();
    }
}
