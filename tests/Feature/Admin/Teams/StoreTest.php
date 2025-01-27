<?php

namespace Tests\Feature\Admin\Teams;

use App\Models\ModulePermission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
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
        $response = $this->postJson(
            route('admin.teams.store'),
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
        $response = $this->actingAs($user)->postJson(
            route('admin.teams.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 171);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 170 characters.']);
    }

    public function test_name_has_colon()
    {
        $data = $this->happyCase;
        $data['name'] = 'abc:efg';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field cannot has ":".']);
    }

    public function test_name_is_exist_for_type()
    {
        $data = $this->happyCase;
        $data['name'] = Team::where('type_id', $data['type_id'])
            ->first()->name;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name of team in this type has already been taken.']);
    }

    public function test_missing_type_id()
    {
        $data = $this->happyCase;
        unset($data['type_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['type_id' => 'The type field is required.']);
    }

    public function test_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['type_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['type_id' => 'The type field must be an integer.']);
    }

    public function test_type_id_is_not_exists()
    {
        $data = $this->happyCase;
        $data['type_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['type_id' => 'The selected type is invalid.']);
    }

    public function test_missing_display_order()
    {
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero()
    {
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_zero_max_plus_one()
    {
        $data = $this->happyCase;
        $data['display_order'] = Team::where('type_id', $data['type_id'])
            ->max('display_order') + 2;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.teams.store'),
            $this->happyCase
        );
        $team = Team::latest('id')->first();
        $response->assertRedirectToRoute('admin.teams.show', ['team' => $team]);
    }
}
