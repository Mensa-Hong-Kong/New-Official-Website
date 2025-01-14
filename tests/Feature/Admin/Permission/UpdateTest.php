<?php

namespace Tests\Feature\Admin\Permission;

use App\Models\ModulePermission;
use App\Models\Permission;
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
        $this->user->givePermissionTo('Edit:Permission');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.permissions.update',
                [
                    'permission' => Permission::inRandomOrder()
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
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.permissions.update',
                [
                    'permission' => Permission::inRandomOrder()
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
                'admin.permissions.update',
                ['permission' => 0]
            ), ['name' => 'abc']
        );
        $response->assertNotFound();
    }

    public function test_name_is_not_string()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.permissions.update',
                [
                    'permission' => Permission::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => ['abc']]
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_has_colon()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.permissions.update',
                [
                    'permission' => Permission::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc:efg']
        );
        $response->assertInvalid(['name' => 'The name field cannot has ";".']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.permissions.update',
                [
                    'permission' => Permission::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The permission display name update success!',
            'name' => 'abc',
        ]);
    }
}
