<?php

namespace Tests\Feature\Admin\Users;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route(
            'admin.users.show',
            ['user' => User::factory()->create()]
        ));
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
            ->get(route(
                'admin.users.show',
                ['user' => $user]
            ));
        $response->assertForbidden();
    }

    public function test_not_exists_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)
            ->get(route(
                'admin.users.show',
                ['user' => 0]
            ));
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)
            ->get(route(
                'admin.users.show',
                ['user' => User::first()]
            ));
        $response->assertSuccessful();
    }
}
