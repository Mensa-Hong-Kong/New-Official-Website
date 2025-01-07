<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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
        $contactType = Arr::random(['email', 'mobile']);
        $contact = '';
        switch ($contactType) {
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
            ), [$contactType => $contact]
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
        switch($type) {
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
                $type => $contact,
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_user_id()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch($type) {
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
                [$type => $contact]
            );
        $response->assertInvalid(['message' => 'The user field is required, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_user_id_is_not_integer()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch($type) {
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
                    $type => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'The user field must be an integer, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_user_id_is_not_exists()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch($type) {
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
                    $type => $contact,
                ]
            );
        $response->assertInvalid(['message' => 'User is ont found, may be deleted, if you are using our CMS, please refresh. If refresh is not show 404, please contact I.T. officer.']);
    }

    public function test_have_missing_contaact()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                ['user_id' => $this->user->id]
            );
        $response->assertInvalid(['message' => 'The data fields of email, mobile must have one.']);
    }

    public function test_have_more_than_one_contaact_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'email' => fake()->freeEmail(),
                    'mobile' => fake()->numberBetween(10000, 999999999999999),
                ]
            );
        $response->assertInvalid(['message' => 'The data fields of email, mobile only can have one.']);
    }

    public function test_email_invalid()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'email' => 'abc',
                ]
            );
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    }

    public function test_mobile_not_integer()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'mobile' => 'abc',
                ]
            );
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'mobile' => '1234',
                ]
            );
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    'mobile' => '1234567890123456',
                ]
            );
        $response->assertInvalid(['mobile' => 'The mobile field must not have more than 15 digits.']);
    }

    public function test_contact_exist_with_same_user()
    {
        $contact = UserHasContact::factory()
            ->{Arr::random(['email', 'mobile'])}()
            ->create();
        $response = $this->actingAs($this->user)
            ->postJson(
                route('admin.contacts.store'),
                [
                    'user_id' => $this->user->id,
                    $contact->type => $contact->contact,
                ]
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} has already been taken."]);
    }

    public function test_with_is_verified_and_is_verified_is_not_boolean()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch($type) {
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
                    $type => $contact,
                    'is_verified' => 'abc',
                ]
            );
        $response->assertInvalid(['message' => 'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_with_is_default_and_is_default_is_not_boolean()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch($type) {
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
        switch($type) {
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
                    $type => $contact,
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
        switch($type) {
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
                    $type => $contact,
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
        switch($type) {
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
                    $type => $contact,
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
