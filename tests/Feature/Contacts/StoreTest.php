<?php

namespace Tests\Feature\Contacts;

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
                'contacts.store',
            ), [$contactType => $contact]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_contaact_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('contacts.store'));
        $response->assertInvalid(['message' => 'The data fields of email, mobile must have one.']);
    }

    public function test_have_more_than_one_contaact_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                [
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
                route('contacts.store'),
                ['email' => 'abc']
            );
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    }

    public function test_mobile_not_integer()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                ['mobile' => 'abc']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                ['mobile' => '1234']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                ['mobile' => '1234567890123456']
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
                route('contacts.store'),
                [$contact->type => $contact->contact]
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} has already been taken."]);
    }

    public function test_happy_case()
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
        $response = $this->actingAs($this->user)
            ->postJson(
                route('contacts.store'),
                [$contactType => $contact]
            );
        $response->assertSuccessful();
        $contactModel = UserHasContact::first();
        $this->assertEquals($contactType, $contactModel->type);
        $this->assertEquals($contact, $contactModel->contact);
        $this->assertEquals($this->user->id, $contactModel->user_id);
        $response->assertJson([
            'success' => "The $contactType create success!",
            'id' => $contactModel->id,
            'type' => $contactType,
            'contact' => $contact,
            'send_verify_code_url' => route('contacts.send-verify-code', ['contact' => $contactModel]),
            'verify_url' => route('contacts.verify', ['contact' => $contactModel]),
            'set_default_url' => route('contacts.set-default', ['contact' => $contactModel]),
            'update_url' => route('contacts.update', ['contact' => $contactModel]),
            'delete_url' => route('contacts.destroy', ['contact' => $contactModel]),
        ]);
    }
}
