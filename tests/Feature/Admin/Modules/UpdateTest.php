<?php

namespace Tests\Feature\Admin\Modules;

use App\Models\Module;
use App\Models\ModulePermission;
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
                'admin.modules.update',
                [
                    'module' => Module::inRandomOrder()
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
                'admin.modules.update',
                [
                    'module' => Module::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
        );
        $response->assertForbidden();
    }

    public function test_permission_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.modules.update',
                ['module' => 0]
            ), ['name' => 'abc']
        );
        $response->assertNotFound();
    }

    public function test_name_is_not_string()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.modules.update',
                [
                    'module' => Module::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => ['abc']]
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.modules.update',
                [
                    'module' => Module::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => str_repeat('a', 256)]
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.modules.update',
                [
                    'module' => Module::inRandomOrder()
                        ->first(),
                ]
            ), ['name' => 'abc']
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The module display name update success!',
            'name' => 'abc',
        ]);
    }
}
