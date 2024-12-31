<?php

namespace Tests\Feature\Admin\Users;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'View:User')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));
        $response->assertSuccessful();
    }
}
