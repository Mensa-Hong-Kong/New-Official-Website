<?php

namespace Tests\Feature\Admin\Permissions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.permissions.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_is_not_admin()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('admin.permissions.index'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(route('admin.permissions.index'));
        $response->assertSuccessful();
    }
}
