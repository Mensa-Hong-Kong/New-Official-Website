<?php

namespace Tests\Feature\Admin\Users;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'username' => '87654321',
        'family_name' => 'LEE',
        'given_name' => 'Chi Nan',
        'passport_type_id' => 2,
        'passport_number' => 'C668668E',
        'gender' => 'Female',
        'birthday' => '2003-09-15',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => '12345678',
            'password' => '12345678',
            'family_name' => 'Chan',
            'given_name' => 'Diamond',
            'passport_type_id' => 2,
            'passport_number' => 'A1234567',
            'gender' => 'Male',
            'birthday' => '1997-07-01',
        ]);
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.users.update',
                ['user' => $this->user]
            ), $this->happyCase
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
        $response = $this->actingAs($user)
            ->patchJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $this->happyCase
            );
        $response->assertForbidden();
    }

    public function test_not_exists_user()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => 0]
                ), $this->happyCase
            );
        $response->assertNotFound();
    }

    public function test_missing_username()
    {
        $data = $this->happyCase;
        unset($data['username']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['username' => 'The username field is required.']);
    }

    public function test_username_is_not_string()
    {
        $data = $this->happyCase;
        $data['username'] = ['12345678'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function test_username_too_short()
    {
        $data = $this->happyCase;
        $data['username'] = '1234567';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function test_username_too_long()
    {
        $data = $this->happyCase;
        $data['username'] = '12345678901234567';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function test_username_is_used()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['username'] = $user->username;
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['username' => 'The username has already been taken.']);
    }
    public function test_missing_family_name()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function test_family_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function test_family_name_too_long()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function test_middle_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function test_middle_name_too_long()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function test_missing_given_name()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function test_given_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function test_given_name_too_long()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function test_missing_passport_type_id()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function test_passport_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function test_missing_passport_number()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_not_match()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function test_passport_number_too_short()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_missing_gender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function test_gender_too_long()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function test_missing_birthday()
    {
        $data = $this->happyCase;
        unset($data['birthday']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function test_birthday_is_not_date()
    {
        $data = $this->happyCase;
        $data['birthday'] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function test_birthday_too_close()
    {
        $data = $this->happyCase;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function test_user_has_contact_and_missing_contacts()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $this->happyCase
            );
        $response->assertInvalid([
            'message' => "The {$contact->type}s field is required. If you are using our CMS, plesae contact I.T. officer.",
            "{$contact->type}s.{$contact->id}.contact" => "The {$contact->type} field is required.",
        ]);
    }

    public function test_user_contacts_is_not_an_array()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "message" => "The {$contact->type}s field must be an array. If you are using our CMS, plesae contact I.T. officer.",
            "{$contact->type}s.{$contact->id}.contact" => "The {$contact->type} field is required.",
        ]);
    }

    public function test_user_missing_contact_of_contacts()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"] = [];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "{$contact->type}s.{$contact->id}.contact" => "The {$contact->type} field is required.",
        ]);
    }

    public function test_email_invalid()
    {
        $contact = UserHasContact::factory()->email()->create();
        $data = $this->happyCase;
        $data["emails"][$contact->id]['contact'] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "emails.{$contact->id}.contact" => 'The email field must be a valid email address.',
        ]);
    }

    public function test_mobile_not_integer()
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $data = $this->happyCase;
        $data["mobiles"][$contact->id]['contact'] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "mobiles.{$contact->id}.contact" => 'The mobile field must be an integer.'
        ]);
    }

    public function test_mobile_too_short()
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $data = $this->happyCase;
        $data["mobiles"][$contact->id]['contact'] = '1234';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "mobiles.{$contact->id}.contact" => 'The mobile field must have at least 5 digits.'
        ]);
    }

    public function test_mobile_too_long()
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $data = $this->happyCase;
        $data["mobiles"][$contact->id]['contact'] = '1234567890123456';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "mobiles.{$contact->id}.contact" => 'The mobile field must not have more than 15 digits.'
        ]);
    }

    public function test_contact_is_default_is_not_boolean()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"][$contact->id] = [
            'contact' => $contact->contact,
            'is_default' => 'abc',
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "message" => "The {$contact->type} defaule field must be true or false. If you are using our CMS, plesae contact I.T. officer."
        ]);
    }

    public function test_contact_is_verified_is_not_boolean()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"][$contact->id] = [
            'contact' => $contact->contact,
            'is_verified' => 'abc',
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertInvalid([
            "message" => "The {$contact->type} verified field must be true or false. If you are using our CMS, plesae contact I.T. officer."
        ]);
    }

    public function test_happy_case_without_middle_name_and_contact()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $response->assertJson($data);
        $user = User::firstWhere('id', $this->user->id);
        $this->assertEquals($data['username'], $user->username);
        $this->assertEquals($data['username'], $user->username);
        $this->assertEquals($data['family_name'], $user->family_name);
        $this->assertEmpty($user->middle_name);
        $this->assertEquals($data['given_name'], $user->given_name);
        $this->assertEquals($data['passport_type_id'], $user->passport_type_id);
        $this->assertEquals($data['passport_number'], $user->passport_number);
        $this->assertEquals($data['gender'], $user->gender->name);
        $this->assertEquals($data['birthday'], $user->birthday);
    }

    public function test_happy_case_with_middle_name_and_without_contact()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'intelligent';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $response->assertJson($data);
        $user = User::firstWhere('id', $this->user->id);
        $this->assertEquals($data['username'], $user->username);
        $this->assertEquals($data['username'], $user->username);
        $this->assertEquals($data['family_name'], $user->family_name);
        $this->assertEquals($data['middle_name'], $user->middle_name);
        $this->assertEquals($data['given_name'], $user->given_name);
        $this->assertEquals($data['passport_type_id'], $user->passport_type_id);
        $this->assertEquals($data['passport_number'], $user->passport_number);
        $this->assertEquals($data['gender'], $user->gender->name);
        $this->assertEquals($data['birthday'], $user->birthday);
    }

    public function test_happy_case_with_non_verified_email_and_email_only_chanage_contact()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gamil.com'])
            ->create();
        $data = $this->happyCase;
        $data["emails"][$email->id]['contact'] = 'example@live.hk';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = false;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk',$email->contact);
        $this->assertFalse($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_non_verified_mobile_and_mobile_only_chanage_contact()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state(['contact' => '12345678'])
            ->create();
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id]['contact'] = '87654321';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = false;
        $data["mobiles"][$mobile->id]['is_default'] = false;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertFalse($mobile->isVerified());
        $this->assertFalse((bool) $mobile->is_default);
    }

    public function test_happy_case_with_non_verified_contact_and_contact_only_chanage_verified()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"][$contact->id] = [
                'contact' => $contact->contact,
                'is_verified' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["{$contact->type}s"][$contact->id]['is_verified'] = true;
        $data["{$contact->type}s"][$contact->id]['is_default'] = false;
        $response->assertJson($data);
        $contact = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals(
            $data["{$contact->type}s"][$contact->id]['contact'],
            $contact->contact
        );
        $this->assertTrue($contact->isVerified());
        $this->assertFalse((bool) $contact->is_default);
    }

    public function test_happy_case_with_non_verified_contact_and_contact_only_chanage_is_default()
    {
        $contact = UserHasContact::factory()->create();
        $data = $this->happyCase;
        $data["{$contact->type}s"] = [
            $contact->id => [
                'contact' => $contact->contact,
                'is_default' => true,
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data["{$contact->type}s"][$contact->id]['is_verified'] = true;
        $data["{$contact->type}s"][$contact->id]['is_default'] = true;
        $data['success'] = 'The user data update success!';
        $response->assertJson($data);
        $contact = UserHasContact::firstWhere('id', $contact->id);
        $this->assertTrue($contact->isVerified());
        $this->assertTrue((bool) $contact->is_default);
    }

    public function test_happy_case_with_non_verified_email_and_email_only_chanage_contact_and_is_verified()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gmail'])
            ->create();
        $data = $this->happyCase;
        $data["emails"] = [
            $email->id => [
                'contact' => 'example@live.hk',
                'is_verified' => true,
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_non_verified_email_and_email_only_chanage_contact_and_is_default()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gamil.com'])
            ->create();
        $data = $this->happyCase;
        $data["emails"] = [
            $email->id => [
                'contact' => 'example@live.hk',
                'is_default' => true,
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = true;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertTrue((bool) $email->is_default);
    }

    public function test_happy_case_with_verified_email_and_email_only_chanage_contact()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gamil.com'])
            ->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id] = [
            'contact' => 'example@live.hk',
            'is_verified' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_verified_mobile_and_mobile_only_chanage_contact()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state(['contact' => '12345678'])
            ->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id] = [
            'contact' => '87654321',
            'is_verified' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = true;
        $data["mobiles"][$mobile->id]['is_default'] = false;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321',$mobile->contact);
        $this->assertTrue($mobile->isVerified());
        $this->assertFalse((bool) $mobile->is_default);
    }

    public function test_happy_case_with_verified_contact_and_contact_only_chanage_verified()
    {
        $contact = UserHasContact::factory()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["{$contact->type}s"][$contact->id] = [
            'contact' => $contact->contact,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["{$contact->type}s"][$contact->id]['is_verified'] = false;
        $data["{$contact->type}s"][$contact->id]['is_default'] = false;
        $response->assertJson($data);
        $contact = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals(
            $data["{$contact->type}s"][$contact->id]['contact'],
            $contact->contact
        );
        $this->assertFalse($contact->isVerified());
        $this->assertFalse((bool) $contact->is_default);
    }

    public function test_happy_case_with_verified_contact_and_contact_only_chanage_is_default()
    {
        $contact = UserHasContact::factory()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["{$contact->type}s"] = [
            $contact->id => [
                'contact' => $contact->contact,
                'is_default' => true,
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["{$contact->type}s"][$contact->id]['is_verified'] = true;
        $data["{$contact->type}s"][$contact->id]['is_default'] = true;
        $response->assertJson($data);
        $contact = UserHasContact::firstWhere('id', $contact->id);
        $this->assertTrue($contact->isVerified());
        $this->assertTrue((bool) $contact->is_default);
    }

    public function test_happy_case_with_verified_email_and_email_only_chanage_contact_and_is_verified()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gmail.com'])
            ->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"] = [
            $email->id => [
                'contact' => 'example@live.hk',
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = false;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertFalse($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_verified_email_and_email_only_chanage_contact_and_is_default()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['contact' => 'example@gamil.com'])
            ->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id] = [
            'contact' => 'example@live.hk',
            'is_default' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = true;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertTrue((bool) $email->is_default);
    }

    public function test_happy_case_with_verified_mobile_and_mobile_only_chanage_contact_and_is_verified()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state(['contact' => '12345678'])
            ->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id]['contact'] = '87654321';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = false;
        $data["mobiles"][$mobile->id]['is_default'] = false;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertFalse($mobile->isVerified());
        $this->assertFalse((bool) $mobile->is_default);
    }

    public function test_happy_case_with_verified_mobile_and_mobile_only_chanage_contact_and_is_default()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state(['contact' => '12345678'])
            ->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id] = [
            'contact' => '87654321',
            'is_default' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = true;
        $data["mobiles"][$mobile->id]['is_default'] = true;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified());
        $this->assertTrue((bool) $mobile->is_default);
    }

    public function test_happy_case_with_default_email_and_email_only_chanage_contact()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state([
                'contact' => 'example@gamil.com',
                'is_default' => true
            ])->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id] = [
            'contact' => 'example@live.hk',
            'is_default' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = true;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertTrue((bool) $email->is_default);
    }

    public function test_happy_case_with_default_mobile_and_mobile_only_chanage_contact()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state([
                'contact' => '12345678',
                'is_default' => true
            ])->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id] = [
            'contact' => '87654321',
            'is_default' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = true;
        $data["mobiles"][$mobile->id]['is_default'] = true;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321',$mobile->contact);
        $this->assertTrue($mobile->isVerified());
        $this->assertTrue((bool) $mobile->is_default);
    }

    public function test_happy_case_with_default_contact_and_contact_only_chanage_is_not_default()
    {
        $contact = UserHasContact::factory()
            ->state(['is_default' => true])
            ->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["{$contact->type}s"] = [
            $contact->id => [
                'contact' => $contact->contact,
                'is_verified' => true,
            ],
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["{$contact->type}s"][$contact->id]['is_verified'] = true;
        $data["{$contact->type}s"][$contact->id]['is_default'] = false;
        $response->assertJson($data);
        $contact = UserHasContact::firstWhere('id', $contact->id);
        $this->assertTrue($contact->isVerified());
        $this->assertFalse((bool) $contact->is_default);
    }

    public function test_happy_case_with_default_contact_and_contact_only_is_not_verified_and_is_not_default()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state(['is_default' => true])
            ->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id] = [
            'contact' => $email->contact,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = false;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertFalse($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_default_email_and_email_only_chanage_contact_and_is_not_default()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state([
                'is_default' => true,
                'contact' => 'example@gamil.com',
            ])->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id] = [
            'contact' => 'example@live.hk',
            'is_verified' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = true;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertTrue($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_default_mobile_and_mobile_only_chanage_contact_and_is_not_default()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state([
                'is_default' => true,
                'contact' => '12345678'
            ])
            ->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id] = [
            'contact' => '87564321',
            'is_verified' => true,
        ];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = true;
        $data["mobiles"][$mobile->id]['is_default'] = false;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87564321', $mobile->contact);
        $this->assertTrue($mobile->isVerified());
        $this->assertFalse((bool) $mobile->is_default);
    }

    public function test_happy_case_with_default_email_and_email_change_all()
    {
        $email = UserHasContact::factory()
            ->email()
            ->state([
                'is_default' => true,
                'contact' => 'example@gamil.com',
            ])->create();
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["emails"][$email->id]['contact'] = 'example@live.hk';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["emails"][$email->id]['is_verified'] = false;
        $data["emails"][$email->id]['is_default'] = false;
        $response->assertJson($data);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.hk', $email->contact);
        $this->assertFalse($email->isVerified());
        $this->assertFalse((bool) $email->is_default);
    }

    public function test_happy_case_with_default_mobile_and_mobile_chanage_all()
    {
        $mobile = UserHasContact::factory()
            ->mobile()
            ->state([
                'is_default' => true,
                'contact' => '12345678'
            ])
            ->create();
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $data = $this->happyCase;
        $data["mobiles"][$mobile->id]['contact'] = '87564321';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.update',
                    ['user' => $this->user]
                ), $data
            );
        $response->assertSuccessful();
        $data['success'] = 'The user data update success!';
        $data["mobiles"][$mobile->id]['is_verified'] = false;
        $data["mobiles"][$mobile->id]['is_default'] = false;
        $response->assertJson($data);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87564321', $mobile->contact);
        $this->assertFalse($mobile->isVerified());
        $this->assertFalse((bool) $mobile->is_default);
    }
}
