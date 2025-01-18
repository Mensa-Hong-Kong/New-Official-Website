<?php

namespace Tests\Feature\Admin\Teams\Roles;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDisplayOrderTest extends TestCase
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
        $response = $this->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            [
                'display_order' => TeamRole::inRandomOrder()
                    ->where('team_id', $team->id)
                    ->get('display_order')
                    ->pluck('display_order')
                    ->toArray(),
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $team = Team::inRandomOrder()->first();
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            [
                'display_order' => TeamRole::inRandomOrder()
                    ->where('team_id', $team->id)
                    ->get('display_order')
                    ->pluck('display_order')
                    ->toArray(),
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_display_order()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => Team::inRandomOrder()->first()]
            )
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_array()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            ['display_order' => 'abc']
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an array.']);
    }

    public function test_display_order_size_is_not_match()
    {
        $team = Team::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            [
                'display_order' => [
                    TeamRole::where('team_id', $team->id)
                        ->first()
                        ->role_id,
                ],
            ]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_have_no_value()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            ['display_order' => []]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_value_is_not_integer()
    {
        $team = Team::inRandomOrder()->first();
        $IDs = TeamRole::inRandomOrder()
            ->where('team_id', $team->id)
            ->get('role_id')
            ->pluck('role_id')
            ->toArray();
        $IDs[0] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field must be an integer.']);
    }

    public function test_display_order_value_is_duplicate()
    {
        $team = Team::inRandomOrder()->first();
        $IDs = TeamRole::inRandomOrder()
            ->where('team_id', $team->id)
            ->get('role_id')
            ->pluck('role_id')
            ->toArray();
        $IDs[] = $IDs[0];
        $role = Role::create(['name' => 'abc']);
        TeamRole::create([
            'name' => "{$team->name}:{$role->name}",
            'team_id' => $team->id,
            'role_id' => $role->id,
        ]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field has a duplicate value.']);
    }

    public function test_display_order_value_is_exists_on_database()
    {
        $team = Team::inRandomOrder()->first();
        $IDs = TeamRole::inRandomOrder()
            ->where('team_id', $team->id)
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[0] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            ['display_order' => $IDs]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $team = Team::inRandomOrder()->first();
        $IDs = TeamRole::inRandomOrder()
            ->where('team_id', $team->id)
            ->get('role_id')
            ->pluck('role_id')
            ->toArray();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.roles.display-order.update',
                ['team' => $team]
            ),
            ['display_order' => $IDs]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The display order update success!',
            'display_order' => $IDs,
        ]);
    }
}
