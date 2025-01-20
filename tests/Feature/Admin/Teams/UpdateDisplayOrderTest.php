<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\ModulePermission;
use App\Models\Team;
use App\Models\TeamType;
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
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $response = $this->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => Team::inRandomOrder()
                    ->where('type_id', $type->id)
                    ->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_permission()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Permission')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => Team::inRandomOrder()
                    ->where('type_id', $type->id)
                    ->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_type_id()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'display_order' => Team::inRandomOrder()
                    ->where(
                        'type_id', TeamType::inRandomOrder()
                            ->whereNot('id', 1)
                            ->first()
                            ->id
                    )->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertInvalid(['message' => 'The type field is required, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_type_id_is_not_integer()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => 'abc',
                'display_order' => Team::inRandomOrder()
                    ->where(
                        'type_id', TeamType::inRandomOrder()
                            ->whereNot('id', 1)
                            ->first()
                            ->id
                    )->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertInvalid(['message' => 'The type field must be an integer, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_type_id_is_not_exists()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => 0,
                'display_order' => Team::inRandomOrder()
                    ->where(
                        'type_id', TeamType::inRandomOrder()
                            ->whereNot('id', 1)
                            ->first()
                            ->id
                    )->get('id')
                    ->pluck('id')
                    ->toArray(),
            ]
        );
        $response->assertInvalid(['message' => 'The selected type is invalid, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_missing_display_order()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            ['type_id' => $type->id]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_array()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => 'abc',
            ]
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an array.']);
    }

    public function test_display_order_size_is_not_match()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => [Team::first()->id],
            ]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_have_no_value()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => [],
            ]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_value_is_not_integer()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $IDs = Team::inRandomOrder()
            ->where('type_id', $type->id)
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[0] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => $IDs,
            ]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field must be an integer.']);
    }

    public function test_display_order_value_is_duplicate()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $IDs = Team::inRandomOrder()
            ->where('type_id', $type->id)
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[] = $IDs[0];
        Team::create([
            'name' => 'abc',
            'type_id' => $type->id,
        ]);
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => $IDs,
            ]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field has a duplicate value.']);
    }

    public function test_display_order_value_is_exists_on_database()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $IDs = Team::inRandomOrder()
            ->where('type_id', $type->id)
            ->get('id')
            ->pluck('id')
            ->toArray();
        $IDs[0] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => $IDs,
            ]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $type = TeamType::inRandomOrder()
            ->whereNot('id', 1)
            ->first();
        $teamIDs = Team::inRandomOrder()
            ->where('type_id', $type->id)
            ->get('id')
            ->pluck('id')
            ->toArray();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.teams.display-order.update'),
            [
                'type_id' => $type->id,
                'display_order' => $teamIDs,
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The display order update success!',
            'display_order' => $teamIDs,
        ]);
    }
}
