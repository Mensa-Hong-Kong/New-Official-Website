<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
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
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->postJson(
            route(
                'admin.contacts.store',
            ), [
                'user_id' => $this->user->id,
                'type' => $type,
                'contact' => $contact,
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:User')
                ->first()
                ->name
        );
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($user)->postJson(
            route('admin.contacts.store'),
            [
                'user_id' => $this->user->id,
                'type' => $type,
                'contact' => $contact,
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_user_id()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'type' => $type,
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The user field is required, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_user_id_is_not_integer()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => 'abc',
                    'type' => $type,
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The user field must be an integer, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_user_id_is_not_exists()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => 0,
                    'type' => $type,
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'User is ont found, may be deleted, if you are using our CMS, please refresh. Than, if refresh is not show 404, please contact I.T. officer.']);
    }

    public function test_missing_type()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The type field is required, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_type_is_not_string()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => [$type],
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The type field must be a string, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_type_is_not_in_list()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => 'abc',
                    'contact' => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The selected type is invalid, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_missing_contact()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                ]
            );
        $response->assertInvalid(['contact' => "The contact of $type is required."]);
    }

    public function test_email_invalid()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => 'email',
                    'contact' => 'abc',
                ]
            );
        $response->assertInvalid(['contact' => 'The contact of email must be a valid email address.']);
    }

    public function test_mobile_not_integer()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => 'mobile',
                    'contact' => 'abc',
                ]
            );
        $response->assertInvalid(['contact' => 'The contact of mobile must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => 'mobile',
                    'contact' => '1234',
                ]
            );
        $response->assertInvalid(['contact' => 'The contact of mobile must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => 'mobile',
                    'contact' => '1234567890123456',
                ]
            );
        $response->assertInvalid(['contact' => 'The contact of mobile must not have more than 15 digits.']);
    }

    public function test_contact_exist_with_same_user()
    {
        $contact = UserHasContact::factory()
            ->{fake()->randomElement(['email', 'mobile'])}()
            ->create();
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $contact->type,
                    'contact' => $contact->contact,
                ]
            );
        $response->assertInvalid(['contact' => "The contact of {$contact->type} has already been taken."]);
    }

    public function test_with_is_verified_and_is_verified_is_not_boolean()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                    'contact' => $contact,
                    'is_verified' => 'abc',
                ]
            );
        $response->assertInvalid(['message' => 'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_with_is_default_and_is_default_is_not_boolean()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                    'contact' => $contact,
                    $type => $contact,
                    'is_default' => 'abc',
                ]
            );
        $response->assertInvalid(['message' => 'The default field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case_for_new_not_verified_contact()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                    'contact' => $contact,
                ]
            );
        $response->assertSuccessful();
        $contactModel = UserHasContact::first();
        $this->assertEquals($type, $contactModel->type);
        $this->assertEquals($contact, $contactModel->contact);
        $this->assertEquals($this->user->id, $contactModel->user_id);
        $this->assertFalse($contactModel->isVerified());
        $this->assertFalse($contactModel->is_default);
        $response->assertJson([
            'success' => "The $type create success!",
            'id' => $contactModel->id,
            'type' => $type,
            'contact' => $contact,
            'is_verified' => false,
            'is_default' => false,
            'verify_url' => route('admin.contacts.verify', ['contact' => $contactModel]),
            'default_url' => route('admin.contacts.default', ['contact' => $contactModel]),
            'update_url' => route('admin.contacts.update', ['contact' => $contactModel]),
            'delete_url' => route('admin.contacts.destroy', ['contact' => $contactModel]),
        ]);
    }

    public function test_happy_case_for_new_verified_contact()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                    'contact' => $contact,
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $contactModel = UserHasContact::first();
        $this->assertEquals($type, $contactModel->type);
        $this->assertEquals($contact, $contactModel->contact);
        $this->assertEquals($this->user->id, $contactModel->user_id);
        $this->assertTrue($contactModel->isVerified());
        $this->assertFalse($contactModel->is_default);
        $response->assertJson([
            'success' => "The $type create success!",
            'id' => $contactModel->id,
            'type' => $type,
            'contact' => $contact,
            'is_verified' => true,
            'is_default' => false,
            'verify_url' => route('admin.contacts.verify', ['contact' => $contactModel]),
            'default_url' => route('admin.contacts.default', ['contact' => $contactModel]),
            'update_url' => route('admin.contacts.update', ['contact' => $contactModel]),
            'delete_url' => route('admin.contacts.destroy', ['contact' => $contactModel]),
        ]);
    }

    public function test_happy_case_for_new_default_contact()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'type' => $type,
                    'contact' => $contact,
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $contactModel = UserHasContact::first();
        $this->assertEquals($type, $contactModel->type);
        $this->assertEquals($contact, $contactModel->contact);
        $this->assertEquals($this->user->id, $contactModel->user_id);
        $this->assertTrue($contactModel->isVerified());
        $this->assertTrue($contactModel->is_default);
        $response->assertJson([
            'success' => "The $type create success!",
            'id' => $contactModel->id,
            'type' => $type,
            'contact' => $contact,
            'is_verified' => true,
            'is_default' => true,
            'verify_url' => route('admin.contacts.verify', ['contact' => $contactModel]),
            'default_url' => route('admin.contacts.default', ['contact' => $contactModel]),
            'update_url' => route('admin.contacts.update', ['contact' => $contactModel]),
            'delete_url' => route('admin.contacts.destroy', ['contact' => $contactModel]),
        ]);
    }
}
