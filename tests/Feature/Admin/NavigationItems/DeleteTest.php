<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\ModulePermission;
use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private NavigationItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Navigation Item');
        $this->item = NavigationItem::factory()->create([
            'master_id' => null,
            'name' => 'abc',
            'display_order' => 0,
        ]);
    }

    public function test_have_no_login(): void
    {
        $response = $this->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => $this->item]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_navigation_item_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Navigation Item')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => $this->item]
            ),
        );
        $response->assertForbidden();
    }

    public function test_item_is_not_exist(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Navigation Item');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_have_no_children(): void
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => $this->item]
            )
        );
        $response->assertJson(['success' => 'The display order update success!']);
        $this->assertFalse(NavigationItem::where('id', $this->item->id)->exists());
    }

    public function test_happy_case_when_has_children(): void
    {
        $subItem = NavigationItem::factory()->create([
            'master_id' => $this->item->id,
        ]);
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => $this->item]
            )
        );
        $response->assertJson(['success' => 'The display order update success!']);
        $this->assertFalse(NavigationItem::where('id', $this->item->id)->exists());
        $this->assertFalse(NavigationItem::where('id', $subItem->id)->exists());
    }
}
