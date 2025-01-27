<?php

namespace Tests\Feature\Admin\Teams\Roles;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $team = Team::first();
        $role = $team->roles()->first();
        $response = $this->get(
            route(
                'admin.teams.roles.edit',
                [
                    'team' => $team,
                    'role' => $role,
                ]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $team = Team::first();
        $role = $team->roles()->first();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.roles.edit',
                    [
                        'team' => $team,
                        'role' => $role,
                    ]
                )
            );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.roles.edit',
                    [
                        'team' => 0,
                        'role' => Role::first(),
                    ]
                )
            );
        $response->assertNotFound();
    }

    public function test_role_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.roles.edit',
                    [
                        'team' => Team::first(),
                        'role' => 0,
                    ]
                )
            );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $team = Team::first();
        $role = $team->roles()->first();
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.roles.edit',
                    [
                        'team' => $team,
                        'role' => $role,
                    ]
                )
            );
        $response->assertSuccessful();
    }
}
