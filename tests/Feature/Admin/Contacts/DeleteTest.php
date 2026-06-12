<?php

namespace Tests\Feature\Admin\Contacts;

use App\Library\Stripe\Events\Customer\DefaultEmail;
use App\Models\ModulePermission;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['synced_to_stripe' => true]);
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->deleteJson(
            route(
                'admin.contacts.destroy',
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
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.contacts.destroy',
                ['contact' => $contact]
            )
        );
        $response->assertForbidden();
    }

    public function test_happy_case_when_contact_is_not_default(): void
    {
        Event::fake(DefaultEmail::class);
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->deleteJson(
                route(
                    'admin.contacts.destroy',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$contact->type} delete success!"]);
        $this->assertNull(UserHasContact::firstWhere('id', $contact->id));
        Event::assertNotDispatched(DefaultEmail::class);
    }

    public function test_happy_case_when_mobile_is_default(): void
    {
        Event::fake(DefaultEmail::class);
        $contact = UserHasContact::factory()->mobile()->createQuietly(['is_default' => true]);
        $response = $this->actingAs($this->user)
            ->deleteJson(
                route(
                    'admin.contacts.destroy',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$contact->type} delete success!"]);
        $this->assertNull(UserHasContact::firstWhere('id', $contact->id));
        Event::assertNotDispatched(DefaultEmail::class);
    }

    public function test_happy_case_when_email_is_default(): void
    {
        Event::fake(DefaultEmail::class);
        $contact = UserHasContact::factory()->email()->createQuietly(['is_default' => true]);
        $response = $this->actingAs($this->user)
            ->deleteJson(
                route(
                    'admin.contacts.destroy',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$contact->type} delete success!"]);
        $this->assertNull(UserHasContact::firstWhere('id', $contact->id));
        $this->assertBroadcastChannel(
            DefaultEmail::class,
            'App.Models.User.'.$contact->user->id,
            PrivateChannel::class,
            ['default_email' => null]
        );
    }
}
