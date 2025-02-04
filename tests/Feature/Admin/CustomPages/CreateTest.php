<?php

namespace Tests\Feature\Admin\CustomPages;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.custom-pages.create'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.custom-pages.create'));
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Page');
        $response = $this->actingAs($user)
            ->get(route('admin.custom-pages.create'));
        $response->assertSuccessful();
    }
}
