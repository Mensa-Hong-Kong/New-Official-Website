<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function testHaveNoLoginUser(): void
    {
        $response = $this->get(route('logout'));
        $response->assertRedirectToRoute('index');
    }

    public function testHasLoginUser(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('logout'));
        $response->assertRedirectToRoute('index');
    }
}
