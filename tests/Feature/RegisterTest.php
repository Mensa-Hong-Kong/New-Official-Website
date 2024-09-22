<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private $happyCase = [
        'username' => '12345678',
        'password' => '12345678',
        'password_confirmation' => '12345678',
        'family_name' => 'Chan',
        'given_name' => 'Diamond',
        'passport_type_id' => 2,
        'passport_number' => 'A1234567',
        'gender' => 'Male',
        'birthday' => '1997-07-01',
    ];

    public function testView(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('register'));
        $response->assertRedirectToRoute('index');
    }

    public function testHasBeenLoginSubmitRegister(): void
    {
        $data = $this->happyCase;
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('register'), $data);
        $response->assertRedirectToRoute('index');
    }

    public function testMissingUsername()
    {
        $data = $this->happyCase;
        unset($data['username']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field is required.']);
    }

    public function testUsernameIsNotString()
    {
        $data = $this->happyCase;
        $data['username'] = ['12345678'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function testUsernameTooShort()
    {
        $data = $this->happyCase;
        $data['username'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function testUsernameTooLong()
    {
        $data = $this->happyCase;
        $data['username'] = '12345678901234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function testUsernameIsUsed()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['username'] = $user->username;
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username has already been taken.']);
    }

    public function testMissingPassword()
    {
        $data = $this->happyCase;
        unset($data['password']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field is required.']);
    }

    public function testPasswordIsNotString()
    {
        $data = $this->happyCase;
        $data['password'] = ['12345678'];
        $data['password_confirmation'] = ['12345678'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function testPasswordTooShort()
    {
        $data = $this->happyCase;
        $data['password'] = '1234567';
        $data['password_confirmation'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function testPasswordTooLong()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678901234567';
        $data['password_confirmation'] = '12345678901234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function testConfirmPasswordNotMatch()
    {
        $data = $this->happyCase;
        $data['password_confirmation'] = '87654321';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field confirmation does not match.']);
    }

    public function testMissingFamilyName()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function testFamilyNameIsNotString()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function testFamilyNameTooLong()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function testMiddleNameIsNotString()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function testMiddleNameTooLong()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function testMissingGivenName()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function testGivenNameIsNotString()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function testGivenNameTooLong()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function testMissingPassportTypeId()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function testPassportTypeIdIsNotInteger()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function testPassportTypeIdIsNotExist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function testMissingPassportNumber()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function testPassportNumberFormatNotMatch()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function testPassportNumberTooShort()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function testPassportNumberTooLong()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function testPassportNumberIsUsed()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['passport_type_id'] = $user->passport_type_id;
        $data['passport_number'] = $user->passport_number;
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number has already been taken.']);
    }

    public function testMissingGender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function testGenderTooLong()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function testMissingBirthday()
    {
        $data = $this->happyCase;
        unset($data['birthday']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function testBirthdayIsNotDate()
    {
        $data = $this->happyCase;
        $data['birthday'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function testBirthdayTooClose()
    {
        $data = $this->happyCase;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->post(route('register'), $data);
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function testEmailInvalid()
    {
        $data = $this->happyCase;
        $data['email'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    }

    public function testMobileNotInteger()
    {
        $data = $this->happyCase;
        $data['mobile'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function testMobileTooShort()
    {
        $data = $this->happyCase;
        $data['mobile'] = '1234';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function testMobileTooLong()
    {
        $data = $this->happyCase;
        $data['mobile'] = '1234567890123456';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must not have more than 15 digits.']);
    }

    public function testWithoutMiddleNameAndMobileAndEmailHappyCase()
    {
        $data = $this->happyCase;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithoutMobileAndEmailAndWithMiddleNameHappyCase()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithoutMiddleNameAndEmailAndWithMobileHappyCase()
    {
        $data = $this->happyCase;
        $data['mobile'] = 12345678;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithoutMiddleNameAndMobileAndWithEmailHappyCase()
    {
        $data = $this->happyCase;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithMiddleNameAndMobileAndWithoutEmailHappyCase()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['mobile'] = 12345678;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithMiddleNameAndEmailAndWithoutMobileHappyCase()
    {
        $data = $this->happyCase;
        $data['mobile'] = 12345678;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithEmailAndMobileAndWithoutMiddleNameHappyCase()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['mobile'] = 12345678;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }

    public function testWithMiddleAndEmailAndMobileNameHappyCase()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertJson(['success']);
    }
}
