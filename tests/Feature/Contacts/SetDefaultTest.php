<?php

namespace Tests\Feature\Contacts;

use App\Library\Stripe\Events\Customer\DefaultEmail;
use App\Library\Stripe\Events\Customer\Synced;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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
        $this->user = User::factory()->create(['synced_to_stripe' => true]);
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

    public function test_mobile_happy_case_user_have_no_default_contact(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->update(['type' => 'mobile']);
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue($this->contact->refresh()->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_email_happy_case_user_have_no_default_contact(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->update(['type' => 'email']);
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->patchJson(route(
                'contacts.set-default', ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} changed to default!"]);
        $this->assertTrue($this->contact->refresh()->is_default);
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$this->contact->user->id,
            PrivateChannel::class,
            ['default_email' => ['contact' => $this->contact->contact]]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$this->contact->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }

    public function test_mobile_happy_case_user_has_default_contact(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->update(['type' => 'mobile']);
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $contact = UserHasContact::factory()->mobile()->createQuietly(['is_default' => true]);
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
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_email_happy_case_user_has_default_contact(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->update(['type' => 'email']);
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $contact = UserHasContact::factory()->email()->createQuietly(['is_default' => true]);
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
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$contact->user->id,
            PrivateChannel::class,
            ['default_email' => ['contact' => $this->contact->contact]]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$contact->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }
}
