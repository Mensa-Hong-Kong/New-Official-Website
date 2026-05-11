<?php

namespace Tests\Feature\Contacts;

use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SetDefaultTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserHasContact $contact;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
        $this->contact->sendVerifyCode();
    }

    public function test_have_no_login(): void
    {
        $response = $this->putJson(route(
            'contacts.set-default', ['contact' => $this->contact]
        ));
        $response->assertUnauthorized();
    }

    public function test_user_contact_is_not_zirself(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertForbidden();
    }

    public function test_the_contact_is_not_verified(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertStatus(428);
        $response->assertJson(['message' => "The {$this->contact->type} is not verified, cannot set this contact to default, please verify first."]);
    }

    public function test_the_contact_already_is_default(): void
    {
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $this->contact->update(['is_default' => true]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertCreated();
    }

    public function test_happy_case_user_have_no_default_contact(): void
    {
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue($this->contact->refresh()->is_default);
    }

    public function test_happy_case_user_has_default_contact(): void
    {
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $contact = UserHasContact::factory()
            ->{$this->contact->type}()
            ->create(['is_default' => true]);
        $contact->sendVerifyCode();
        $contact->lastVerification->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue($this->contact->refresh()->is_default);
        $this->assertFalse($contact->refresh()->is_default);
    }
}
