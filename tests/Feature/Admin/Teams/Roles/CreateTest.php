<?php

namespace Tests\Feature\Admin\Teams\Roles;

use App\Models\ModulePermission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $team = Team::first();
        $response = $this->get(
            route(
                'admin.teams.roles.create',
                ['team' => $team]
            )

        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $team = Team::first();
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
                    'admin.teams.roles.create',
                    ['team' => $team]
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
                    'admin.teams.roles.create',
                    ['team' => 0]
                )
            );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $team = Team::first();
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.roles.create',
                    ['team' => $team]
                )
            );
        $response->assertSuccessful();
    }
}
