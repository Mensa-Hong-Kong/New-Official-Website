<?php

namespace Tests\Feature\Admin\CustomWebPages;

use App\Models\CustomWebPage;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
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
        $response = $this->get(
            route(
                'admin.custom-web-pages.edit',
                ['custom_web_page' => $this->page]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_custom_web_page()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Web Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.custom-web-pages.edit',
                    ['custom_web_page' => $this->page]
                )
            );
        $response->assertForbidden();
    }

    public function test_custom_web_page_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Web Page');
        $response = $this->actingAs($user)->get(
            route(
                'admin.custom-web-pages.edit',
                ['custom_web_page' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Web Page');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.custom-web-pages.edit',
                    ['custom_web_page' => $this->page]
                )
            );
        $response->assertSuccessful();
    }
}
