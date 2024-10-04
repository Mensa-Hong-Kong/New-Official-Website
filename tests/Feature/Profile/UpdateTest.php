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

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->state([
            'username' => 'testing123',
            'password' => '12345678',
        ])->create();
    }

    public function testUnauthorized(): void
    {
        $response = $this->put(route('profile.update'));

        $response->assertRedirectToRoute('login');
    }

    public function testUsernameIsNotString()
    {
        $data = $this->happyCase;
        $data['username'] = ['12345678'];
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function testUsernameTooShort()
    {
        $data = $this->happyCase;
        $data['username'] = '1234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function testUsernameTooLong()
    {
        $data = $this->happyCase;
        $data['username'] = '12345678901234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function testWithChangeUsernameMissingPassword()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function testWithChangePasswordMissingPassword()
    {
        $data = $this->happyCase;
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function testPasswordIsNotString()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = ['12345678'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function testPasswordTooShort()
    {
        $data = $this->happyCase;
        $data['password'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function testPasswordTooLong()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678901234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function testNewPasswordIsNotString()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = ['12345678'];
        $data['new_password_confirmation'] = ['12345678'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be a string.']);
    }

    public function testNewPasswordTooShort()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '1234567';
        $data['new_password_confirmation'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be at least 8 characters.']);
    }

    public function testNewPasswordTooLong()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '12345678901234567';
        $data['new_password_confirmation'] = '12345678901234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must not be greater than 16 characters.']);
    }

    public function testConfirmNewPasswordNotMatch()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '87654321';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field confirmation does not match.']);
    }

    public function testMissingFamilyName()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function testFamilyNameIsNotString()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function testFamilyNameTooLong()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function testMiddleNameIsNotString()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function testMiddleNameTooLong()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function testMissingGivenName()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function testGivenNameIsNotString()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function testGivenNameTooLong()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function testMissingPassportTypeId()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function testPassportTypeIdIsNotInteger()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function testPassportTypeIdIsNotExist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function testMissingPassportNumber()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function testPassportNumberFormatNotMatch()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function testPassportNumberTooShort()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function testPassportNumberTooLong()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function testPassportNumberIsUsed()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['passport_type_id'] = $user->passport_type_id;
        $data['passport_number'] = $user->passport_number;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number has already been taken.']);
    }

    public function testMissingGender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function testGenderTooLong()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function testMissingBirthday()
    {
        $data = $this->happyCase;
        unset($data['birthday']);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function testBirthdayIsNotDate()
    {
        $data = $this->happyCase;
        $data['birthday'] = 'abc';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function testBirthdayTooClose()
    {
        $data = $this->happyCase;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function testWithoutChangeUsernameAndNewPasswordAndMiddleHappyCase()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function testWithChangeUsernameWithoutNewPasswordAndMiddleHappyCase()
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

    public function testWithNewPasswordWithoutChangeUsernameAndMiddleHappyCase()
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

    public function testWithMiddleWithoutChangeUsernameAndNewPasswordHappyCase()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertValid();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation', 'gender_id'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $response->assertJson($expect);
    }

    public function testWithChangeUsernameAndNewPasswordWithoutMiddleHappyCase()
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

    public function testWithChangeUsernameAndMiddleWithoutNewPasswordHappyCase()
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

    public function testWithNewPasswordAndMiddleWithoutChangeUsernameHappyCase()
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

    public function testWithChangeUsernameAndNewPasswordAndMiddleHappyCase()
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
