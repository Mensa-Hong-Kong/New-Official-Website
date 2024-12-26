<?php

namespace Tests\Feature\Contact;

use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        Notification::fake();
        $this->user = User::factory()->create();
    }

    public function test_have_no_login()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->putJson(
            route(
                'contacts.update',
                ['contact' => $contact]
            ), [$contact->type => $contact->contact]
        );
        $response->assertUnauthorized();
    }

    public function test_user_contact_is_not_zirself()
    {
        $contact = UserHasContact::factory()->create();
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->patchJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $contact->contact]
            );
        $response->assertForbidden();
    }

    public function test_missing_contact()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                )
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} field is required."]);
    }

    public function test_email_invalid()
    {
        $contact = UserHasContact::factory()
            ->email()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), ['email' => 'abc']
            );
        $response->assertInvalid(['email' => 'The email field must be a valid email address']);
    }

    public function test_mobile_not_integer()
    {
        $contact = UserHasContact::factory()
            ->mobile()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => 'abc']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $contact = UserHasContact::factory()
            ->mobile()
            ->create();
        $response = $this->actingAs($this->user)
            ->patch(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => '1234']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $contact = UserHasContact::factory()
            ->mobile()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => '1234567890123456']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must not have more than 15 digits.']);
    }

    public function test_contact_exist_with_same_user()
    {
        $contact = UserHasContact::factory()
            ->create();
        $newContact = UserHasContact::factory()
            ->{$contact->type}()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $newContact->contact]
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} has already been taken."]);
    }

    public function test_happy_case_when_contact_have_no_change_and_origin_is_default_and_is_verified()
    {
        $contact = UserHasContact::factory()
            ->state(['is_default' => true])
            ->create();
        $contact->sendVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => "$contact->contact"]
            );
        $type = ucfirst($contact->type);
        $this->assertEquals(
            $contact->id,
            $this->user->{"default$type"}->id
        );
        $this->assertTrue($contact->isVerified());
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            'contact' => "$contact->contact",
            "default_{$contact->type}_id" => $contact->id,
            'is_verified' => true,
        ]);
        $this->assertNull($contact->lastVerification->expired_at);
    }

    public function test_happy_case_when_contact_has_change()
    {
        $contact = UserHasContact::factory()
            ->create();
        $contact->sendVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $newContact = '';
        switch ($contact->type) {
            case 'email':
                $newContact = fake()->freeEmail();
                break;
            case 'mobile':
                $newContact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $newContact]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            'contact' => $newContact,
            "default_{$contact->type}_id" => null,
            'is_verified' => false,
        ]);
        $this->assertNotNull($contact->lastVerification->expired_at);
    }
}
