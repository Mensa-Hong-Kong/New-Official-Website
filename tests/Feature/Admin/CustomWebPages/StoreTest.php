<?php

namespace Tests\Feature\Admin\CustomWebPages;

use App\Models\CustomWebPage;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

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
        $this->user->givePermissionTo('Edit:Custom Web Page');
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.custom-web-pages.store'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_custom_web_page_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Custom Web Page')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->postJson(
            route('admin.custom-web-pages.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_pathname()
    {
        $data = $this->happyCase;
        unset($data['pathname']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field is required.']);
    }

    public function test_pathname_is_not_string()
    {
        $data = $this->happyCase;
        $data['pathname'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must be a string.']);
    }

    public function test_pathname_too_long()
    {
        $data = $this->happyCase;
        $data['pathname'] = str_repeat('a', 769);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must not be greater than 768 characters.']);
    }

    public function test_pathname_format_not_match()
    {
        $data = $this->happyCase;
        $data['pathname'] = 'abc\\xyz';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname field must only contain letters, numbers, dashes and slash.']);
    }

    public function test_pathname_is_exist()
    {
        CustomWebPage::factory()
            ->state(['pathname' => $this->happyCase['pathname']])
            ->create();
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['pathname' => 'The pathname has already been taken.']);
    }

    public function test_missing_title()
    {
        $data = $this->happyCase;
        unset($data['title']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['title' => 'The title field is required.']);
    }

    public function test_title_is_not_string()
    {
        $data = $this->happyCase;
        $data['title'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['title' => 'The title field must be a string.']);
    }

    public function test_title_too_long()
    {
        $data = $this->happyCase;
        $data['title'] = str_repeat('a', 61);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['title' => 'The title field must not be greater than 60 characters.']);
    }

    public function test_open_graph_image_url_is_not_string()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field must be a string.']);
    }

    public function test_open_graph_image_url_too_long()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = str_repeat('a', 8001);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field must not be greater than 8000 characters.']);
    }

    public function test_open_graph_image_url_is_not_a_valid()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['og_image_url' => 'The open graph image url field is not a valid URL.']);
    }

    public function test_missing_description()
    {
        $data = $this->happyCase;
        unset($data['description']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['description' => 'The description field is required.']);
    }

    public function test_description_is_not_string()
    {
        $data = $this->happyCase;
        $data['description'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['description' => 'The description field must be a string.']);
    }

    public function test_description_too_long()
    {
        $data = $this->happyCase;
        $data['description'] = str_repeat('a', 66);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['description' => 'The description field must not be greater than 65 characters.']);
    }

    public function test_content_is_not_string()
    {
        $data = $this->happyCase;
        $data['content'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['content' => 'The content field must be a string.']);
    }

    public function test_content_too_long()
    {
        $data = $this->happyCase;
        $data['content'] = str_repeat('a', 4194304);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertInvalid(['content' => 'The content field must not be greater than 4194303 characters.']);
    }

    public function test_happy_case_when_have_no_og_image_url()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.custom-web-pages.index');
    }

    public function test_happy_case_when_has_og_image_url()
    {
        $data = $this->happyCase;
        $data['og_image_url'] = 'https://google.com';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.custom-web-pages.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.custom-web-pages.index');
    }
}
