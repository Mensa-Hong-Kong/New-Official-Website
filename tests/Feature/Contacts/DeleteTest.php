<?php

namespace Tests\Feature\Contacts;

use App\Library\Stripe\Events\Customer\DefaultEmail;
use App\Library\Stripe\Events\Customer\Synced;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserHasContact $contact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['synced_to_stripe' => true]);
        $this->contact = UserHasContact::factory()->create();
    }

    public function test_have_no_login(): void
    {
        $response = $this->deleteJson(route(
            'contacts.destroy',
            ['contact' => $this->contact]
        ));
        $response->assertUnauthorized();
    }

    public function test_user_contact_is_not_zirself(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertForbidden();
    }

    public function test_happy_case_when_contact_is_not_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $response = $this->actingAs($this->user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} delete success!"]);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_when_mobile_is_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->updateQuietly([
            'type' => 'mobile',
            'is_default' => true,
        ]);
        $response = $this->actingAs($this->user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} delete success!"]);
        Event::assertNotDispatched(DefaultEmail::class);
        Event::assertNotDispatched(Synced::class);
    }

    public function test_happy_case_when_email_is_default(): void
    {
        Event::fake([
            DefaultEmail::class,
            Synced::class,
        ]);
        $this->contact->updateQuietly([
            'type' => 'email',
            'is_default' => true,
        ]);
        $response = $this->actingAs($this->user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} delete success!"]);
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$this->contact->user->id,
            PrivateChannel::class,
            ['default_email' => null]
        );
        $this->assertBroadcastChannel(
            Synced::class,
            'App.Models.User.'.$this->contact->user->id,
            PrivateChannel::class,
            ['synced_to_stripe' => false]
        );
    }
}
