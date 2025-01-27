<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\Team;
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
        $response = $this->deleteJson(
            route(
                'admin.teams.destroy',
                ['team' => Team::inRandomOrder()->first()]
            ),
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
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.teams.destroy',
                ['team' => Team::inRandomOrder()->first()]
            ),
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.destroy',
                ['team' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_have_no_unnecessary_role()
    {
        $team = Team::whereDoesntHave(
            'roles', function ($query) {
                $query->withCount('teams')
                    ->having('teams_count', '=', '1');
            }
        )->has('roles')
            ->first();
        $roles = $team->roles;
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.teams.destroy',
                ['team' => $team]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The team of $team->name delete success!"]);
        $this->assertEquals(
            $roles->count(),
            Role::whereIn('id', $roles->pluck('id')
                ->toArray())
                ->count()
        );
    }

    public function test_happy_case_when_has_unnecessary_roles()
    {
        $team = Team::whereHas(
            'roles', function ($query) {
                $query->withCount('teams')
                    ->having('teams_count', '>', '1');
            }
        )->whereHas(
            'roles', function ($query) {
                $query->withCount('teams')
                    ->having('teams_count', '=', '1');
            }
        )->first();
        $roles = $team->roles;
        $totalUsingRoles = 0;
        foreach ($roles as $role) {
            if ($role->teams()->count() > 1) {
                $totalUsingRoles++;
            }
        }
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.teams.destroy',
                ['team' => $team]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The team of $team->name delete success!"]);
        $this->assertEquals(
            $totalUsingRoles,
            Role::whereIn('id', $roles->pluck('id')
                ->toArray())
                ->count()
        );
    }
}
