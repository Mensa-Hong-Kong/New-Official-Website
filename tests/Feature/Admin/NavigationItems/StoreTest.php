<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $happyCase = [
        'master_id' => 0,
        'name' => 'abc',
        'display_order' => 0,
    ];

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Navigation Item');
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.navigation-items.store'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_missing_master_id()
    {
        $data = $this->happyCase;
        unset($data['master_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['master_id' => 'The master field is required.']);
    }

    public function test_master_id_is_not_string()
    {
        $data = $this->happyCase;
        $data['master_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['master_id' => 'The master field must be an integer.']);
    }

    public function test_master_id_is_invalid()
    {
        $data = $this->happyCase;
        $data['master_id'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['master_id' => 'The selected master is invalid.']);
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_url_is_not_string()
    {
        $data = $this->happyCase;
        $data['url'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must be a string.']);
    }

    public function test_url_too_long()
    {
        $data = $this->happyCase;
        $data['url'] = str_repeat('a', 8001);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must not be greater than 8000 characters.']);
    }

    public function test_url_is_no_active_url()
    {
        $data = $this->happyCase;
        $data['url'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must be a valid URL.']);
    }

    public function test_missing_display_order()
    {
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero()
    {
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_max_plus_one()
    {
        $data = $this->happyCase;
        $data['display_order'] = NavigationItem::whereNull('master_id')
            ->max('display_order');
        if ($data['display_order'] === null) {
            $data['display_order']++;
        } else {
            $data['display_order'] += 2;
        }
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_happy_case_when_have_no_url()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
    }

    public function test_happy_case_when_has_url()
    {
        $data = $this->happyCase;
        $data['url'] = 'https://google.com';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.navigation-items.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
    }
}
