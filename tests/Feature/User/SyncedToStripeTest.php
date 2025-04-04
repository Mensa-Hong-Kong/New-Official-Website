<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncedToStripeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized(): void
    {
        $response = $this->getJson(route('profile.synced-to-stripe'));
        $response->assertUnauthorized();
    }

    public function test_unsynced_to_stripe_user()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        $response = $this->actingAs($user)->get(route('profile.synced-to-stripe'));
        $response->assertOk();
        $response->assertJson(['status' => false]);
    }

    public function test_synced_to_stripe_user()
    {
        $user = User::factory()->state(['synced_to_stripe' => true])->create();
        $response = $this->actingAs($user)->get(route('profile.synced-to-stripe'));
        $response->assertOk();
        $response->assertJson(['status' => true]);
    }
}
