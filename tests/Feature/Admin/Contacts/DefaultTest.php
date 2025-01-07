<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->putJson(
            route(
                'admin.contacts.default',
                ['contact' => $contact]
            ), ['status' => true]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_user_permission()
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
            ), ['status' => true]
        );
        $response->assertForbidden();
    }

    public function test_missing_status()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                )
            );
        $response->assertInvalid(['status' => 'The status field is required. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_status_is_not_boolean()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => 'abc']
            );
        $response->assertInvalid(['status' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case_not_verified_contact_no_change()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => false]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertFalse($contact->isVerified());
        $this->assertFalse($contact->is_default);
    }

    public function test_happy_case_not_verified_contact_change_to_default()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified());
        $this->assertTrue($contact->is_default);
    }

    public function test_happy_case_verified_contact_no_change()
    {
        $contact = UserHasContact::factory()
            ->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => false]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified());
        $this->assertFalse($contact->is_default);
    }

    public function test_happy_case_verified_contact_change_to_default()
    {
        $contact = UserHasContact::factory()
            ->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified());
        $this->assertTrue($contact->is_default);
    }

    public function test_happy_case_default_contact_no_change()
    {
        $contact = UserHasContact::factory()
            ->state(['is_default' => true])
            ->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => true]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => true,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified());
        $this->assertTrue($contact->is_default);
    }

    public function test_happy_case_default_contact_change_to_non_default()
    {
        $contact = UserHasContact::factory()
            ->state(['is_default' => true])
            ->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.default',
                    ['contact' => $contact]
                ), ['status' => false]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The contact default status update success!',
            'status' => false,
        ]);
        $contact->refresh();
        $this->assertTrue($contact->isVerified());
        $this->assertFalse($contact->is_default);
    }
}
