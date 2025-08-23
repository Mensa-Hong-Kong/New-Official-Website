<?php

namespace Tests\Feature\Admin\Users;

use App\Models\Gender;
use App\Models\ModulePermission;
use App\Models\User;
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
            'gender_id' => Gender::firstOrCreate(['name' => 'Male'])->id,
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

    public function test_happy_case_without_middle_name()
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
        $this->assertEquals($data['birthday'], $user->birthday->format('Y-m-d'));
    }

    public function test_happy_case_with_middle_name()
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
        $this->assertEquals($data['birthday'], $user->birthday->format('Y-m-d'));
    }
}
