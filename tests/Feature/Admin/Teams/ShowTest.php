<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.teams.show',
                ['team' => Team::first()]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_is_not_admin()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.show',
                    ['team' => Team::first()]
                )
            );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)->get(
            route(
                'admin.teams.show',
                ['team' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.show',
                    ['team' => Team::first()]
                )
            );
        $response->assertSuccessful();
    }
}
