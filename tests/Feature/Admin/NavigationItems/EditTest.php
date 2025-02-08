<?php

namespace Tests\Feature\Admin\NavigationItems;

use App\Models\ModulePermission;
use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    private $item;

    protected function setUp(): void
    {
        parent::setup();
        $this->item = NavigationItem::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.navigation-items.edit',
                ['navigation_item' => $this->item]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_navigation_item()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Navigation Item')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.navigation-items.edit',
                    ['navigation_item' => $this->item]
                )
            );
        $response->assertForbidden();
    }

    public function test_navigation_item_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Navigation Item');
        $response = $this->actingAs($user)->get(
            route(
                'admin.navigation-items.edit',
                ['navigation_item' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Navigation Item');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.navigation-items.edit',
                    ['navigation_item' => $this->item]
                )
            );
        $response->assertSuccessful();
    }
}
