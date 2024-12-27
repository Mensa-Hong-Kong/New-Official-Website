<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized(): void
    {
        $response = $this->get(route('profile.show'));
        $response->assertRedirectToRoute('login');
    }

    public function test_happy_case(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('profile.show'));
        $response->assertOk();
    }
}
