<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.navigation-items.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Navigation Item')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.navigation-items.index'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Navigation Item');
        $response = $this->actingAs($user)
            ->get(route('admin.navigation-items.index'));
        $response->assertSuccessful();
    }
}
