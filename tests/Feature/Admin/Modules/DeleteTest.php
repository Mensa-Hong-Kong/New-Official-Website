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

    public function test_have_no_login()
    {
        $response = $this->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => NavigationItem::factory()->create()]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
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
                ['navigation_item' => NavigationItem::factory()->create()]
            ),
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
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

    public function test_happy_case()
    {
        $item1 = NavigationItem::factory()->create();
        $item1point1 = NavigationItem::factory()
            ->state(['master_id' => $item1->id])
            ->create();
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Navigation Item');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.navigation-items.destroy',
                ['navigation_item' => $item1]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The display order update success!']);
        $this->assertFalse(
            NavigationItem::where('id', $item1->id)
                ->exists()
        );
        $this->assertFalse(
            NavigationItem::where('id', $item1point1->id)
                ->exists()
        );
    }
}
