<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatedStripeUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized(): void
    {
        $response = $this->getJson(route('profile.created-stripe-user'));
        $response->assertUnauthorized();
    }

    public function test_uncreated_stripe_user()
    {
        $user = User::factory()->state(['stripe_id' => null])->create();
        $response = $this->actingAs($user)->get(route('profile.created-stripe-user'));
        $response->assertOk();
        $response->assertJson(['status' => false]);
    }

    public function test_created_stripe_user()
    {
        $user = User::factory()->state(['stripe_id' => 'cus_NeGfPRiPKxeBi1'])->create();
        $response = $this->actingAs($user)->get(route('profile.created-stripe-user'));
        $response->assertOk();
        $response->assertJson(['status' => true]);
    }
}
