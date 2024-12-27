<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login_user(): void
    {
        $response = $this->get(route('logout'));
        $response->assertRedirectToRoute('index');
    }

    public function test_has_login_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('logout'));
        $response->assertRedirectToRoute('index');
    }
}
