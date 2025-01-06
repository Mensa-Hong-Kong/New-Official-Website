<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyTest extends TestCase
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
                'admin.contacts.verify',
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
                'admin.contacts.verify',
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
                    'admin.contacts.verify',
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
                    'admin.contacts.verify',
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
                    'admin.contacts.verify',
                    ['contact' => $contact]
                ), ['status' => false]
            );
            $response->assertSuccessful();
            $response->assertJson([
                'success' => 'The contact verifty status update success!',
                'status' => false,
            ]);
        $this->assertFalse($contact->isVerified());
    }

    public function test_happy_case_not_verified_contact_change_to_verified()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.verify',
                    ['contact' => $contact]
                ), ['status' => true]
            );
            $response->assertSuccessful();
            $response->assertJson([
                'success' => 'The contact verifty status update success!',
                'status' => true,
            ]);
        $this->assertTrue($contact->isVerified());
    }

    public function test_happy_case_verified_contact_no_change()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.verify',
                    ['contact' => $contact]
                ), ['status' => true]
            );
            $response->assertSuccessful();
            $response->assertJson([
                'success' => 'The contact verifty status update success!',
                'status' => true,
            ]);
        $this->assertTrue($contact->isVerified());
    }

    public function test_happy_case_verified_contact_change_to_not_verified()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.verify',
                    ['contact' => $contact]
                ), ['status' => false]
            );
            $response->assertSuccessful();
            $response->assertJson([
                'success' => 'The contact verifty status update success!',
                'status' => false,
            ]);
        $this->assertFalse($contact->isVerified());
    }
}
