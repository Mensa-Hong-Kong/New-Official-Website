<?php

namespace Tests\Feature\Admin\CustomPages;

use App\Models\CustomPage;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $page;

    private $happyCase = [
        'pathname' => 'abc/EFG-123',
        'title' => 'abc',
        'description' => 'abc',
        'content' => 'xyz',
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Custom Page');
        $this->page = CustomPage::factory()->state(['og_image_url' => null])->create();
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_custom_page_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_custom_page_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => 0]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_missing_pathname()
    {
        $data = $this->happyCase;
        unset($data['pathname']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field is required.']);
    }

    public function test_pathname_is_not_string()
    {
        $data = $this->happyCase;
        $data['pathname'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must be a string.']);
    }

    public function test_pathname_too_long()
    {
        $data = $this->happyCase;
        $data['pathname'] = str_repeat('a', 769);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must not be greater than 768 characters.']);
    }

    public function test_pathname_format_not_match()
    {
        $data = $this->happyCase;
        $data['pathname'] = 'abc\\xyz';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must only contain letters, numbers, dashes and slash.']);
    }

    public function test_pathname_is_exist()
    {
        CustomPage::factory()
            ->state(['pathname' => $this->happyCase['pathname']])
            ->create();
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname has already been taken.']);
    }

    public function test_missing_title()
    {
        $data = $this->happyCase;
        unset($data['title']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['title' => 'The title field is required.']);
    }

    public function test_title_is_not_string()
    {
        $data = $this->happyCase;
        $data['title'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['title' => 'The title field must be a string.']);
    }

    public function test_title_too_long()
    {
        $data = $this->happyCase;
        $data['title'] = str_repeat('a', 61);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['title' => 'The title field must not be greater than 60 characters.']);
    }

    public function test_open_graph_image_url_is_not_string()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field must be a string.']);
    }

    public function test_open_graph_image_url_too_long()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = str_repeat('a', 8001);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field must not be greater than 8000 characters.']);
    }

    public function test_open_graph_image_url_is_not_a_valid()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field is not a valid URL.']);
    }

    public function test_missing_description()
    {
        $data = $this->happyCase;
        unset($data['description']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['description' => 'The description field is required.']);
    }

    public function test_description_is_not_string()
    {
        $data = $this->happyCase;
        $data['description'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['description' => 'The description field must be a string.']);
    }

    public function test_description_too_long()
    {
        $data = $this->happyCase;
        $data['description'] = str_repeat('a', 66);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['description' => 'The description field must not be greater than 65 characters.']);
    }

    public function test_content_is_not_string()
    {
        $data = $this->happyCase;
        $data['content'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['content' => 'The content field must be a string.']);
    }

    public function test_content_too_long()
    {
        $data = $this->happyCase;
        $data['content'] = str_repeat('a', 4194304);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertInvalid(['content' => 'The content field must not be greater than 4194303 characters.']);
    }

    public function test_happy_case_with_no_change_when_have_no_og_image_url()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            [
                'pathname' => $this->page->pathname,
                'title' => $this->page->title,
                'description' => $this->page->description,
                'content' => $this->page->content,
            ]
        );
        $response->assertRedirectToRoute('admin.custom-pages.index');
        $page = CustomPage::find($this->page->id);
        $this->assertEquals($this->page->pathname, $page->pathname);
        $this->assertEquals($this->page->title, $page->title);
        $this->assertNull($page->og_image_url);
        $this->assertEquals($this->page->description, $page->description);
        $this->assertEquals($this->page->content, $page->content);
    }

    public function test_happy_case_with_no_change_when_has_og_image_url()
    {
        $this->page->update(['og_image_url' => 'https://yahoo.com']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            [
                'pathname' => $this->page->pathname,
                'title' => $this->page->title,
                'og_image_url' => $this->page->og_image_url,
                'description' => $this->page->description,
                'content' => $this->page->content,
            ]
        );
        $response->assertRedirectToRoute('admin.custom-pages.index');
        $page = CustomPage::find($this->page->id);
        $this->assertEquals($this->page->pathname, $page->pathname);
        $this->assertEquals($this->page->title, $page->title);
        $this->assertEquals($this->page->og_image_url, $page->og_image_url);
        $this->assertEquals($this->page->description, $page->description);
        $this->assertEquals($this->page->content, $page->content);
    }

    public function test_happy_case_with_changing_when_have_no_og_image_url()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.custom-pages.index');
        $page = CustomPage::find($this->page->id);
        $this->assertEquals(strtolower($this->happyCase['pathname']), $page->pathname);
        $this->assertEquals($this->happyCase['title'], $page->title);
        $this->assertNull($page->og_image_url);
        $this->assertEquals($this->happyCase['description'], $page->description);
        $this->assertEquals($this->happyCase['content'], $page->content);
    }

    public function test_happy_case_with_changing_when_has_og_image_url()
    {
        $this->page->update(['og_image_url' => 'https://yahoo.com']);
        $data = $this->happyCase;
        $data['og_image_url'] = 'https://google.com';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.custom-pages.update',
                ['custom_page' => $this->page]
            ),
            $data
        );
        $response->assertRedirectToRoute('admin.custom-pages.index');
        $page = CustomPage::find($this->page->id);
        $this->assertEquals(strtolower($data['pathname']), $page->pathname);
        $this->assertEquals($data['title'], $page->title);
        $this->assertEquals($data['og_image_url'], $page->og_image_url);
        $this->assertEquals($data['description'], $page->description);
        $this->assertEquals($data['content'], $page->content);
    }
}
