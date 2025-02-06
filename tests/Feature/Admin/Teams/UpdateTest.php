<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\ModulePermission;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\TeamType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'name' => 'abc',
        'type_id' => 1,
        'display_order' => 0,
    ];

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
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $this->happyCase
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
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)->get(
            route(
                'admin.teams.update',
                ['team' => 0]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                [
                    'team' => Team::inRandomOrder()->first(),
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 171);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                [
                    'team' => Team::inRandomOrder()->first(),
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 170 characters.']);
    }

    public function test_name_has_colon()
    {
        $data = $this->happyCase;
        $data['name'] = 'abc:efg';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field cannot has ":".']);
    }

    public function test_name_is_exist_for_type()
    {
        $data = $this->happyCase;
        $team = Team::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $data['name'] = Team::where('type_id', $data['type_id'])
            ->whereNot('id', $team->id)
            ->whereNot('name', $team->name)
            ->first()
            ->name;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name of team in this type has already been taken.']);
    }

    public function test_missing_type_id()
    {
        $data = $this->happyCase;
        unset($data['type_id']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['type_id' => 'The type field is required.']);
    }

    public function test_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['type_id'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['type_id' => 'The type field must be an integer.']);
    }

    public function test_type_id_is_not_exists()
    {
        $data = $this->happyCase;
        $data['type_id'] = '0';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['type_id' => 'The selected type is invalid.']);
    }

    public function test_missing_display_order()
    {
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero()
    {
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_max_plus_one()
    {
        $data = $this->happyCase;
        $data['display_order'] = Team::where('type_id', $data['type_id'])
            ->max('display_order') + 1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => Team::inRandomOrder()->first()]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_happy_case_when_nothing_in_change()
    {
        $team = Team::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => $team->name,
                'type_id' => $team->type_id,
                'display_order' => $team->display_order,
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals($team->name, $newTeam->name);
        $this->assertEquals($team->type_id, $newTeam->type_id);
        $this->assertEquals($team->display_order, $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$team->type->name}:{$team->name}:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }

    public function test_happy_case_when_only_change_name()
    {
        $team = Team::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => 'abc',
                'type_id' => $team->type_id,
                'display_order' => $team->display_order,
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals('abc', $newTeam->name);
        $this->assertEquals($team->type_id, $newTeam->type_id);
        $this->assertEquals($team->display_order, $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$team->type->name}:abc:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }

    public function test_happy_case_when_only_change_display_order()
    {
        $team = Team::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $teams = Team::where('type_id', $team->type_id)
            ->inRandomOrder()
            ->get();
        $displayOrders = [];
        foreach ($teams as $index => $team) {
            $displayOrders[] = $index;
            $team->update(['display_order' => $index]);
        }
        $team->refresh();
        unset($displayOrders[$team->display_order]);
        shuffle($displayOrders);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => $team->name,
                'type_id' => $team->type_id,
                'display_order' => $displayOrders[0],
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals($team->name, $newTeam->name);
        $this->assertEquals($team->type_id, $newTeam->type_id);
        $this->assertEquals($displayOrders[0], $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$team->type->name}:{$team->name}:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }

    public function test_happy_case_when_only_have_no_change_name()
    {
        $team = Team::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $newType = TeamType::inRandomOrder()
            ->whereNotIn('id', [$team->type_id, 1])
            ->first();
        $teams = Team::where('type_id', $newType->id)
            ->inRandomOrder()
            ->get();
        $displayOrders = [];
        foreach ($teams as $index => $team) {
            $displayOrders[] = $index;
            $team->update(['display_order' => $index]);
        }
        $team->refresh();
        unset($displayOrders[$team->display_order]);
        shuffle($displayOrders);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => $team->name,
                'type_id' => $newType->id,
                'display_order' => $displayOrders[0],
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals($team->name, $newTeam->name);
        $this->assertEquals($newType->id, $newTeam->type_id);
        $this->assertEquals($displayOrders[0], $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$newType->name}:{$team->name}:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }

    public function test_happy_case_when_only_have_no_change_type()
    {
        $team = Team::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $teams = Team::where('type_id', $team->type_id)
            ->inRandomOrder()
            ->get();
        $displayOrders = [];
        foreach ($teams as $index => $team) {
            $displayOrders[] = $index;
            $team->update(['display_order' => $index]);
        }
        $team->refresh();
        unset($displayOrders[$team->display_order]);
        shuffle($displayOrders);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => 'abc',
                'type_id' => $team->type_id,
                'display_order' => $displayOrders[0],
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals('abc', $newTeam->name);
        $this->assertEquals($team->type_id, $newTeam->type_id);
        $this->assertEquals($displayOrders[0], $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$team->type->name}:abc:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }

    public function test_happy_case_when_change_all()
    {
        $team = Team::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $newType = TeamType::inRandomOrder()
            ->whereNotIn('id', [$team->type_id, 1])
            ->first();
        $teams = Team::where('type_id', $newType->id)
            ->inRandomOrder()
            ->get();
        $displayOrders = [];
        foreach ($teams as $index => $team) {
            $displayOrders[] = $index;
            $team->update(['display_order' => $index]);
        }
        $team->refresh();
        unset($displayOrders[$team->display_order]);
        shuffle($displayOrders);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.teams.update',
                ['team' => $team]
            ),
            [
                'name' => 'abc',
                'type_id' => $newType->id,
                'display_order' => $displayOrders[0],
            ]
        );
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
        $newTeam = Team::find($team->id);
        $newTeamRoles = TeamRole::where('team_id', $team->id)
            ->get('name')
            ->pluck('name')
            ->toArray();
        $this->assertEquals('abc', $newTeam->name);
        $this->assertEquals($newType->id, $newTeam->type_id);
        $this->assertEquals($displayOrders[0], $newTeam->display_order);
        $expectedTeamRoles = [];
        foreach ($team->roles as $role) {
            $expectedTeamRoles[] = "{$newType->name}:abc:{$role->name}";
        }
        $this->assertEquals($expectedTeamRoles, $newTeamRoles);
    }
}
