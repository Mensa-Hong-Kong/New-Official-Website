<?php

namespace Tests\Feature\Admin\Teams\Roles;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $happyCase = [
        'name' => 'abc',
        'display_order' => 0,
    ];

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Permission');
    }

    public function test_have_no_login()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $response = $this->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => 0,
                    'role' => Role::inRandomOrder()->first(),
                ]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_role_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => Team::inRandomOrder()
                        ->whereNot('type_id', 1)
                        ->first(),
                    'role' => 0,
                ]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_missing_name()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 171);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 170 characters.']);
    }

    public function test_name_has_colon()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['name'] = 'abc:efg';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field cannot has ":".']);
    }

    public function test_name_is_exist_for_team()
    {
        $team = Team::inRandomOrder()
            ->has('roles', '>', 1)
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['name'] = $team->roles()
            ->whereNot('role_id', $role->id)
            ->inRandomOrder()
            ->first()
            ->name;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name of role in this team has already been taken.']);
    }

    public function test_missing_display_order()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_max_plus_one()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['display_order'] = TeamRole::where('team_id', $team->id)
            ->max('display_order') + 1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_module_permissions_is_not_array()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['module_permissions'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['message' => 'The permissions field must be an array, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_module_permissions_value_is_not_integer()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['module_permissions'][] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['message' => 'The value of permissions must be an integer, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_module_permissions_value_is_duplicate()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $permission = ModulePermission::inRandomOrder()->first();
        $data['module_permissions'] = [$permission->id, $permission->id];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['message' => 'The permissions has a duplicate value, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_module_permissions_value_is_exist_on_database()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $data = $this->happyCase;
        $data['module_permissions'][] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $response->assertInvalid(['message' => 'The permissions not up to date, if you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $team = Team::inRandomOrder()
            ->whereNot('type_id', 1)
            ->first();
        $role = $team->roles()->inRandomOrder()->first();
        $teamRole = TeamRole::where('team_id', $team->id)
            ->where('role_id', $role->id)
            ->first();
        $teamRole->syncPermissions([]);
        $data = $this->happyCase;
        $permission = ModulePermission::inRandomOrder()->first();
        $data['module_permissions'][] = $permission->id;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.update',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
            $data
        );
        $role->refresh();
        $teamRole->refresh();
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $this->assertEquals($data['name'], $role->name);
        $this->assertEquals($data['display_order'], $teamRole->display_order);
        $this->assertTrue($teamRole->hasPermissionTo($permission->name));
    }
}
