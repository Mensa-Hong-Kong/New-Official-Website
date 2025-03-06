<?php

namespace Tests\Feature\Admin\SiteContents;

use App\Models\ModulePermission;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = ['content' => 'abc'];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Site Content');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.site-contents.update',
                ['site_content' => SiteContent::inRandomOrder()->first()]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_site_content_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Site Content')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.site-contents.update',
                ['site_content' => SiteContent::inRandomOrder()->first()]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_content_is_not_string()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.site-contents.update',
                ['site_content' => SiteContent::inRandomOrder()->first()]
            ),
            ['content' => ['abc']]
        );
        $response->assertInvalid(['content' => 'The content field must be a string.']);
    }

    public function test_content_too_long()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.site-contents.update',
                ['site_content' => SiteContent::inRandomOrder()->first()]
            ),
            ['content' => str_repeat('a', 65536)]
        );
        $response->assertInvalid(['content' => 'The content field must not be greater than 65535 characters.']);
    }

    public function test_happy_case()
    {
        $content = SiteContent::inRandomOrder()->first();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.site-contents.update',
                ['site_content' => $content]
            ),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.site-contents.index');
        $content->refresh();
        $this->assertEquals($this->happyCase['content'], $content->content);
    }
}
