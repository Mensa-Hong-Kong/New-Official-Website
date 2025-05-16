<?php

namespace Tests\Feature\User;

use App\Jobs\Stripe\Customers\CreateUser;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

    protected function setUp(): void
    {
        parent::setup();
        Queue::fake();
    }

    public function test_view(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('register'));
        $response->assertRedirectToRoute('index');
    }

    public function test_has_been_login_submit_register(): void
    {
        $data = $this->happyCase;
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('register'), $data);
        $response->assertRedirectToRoute('index');
    }

    public function test_missing_username()
    {
        $data = $this->happyCase;
        unset($data['username']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field is required.']);
    }

    public function test_username_is_not_string()
    {
        $data = $this->happyCase;
        $data['username'] = ['12345678'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function test_username_too_short()
    {
        $data = $this->happyCase;
        $data['username'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function test_username_too_long()
    {
        $data = $this->happyCase;
        $data['username'] = '12345678901234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function test_username_is_used()
    {
        $user = User::factory()->create();
        $data = $this->happyCase;
        $data['username'] = $user->username;
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['username' => 'The username has already been taken.']);
    }

    public function test_missing_password()
    {
        $data = $this->happyCase;
        unset($data['password']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field is required.']);
    }

    public function test_password_is_not_string()
    {
        $data = $this->happyCase;
        $data['password'] = ['12345678'];
        $data['password_confirmation'] = ['12345678'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function test_password_too_short()
    {
        $data = $this->happyCase;
        $data['password'] = '1234567';
        $data['password_confirmation'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_password_too_long()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678901234567';
        $data['password_confirmation'] = '12345678901234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function test_confirm_password_not_match()
    {
        $data = $this->happyCase;
        $data['password_confirmation'] = '87654321';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['password' => 'The password field confirmation does not match.']);
    }

    public function test_missing_family_name()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function test_family_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function test_family_name_too_long()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function test_middle_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function test_middle_name_too_long()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function test_missing_given_name()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function test_given_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function test_given_name_too_long()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function test_missing_passport_type_id()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function test_passport_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function test_missing_passport_number()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_not_match()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function test_passport_number_too_short()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_missing_gender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function test_gender_too_long()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function test_missing_birthday()
    {
        $data = $this->happyCase;
        unset($data['birthday']);
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function test_birthday_is_not_date()
    {
        $data = $this->happyCase;
        $data['birthday'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function test_birthday_too_close()
    {
        $data = $this->happyCase;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->post(route('register'), $data);
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function test_email_invalid()
    {
        $data = $this->happyCase;
        $data['email'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    }

    public function test_mobile_not_integer()
    {
        $data = $this->happyCase;
        $data['mobile'] = 'abc';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $data = $this->happyCase;
        $data['mobile'] = '1234';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $data = $this->happyCase;
        $data['mobile'] = '1234567890123456';
        $response = $this->post(route('register'), $data);
        $response->assertInvalid(['mobile' => 'The mobile field must not have more than 15 digits.']);
    }

    public function test_without_middle_name_and_mobile_and_email_happy_case()
    {
        $data = $this->happyCase;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        Queue::assertPushed(CreateUser::class);
    }

    public function test_without_mobile_and_email_and_with_middle_name_happy_case()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        Queue::assertPushed(CreateUser::class);
    }

    public function test_without_middle_name_and_email_and_with_mobile_happy_case()
    {
        $data = $this->happyCase;
        $data['mobile'] = 12345678;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'mobile')
                ->where('contact', 12345678)
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }

    public function test_without_middle_name_and_mobile_and_with_email_happy_case()
    {
        $data = $this->happyCase;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'email')
                ->where('contact', 'example@gamil.com')
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }

    public function test_with_middle_name_and_mobile_and_without_email_happy_case()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['mobile'] = 12345678;
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'mobile')
                ->where('contact', 12345678)
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }

    public function test_with_middle_name_and_email_and_without_mobile_happy_case()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'email')
                ->where('contact', 'example@gamil.com')
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }

    public function test_with_email_and_mobile_and_without_middle_name_happy_case()
    {
        $data = $this->happyCase;
        $data['mobile'] = 12345678;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'mobile')
                ->where('contact', 12345678)
                ->exists()
        );
        $this->assertTrue(
            UserHasContact::where('type', 'email')
                ->where('contact', 'example@gamil.com')
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }

    public function test_with_middle_and_email_and_mobile_name_happy_case()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'Tai Man';
        $data['mobile'] = 12345678;
        $data['email'] = 'example@gamil.com';
        $response = $this->post(route('register'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $this->assertTrue(
            UserHasContact::where('type', 'mobile')
                ->where('contact', 12345678)
                ->exists()
        );
        $this->assertTrue(
            UserHasContact::where('type', 'email')
                ->where('contact', 'example@gamil.com')
                ->exists()
        );
        Queue::assertPushed(CreateUser::class);
    }
}
