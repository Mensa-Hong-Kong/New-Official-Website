<?php

namespace Tests\Feature\Admin\SiteContents;

use App\Models\ModulePermission;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    private SiteContent $content;

    protected function setUp(): void
    {
        parent::setUp();
        $this->content = SiteContent::inRandomOrder()->first();
    }

    public function test_have_no_login(): void
    {
        $response = $this->get(
            route(
                'admin.site-contents.edit',
                ['site_content' => $this->content]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_site_content(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Site Content')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.site-contents.edit',
                    ['site_content' => $this->content]
                )
            );
        $response->assertForbidden();
    }

    public function test_site_content_is_not_exist(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Site Content');
        $response = $this->actingAs($user)->get(
            route(
                'admin.site-contents.edit',
                ['site_content' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Site Content');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.site-contents.edit',
                    ['site_content' => $this->content]
                )
            );
        $response->assertSuccessful();
    }
}
