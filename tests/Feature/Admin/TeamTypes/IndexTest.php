<?php

namespace Tests\Feature\Admin\TeamTypes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login(): void
    {
        $response = $this->get(route('admin.team-types.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_is_not_admin(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('admin.team-types.index'));
        $response->assertForbidden();
    }

    public function test_happy_case(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Permission');
        $response = $this->actingAs($user)
            ->get(route('admin.team-types.index'));
        $response->assertSuccessful();
    }
}
