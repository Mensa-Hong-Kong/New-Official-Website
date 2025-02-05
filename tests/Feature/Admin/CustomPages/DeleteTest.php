<?php

namespace Tests\Feature\Admin\CustomPages;

use App\Models\CustomPage;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private $page;

    protected function setUp(): void
    {
        parent::setup();
        $this->page = CustomPage::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->deleteJson(
            route(
                'admin.custom-pages.destroy',
                ['custom_page' => $this->page]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.custom-pages.destroy',
                ['custom_page' => $this->page]
            ),
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Page');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.custom-pages.destroy',
                ['custom_page' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Page');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.custom-pages.destroy',
                ['custom_page' => $this->page]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The custom page of \"{$this->page->title}\" delete success!"]);
        $this->assertNull(CustomPage::find($this->page->id));
    }
}
