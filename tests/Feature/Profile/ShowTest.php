<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function testUnauthorized(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirectToRoute('login');
    }

    public function testHappyCase(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
    }
}
