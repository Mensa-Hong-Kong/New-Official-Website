<?php

namespace Tests\Feature\Admin\SiteContents;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login(): void
    {
        $response = $this->get(route('admin.site-contents.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_site_content_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Site Content')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.site-contents.index'));
        $response->assertForbidden();
    }

    public function test_happy_case(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Site Content');
        $response = $this->actingAs($user)
            ->get(route('admin.site-contents.index'));
        $response->assertSuccessful();
    }
}
