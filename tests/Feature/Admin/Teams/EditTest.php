<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\ModulePermission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.teams.edit',
                ['team' => Team::first()]
            )
        );
        $response->assertRedirectToRoute('login');
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
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.edit',
                    ['team' => Team::first()]
                )
            );
        $response->assertForbidden();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.teams.edit',
                    ['team' => Team::first()]
                )
            );
        $response->assertSuccessful();
    }
}
