<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\ModulePermission;
use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDisplayOrderTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Navigation Item');
        $item1 = NavigationItem::factory()->create();
        $item1point1 = NavigationItem::factory()
            ->state([
                'master_id' => $item1->id,
                'display_order' => 0,
            ])->create();
        $item2 = NavigationItem::factory()
            ->state([
                'master_id' => null,
                'display_order' => 1,
            ])->create();
        $this->happyCase = [
            'display_order' => [
                0 => [$item1->id, $item2->id],
                $item1->id => [$item1point1->id],
            ],
        ];
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route('admin.navigation-items.display-order.update'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Navigation Item')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_display_order()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update')
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_array()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            ['display_order' => 'abc']
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an array.']);
    }

    public function test_display_order_value_is_not_array()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            ['display_order' => ['abc']]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field must be an array.']);
    }

    public function test_display_order_have_no_value()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            ['display_order' => []]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_sub_array_have_no_value()
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            ['display_order' => [[]]]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field is required.']);
    }

    public function test_display_order_array_key_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order']['abc'] = $data['display_order'][0];
        unset($data['display_order'][0]);
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The array key of display_order field must be an integer.']);
    }

    public function test_display_order_array_key_is_not_item_id_or_0()
    {
        $data = $this->happyCase;
        $data['display_order'][-1] = $data['display_order'][0];
        unset($data['display_order'][0]);
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['message' => 'The master ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_sub_array_value_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order'][0][0] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['display_order.0.0' => 'The display_order.0.0 field must be an integer.']);
    }

    public function test_display_order_total_sub_array_value_no_match_database()
    {
        $data = $this->happyCase;
        unset($data['display_order'][0][0]);
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_value_is_duplicate()
    {
        $data = $this->happyCase;
        $data['display_order'][0][0] = $data['display_order'][0][1];
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field has a duplicate value. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_value_is_not_exist_item_id()
    {
        $data = $this->happyCase;
        $data['display_order'][0][0] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case_with_no_change()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $this->happyCase
        );
        $response->assertSuccessful();
        $data['success'] = 'The display order update success!';
        $response->assertJson($data);
    }

    public function test_happy_case_change_all()
    {
        $item1ID = $this->happyCase['display_order'][0][0];
        $item2ID = $this->happyCase['display_order'][0][1];
        $item1point1ID = $this->happyCase['display_order'][$item1ID][0];
        $data = [
            'display_order' => [
                0 => [$item1point1ID, $item1ID],
                $item1point1ID => [$item2ID],
            ],
        ];
        $response = $this->actingAs($this->user)->putJson(
            route('admin.navigation-items.display-order.update'),
            $data
        );
        $response->assertSuccessful();
        $data['success'] = 'The display order update success!';
        $response->assertJson($data);
    }
}
