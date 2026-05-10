<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private array $happyCase = [
        'master_id' => 0,
        'name' => 'abc',
        'display_order' => 0,
    ];

    private User $user;

    private NavigationItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Navigation Item');
        $data = $this->happyCase;
        $data['master_id'] = null;
        $data['url'] = null;
        $this->item = NavigationItem::factory()->create($data);
    }

    public function test_have_no_login(): void
    {
        $response = $this->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_missing_master_id(): void
    {
        $data = $this->happyCase;
        unset($data['master_id']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['master_id' => 'The master field is required.']);
    }

    public function test_master_id_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['master_id'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['master_id' => 'The master field must be an integer.']);
    }

    public function test_master_id_is_invalid(): void
    {
        $data = $this->happyCase;
        $data['master_id'] = -1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['master_id' => 'The selected master is invalid.']);
    }

    public function test_missing_name(): void
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long(): void
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_url_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['url'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must be a string.']);
    }

    public function test_url_too_long(): void
    {
        $data = $this->happyCase;
        $data['url'] = str_repeat('a', 8001);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must not be greater than 8000 characters.']);
    }

    public function test_url_is_no_active_url(): void
    {
        $data = $this->happyCase;
        $data['url'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['url' => 'The url field must be a valid URL.']);
    }

    public function test_missing_display_order(): void
    {
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero(): void
    {
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_max_plus_one(): void
    {
        $data = $this->happyCase;
        $data['display_order'] = NavigationItem::whereNull('master_id')
            ->max('display_order');
        if ($data['display_order'] === null) {
            $data['display_order']++;
        } else {
            $data['display_order'] += 1;
        }
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_happy_case_with_no_change_when_have_no_url(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
    }

    public function test_happy_case_with_no_change_when_has_url(): void
    {
        $data = $this->happyCase;
        $this->item->update(['url' => 'https://google.com']);
        $data['url'] = 'https://google.com';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            $data
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
    }

    public function test_happy_case_with_changing_when_have_no_url(): void
    {
        $item = NavigationItem::factory()->create([
            'master_id' => null,
            'display_order' => 1,
        ]);
        $subItem1 = NavigationItem::factory()->create([
            'master_id' => $item->id,
            'display_order' => 0,
        ]);
        $subItem2 = NavigationItem::factory()->create([
            'master_id' => $item->id,
            'display_order' => 1,
        ]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            [
                'master_id' => $item->id,
                'name' => 'xyz',
                'display_order' => 1,
            ]
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
        $this->item->refresh();
        $this->assertEquals($item->id, $this->item->master_id);
        $this->assertEquals('xyz', $this->item->name);
        $this->assertEquals(1, $this->item->display_order);
        $this->assertEquals(0, $item->refresh()->display_order);
        $this->assertEquals(0, $subItem1->refresh()->display_order);
        $this->assertEquals(2, $subItem2->refresh()->display_order);
    }

    public function test_happy_case_with_changing_when_has_url(): void
    {
        $item = NavigationItem::factory()->create([
            'master_id' => null,
            'display_order' => 1,
        ]);
        $subItem1 = NavigationItem::factory()->create([
            'master_id' => $item->id,
            'display_order' => 0,
        ]);
        $subItem2 = NavigationItem::factory()->create([
            'master_id' => $item->id,
            'display_order' => 1,
        ]);
        $this->item->update(['url' => 'https://google.com']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.navigation-items.update',
                ['navigation_item' => $this->item]
            ),
            [
                'master_id' => $item->id,
                'name' => 'xyz',
                'display_order' => 1,
                'url' => 'https://youtube.com',
            ]
        );
        $response->assertRedirectToRoute('admin.navigation-items.index');
        $this->item->refresh();
        $this->assertEquals($item->id, $this->item->master_id);
        $this->assertEquals('xyz', $this->item->name);
        $this->assertEquals('https://youtube.com', $this->item->url);
        $this->assertEquals(1, $this->item->display_order);
        $this->assertEquals(0, $item->refresh()->display_order);
        $this->assertEquals(0, $subItem1->refresh()->display_order);
        $this->assertEquals(2, $subItem2->refresh()->display_order);
    }
}
