<?php

namespace Tests\Feature\Admin\Contacts;

use App\Library\Stripe\Events\Customer\DefaultEmail;
use App\Library\Stripe\Events\Customer\Synced;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DefaultTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['synced_to_stripe' => true]);
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->putJson(
            route(
                'admin.contacts.default',
                ['contact' => $contact]
            )
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_user_permission(): void
    {
        $contact = UserHasContact::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:User')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.contacts.default',
                ['contact' => $contact]
            )
        );
        $response->assertForbidden();
    }

    public function test_status_is_not_boolean(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => 'abc']
            );
        $response->assertInvalid(['status' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case_not_verified_contact_no_change(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertFalse($contact->isVerified);
        $this->assertFalse($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_not_verified_mobile_change_to_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->mobile()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertTrue($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_not_verified_email_change_to_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->email()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertTrue($contact->is_default);
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['default_email' => ['contact' => $contact->contact]]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }

    public function test_happy_case_verified_contact_no_change(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertFalse($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_verified_mobile_change_to_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->mobile()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertTrue($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_verified_email_change_to_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->email()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertTrue($contact->is_default);
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['default_email' => ['contact' => $contact->contact]]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }

    public function test_happy_case_default_contact_no_change(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->createQuietly(['is_default' => true]);
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ),
                ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertTrue($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_default_mobile_change_to_non_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->mobile()->createQuietly(['is_default' => true]);
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertFalse($contact->is_default);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_default_email_change_to_non_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $contact = UserHasContact::factory()->email()->createQuietly(['is_default' => true]);
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} default status update success!",
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified);
        $this->assertFalse($contact->is_default);
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['default_email' => null]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$this->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }
}
