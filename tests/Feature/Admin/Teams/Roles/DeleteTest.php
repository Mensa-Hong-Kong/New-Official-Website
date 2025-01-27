<?php

namespace Tests\Feature\Admin\Teams\Roles;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
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
        $team = Team::inRandomOrder()->first();
        $role = $team->roles->first();
        $response = $this->deleteJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $team = Team::inRandomOrder()->first();
        $role = $team->roles->first();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => 0,
                    'role' => Role::inRandomOrder()->first(),
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_role_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => Team::inRandomOrder()->first(),
                    'role' => 0,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_have_no_other_team()
    {
        $role = Role::has('teams', '=', 1)
            ->first();
        $team = $role->teams()->first();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The role of $role->name delete success!"]);
        $this->assertNull(Role::firstWhere('id', $role->id));
    }

    public function test_happy_case_when_has_other_teams()
    {
        $role = Role::has('teams', '>', 1)
            ->first();
        $team = $role->teams()->first();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.teams.roles.destroy',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The role of $role->name delete success!"]);
        $this->assertNotNull(Role::firstWhere('id', $role->id));
        $this->assertNull(
            TeamRole::where('team_id', $team->id)
                ->where('role_id', $role->id)
                ->first()
        );
    }
}
