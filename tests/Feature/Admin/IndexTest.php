<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\ModulePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_is_not_admin()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('admin.index'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()->first()->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.index'));
        $response->assertSuccessful();
    }
}
