<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'username' => 'testing123',
        'family_name' => 'Chan',
        'given_name' => 'Diamond',
        'passport_type_id' => 2,
        'passport_number' => 'A1234567',
        'gender' => 'Male',
        'birthday' => '1997-07-01',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->state([
            'username' => 'testing123',
            'password' => '12345678',
        ])->create();
    }

    public function test_unauthorized(): void
    {
        $response = $this->put(route('profile.update'));

        $response->assertRedirectToRoute('login');
    }

    public function test_username_is_not_string()
    {
        $data = $this->happyCase;
        $data['username'] = ['12345678'];
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function test_username_too_short()
    {
        $data = $this->happyCase;
        $data['username'] = '1234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function test_username_too_long()
    {
        $data = $this->happyCase;
        $data['username'] = '12345678901234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function test_with_change_username_missing_password()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function test_with_change_password_missing_password()
    {
        $data = $this->happyCase;
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function test_password_is_not_string()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = ['12345678'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function test_password_too_short()
    {
        $data = $this->happyCase;
        $data['password'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_password_too_long()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678901234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function test_new_password_is_not_string()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = ['12345678'];
        $data['new_password_confirmation'] = ['12345678'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be a string.']);
    }

    public function test_new_password_too_short()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '1234567';
        $data['new_password_confirmation'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be at least 8 characters.']);
    }

    public function test_new_password_too_long()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '12345678901234567';
        $data['new_password_confirmation'] = '12345678901234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must not be greater than 16 characters.']);
    }

    public function test_confirm_new_password_not_match()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '87654321';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field confirmation does not match.']);
    }

    public function test_missing_family_name()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function test_family_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function test_family_name_too_long()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function test_middle_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function test_middle_name_too_long()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function test_missing_given_name()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function test_given_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function test_given_name_too_long()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function test_missing_passport_type_id()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function test_passport_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function test_missing_passport_number()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_not_match()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function test_passport_number_too_short()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_passport_number_is_used()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['passport_type_id'] = $user->passport_type_id;
        $data['passport_number'] = $user->passport_number;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number has already been taken.']);
    }

    public function test_missing_gender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function test_gender_too_long()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function test_missing_birthday()
    {
        $data = $this->happyCase;
        unset($data['birthday']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function test_birthday_is_not_date()
    {
        $data = $this->happyCase;
        $data['birthday'] = 'abc';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function test_birthday_too_close()
    {
        $data = $this->happyCase;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function test_without_change_username_and_new_password_and_middle_happy_case()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_change_username_without_new_password_and_middle_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_new_password_without_change_username_and_middle_happy_case()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_middle_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_change_username_and_new_password_without_middle_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_change_username_and_middle_without_new_password_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $data['middle_name'] = 'Tai Man';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_new_password_and_middle_without_change_username_happy_case()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $data['middle_name'] = 'Tai Man';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function test_with_change_username_and_new_password_and_middle_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $data['middle_name'] = 'Tai Man';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }
}
