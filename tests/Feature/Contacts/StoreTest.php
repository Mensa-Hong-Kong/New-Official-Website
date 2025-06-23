<?php

namespace Tests\Feature\Contacts;

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
                'contacts.store',
            ), [
                'type' => $type,
                'contact' => $contact,
            ]
        );
        $response->assertUnauthorized();
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
                route('contacts.store'),
                ['contact' => $contact]
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
                route('contacts.store'),
                [
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
                route('contacts.store'),
                [
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
                route('contacts.store'),
                ['type' => $type]
            );
        $response->assertInvalid(['contact' => "The contact of $type is required."]);
    }

    public function test_email_invalid()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                [
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
                route('contacts.store'),
                [
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
                route('contacts.store'),
                [
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
                route('contacts.store'),
                [
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
                route('contacts.store'),
                [
                    'type' => $contact->type,
                    'contact' => $contact->contact,
                ]
            );
        $response->assertInvalid(['contact' => "The contact of {$contact->type} has already been taken."]);
    }

    public function test_happy_case()
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
                route('contacts.store'),
                [
                    'type' => $type,
                    'contact' => $contact,
                ]
            );
        $response->assertSuccessful();
        $contactModel = UserHasContact::first();
        $this->assertEquals($type, $contactModel->type);
        $this->assertEquals($contact, $contactModel->contact);
        $this->assertEquals($this->user->id, $contactModel->user_id);
        $response->assertJson([
            'success' => "The $type create success!",
            'id' => $contactModel->id,
            'contact' => $contact,
        ]);
    }
}
