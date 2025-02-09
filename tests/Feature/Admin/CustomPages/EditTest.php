<?php

namespace Tests\Feature\Admin\CustomPages;

use App\Models\CustomPage;
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
        $this->page = CustomPage::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.custom-pages.edit',
                ['custom_page' => $this->page]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_custom_page()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.custom-pages.edit',
                    ['custom_page' => $this->page]
                )
            );
        $response->assertForbidden();
    }

    public function test_custom_page_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Page');
        $response = $this->actingAs($user)->get(
            route(
                'admin.custom-pages.edit',
                ['custom_page' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Custom Page');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.custom-pages.edit',
                    ['custom_page' => $this->page]
                )
            );
        $response->assertSuccessful();
    }
}
