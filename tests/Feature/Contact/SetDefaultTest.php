<?php

namespace Tests\Feature\Contact;

use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SetDefaultTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $contact;

    protected function setUp(): void
    {
        parent::setup();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
        $this->contact->sendVerifyCode();
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(route(
            'contacts.default', ['contact' => $this->contact]
        ));
        $response->assertUnauthorized();
    }

    public function test_user_contact_is_not_zirself()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->patchJson(route(
                'contacts.default', ['contact' => $this->contact]
            ));
        $response->assertForbidden();
    }

    public function test_the_contact_is_not_verified()
    {
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.default', ['contact' => $this->contact]
            ));
        $response->assertStatus(428);
        $response->assertJson(['message' => "The {$this->contact->type} is not verified, cannot set this contact to default, please verify first."]);
    }

    public function test_the_contact_already_is_default()
    {
        $this->contact->lastVerification->update(['verified_at' => now()]);
        $this->contact->update(['is_default' => true]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.default', ['contact' => $this->contact]
            ));
        $response->assertCreated();
    }

    public function test_happy_case_user_have_no_default_contact()
    {
        $this->contact->lastVerification->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue((bool) $this->contact->fresh()->is_default);
    }

    public function test_happy_case_user_has_default_contact()
    {
        $this->contact->lastVerification->update(['verified_at' => now()]);
        $contact = UserHasContact::factory()
            ->{$this->contact->type}()
            ->state(['is_default' => true])
            ->create();
        $contact->sendVerifyCode();
        $contact->lastVerification->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue((bool) $this->contact->fresh()->is_default);
        $this->assertFalse((bool) $contact->fresh()->is_default);
    }
}
