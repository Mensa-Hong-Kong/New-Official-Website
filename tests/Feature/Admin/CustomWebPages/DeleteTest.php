<?php

namespace Tests\Feature\Admin\CustomWebPages;

use App\Models\CustomWebPage;
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
        $this->page = CustomWebPage::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->deleteJson(
            route(
                'admin.custom-web-pages.destroy',
                ['custom_web_page' => $this->page]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Web Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.custom-web-pages.destroy',
                ['custom_web_page' => $this->page]
            ),
        );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Web Page');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.custom-web-pages.destroy',
                ['custom_web_page' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Web Page');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.custom-web-pages.destroy',
                ['custom_web_page' => $this->page]
            ),
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The custom web page of \"{$this->page->title}\" delete success!"]);
        $this->assertNull(CustomWebPage::find($this->page->id));
    }
}
