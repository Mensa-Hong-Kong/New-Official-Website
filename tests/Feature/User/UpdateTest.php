<?php

namespace Tests\Feature\User;

use App\Models\Address;
use App\Models\District;
use App\Models\Member;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCaseWithoutPassportInformation = ['username' => 'testing123'];

    private $happyCaseWithPassportInformation = [
        'username' => 'testing123',
        'family_name' => 'Chan',
        'middle_name' => 'Diamond',
        'given_name' => 'Chi Nan',
        'passport_type_id' => 1,
        'passport_number' => 'A12345678',
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
        $response = $this->putJson(route('profile.update'));
        $response->assertUnauthorized();
    }

    public function test_username_is_not_string()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['username'] = ['12345678'];
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function test_username_too_short()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['username'] = '1234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function test_username_too_long()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['username'] = '12345678901234567';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function test_missing_password_when_username_present()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['username'] = 'testing2';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function test_missing_password_when_new_password_present()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function test_password_is_not_string()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['username'] = 'testing2';
        $data['password'] = ['12345678'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function test_password_too_short()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '1234567';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_password_too_long()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '12345678901234567';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function test_password_incorrect()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = 'wrong_password';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The provided password is incorrect.']);
    }

    public function test_new_password_is_not_string()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '12345678';
        $data['new_password'] = ['12345678'];
        $data['new_password_confirmation'] = ['12345678'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be a string.']);
    }

    public function test_new_password_too_short()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '12345678';
        $data['new_password'] = '1234567';
        $data['new_password_confirmation'] = '1234567';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must be at least 8 characters.']);
    }

    public function test_new_password_too_long()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '12345678';
        $data['new_password'] = '12345678901234567';
        $data['new_password_confirmation'] = '12345678901234567';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field must not be greater than 16 characters.']);
    }

    public function test_confirm_new_password_not_match()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '87654321';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['new_password' => 'The new password field confirmation does not match.']);
    }

    public function test_prefix_name_is_not_string()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['prefix_name'] = ['Mr.'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['prefix_name' => 'The prefix name field must be a string.']);
    }

    public function test_prefix_name_too_long()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['prefix_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['prefix_name' => 'The prefix name field must not be greater than 255 characters.']);
    }

    public function test_nickname_is_not_string()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['nickname'] = ['Tester'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['nickname' => 'The nickname field must be a string.']);
    }

    public function test_nickname_too_long()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['nickname'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['nickname' => 'The nickname field must not be greater than 255 characters.']);
    }

    public function test_suffix_name_is_not_string()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['suffix_name'] = ['Jr.'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['suffix_name' => 'The suffix name field must be a string.']);
    }

    public function test_suffix_name_too_long()
    {
        $member = $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['suffix_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['suffix_name' => 'The suffix name field must not be greater than 255 characters.']);
    }

    public function test_exists_family_name_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $this->assertFalse($this->user->canEditPassportInformation);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['family_name'] = 'Chan';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_family_name_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['family_name']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function test_family_name_is_not_string_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['family_name'] = ['Chan'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function test_family_name_too_long_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function test_exists_middle_name_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['middle_name'] = 'Diamond';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_middle_name_is_not_string_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['middle_name'] = ['Diamond'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function test_middle_name_too_long_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function test_exists_given_name_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['given_name'] = 'Chi Nan';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_given_name_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['given_name']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function test_given_name_is_not_string_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['given_name'] = ['Chi Nan'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function test_given_name_too_long_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function test_exists_passport_type_id_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['passport_type_id'] = 1;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_passport_type_id_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['passport_type_id']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field is required.']);
    }

    public function test_passport_type_id_not_integer_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['passport_type_id'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_not_exists_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['passport_type_id'] = 0;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_type_id' => 'The selected passport type id is invalid.']);
    }

    public function test_exists_passport_number_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithoutPassportInformation;
        $data['passport_number'] = 'A12345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_passport_number_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['passport_number']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_invalid_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['passport_number'] = 'A123-45678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid. It should only contain uppercase letters and numbers.']);
    }

    public function test_passport_number_too_short_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['passport_number'] = 'A123456';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['passport_number'] = 'A123456789012345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_exists_gender_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithPassportInformation;
        $data['gender'] = 'Male';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_gender_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['gender']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function test_gender_too_long_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function test_exists_birthday_when_user_cannot_edit_passport_information()
    {
        $this->user->member()->create();
        $data = $this->happyCaseWithPassportInformation;
        $data['birthday'] = '1997-07-01';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertConflict();
        $response->assertJson(['message' => 'You cannot update passport information, please read the instructions on the profile page.']);
    }

    public function test_missing_birthday_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        unset($data['birthday']);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function test_birthday_is_not_date_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['birthday'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function test_birthday_too_close_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['birthday'] = now()->subYears(2)->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function test_missing_district_id_when_user_is_active_member()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => now()->year,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The district field is required when you are an active member or have membership order in progress.']);
    }

    public function test_missing_district_id_when_user_has_membership_order_in_progress()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'pending',
            'from_year' => now()->year,
            'expired_at' => now()->addMinutes(30),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The district field is required when you are an active member or have membership order in progress.']);
    }

    public function test_district_id_is_not_integer()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = 'abc';
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The district field must be an integer.']);
    }

    public function test_district_id_not_exists()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = 0;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The selected district is invalid.']);
    }

    public function test_missing_address_when_user_is_active_member()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => now()->year,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field is required when you are an active member or have membership order in progress.']);
    }

    public function test_missing_address_when_user_has_membership_order_in_progress()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'pending',
            'from_year' => now()->year,
            'expired_at' => now()->addMinutes(30),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field is required when you are an active member or have membership order in progress.']);
    }

    public function test_address_required_when_district_id_present()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field is required when district is present.']);
    }

    public function test_address_not_string()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = ['123 Street'];
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field must be a string.']);
    }

    public function test_address_too_long()
    {
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field must not be greater than 255 characters.']);
    }

    public function test_happy_case_without_change_username_and_new_password_and_address_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_happy_case_with_change_username_without_new_password_and_address_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_happy_case_with_new_password_without_change_username_and_address_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_happy_case_with_change_address_when_user_can_edit_passport_information_and_before_have_no_address_and_without_change_username_and_new_password()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
        $this->assertTrue(
            Address::where('district_id', $data['district_id'])
                ->where('value', $data['address'])
                ->exists()
        );
    }

    public function test_happy_case_with_change_address_when_user_can_edit_passport_information_and_before_has_address_and_the_user_address_have_no_other_object_using_and_without_change_username_and_new_password()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
        $this->assertTrue(
            Address::where('district_id', $data['district_id'])
                ->where('value', $data['address'])
                ->exists()
        );
        $this->assertFalse(
            Address::where('district_id', $address->district_id)
                ->where('value', $address->value)
                ->exists()
        );
        $this->assertEquals(1,Address::count());
    }

    public function test_happy_case_without_address_when_user_can_edit_passport_information_and_before_has_address_and_the_user_address_have_no_other_object_using_and_without_change_username_and_new_password()
    {
        $data = $this->happyCaseWithPassportInformation;
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['address'] = null;
        $expect['district_id'] = null;
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
        $this->assertNull($this->user->fresh()->address_id);
        $this->assertEquals(0,Address::count());
    }

    public function test_happy_case_with_change_address_when_user_can_edit_passport_information_and_before_has_address_and_the_user_address_have_other_object_using_and_without_change_username_and_new_password()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        User::factory()->state(['address_id' => $address->id])->create();
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
        $this->assertTrue(
            Address::where('district_id', $data['district_id'])
                ->where('value', $data['address'])
                ->exists()
        );
        $this->assertTrue(
            Address::where('district_id', $address->district_id)
                ->where('value', $address->value)
                ->exists()
        );
        $this->assertNotEquals($address->id, $this->user->fresh()->address_id);
        $this->assertEquals(2,Address::count());
    }

    public function test_happy_case_with_change_username_and_new_password_and_without_address_when_user_can_edit_passport_information()
    {
        $data = $this->happyCaseWithPassportInformation;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_happy_case_without_change_username_and_new_password_and_address_and_member_data_when_user_is_active_member_and_before_member_data_is_null()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => now()->year,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => $data['district_id'],
            'value' => $data['address'],
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $expect['prefix_name'] = null;
        $expect['nickname'] = null;
        $expect['suffix_name'] = null;
        $response->assertJson($expect);
        $member = Member::find($member->user_id);
        $this->assertNull($member->prefix_name);
        $this->assertNull($member->nickname);
        $this->assertNull($member->suffix_name);
    }

    public function test_happy_case_without_change_username_and_new_password_and_address_and_with_member_data_when_user_is_active_member_and_before_member_data_is_null()
    {
        $member = $this->user->member()->create();
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => now()->year,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => $data['district_id'],
            'value' => $data['address'],
        ]);
        $this->user->update(['address_id' => $address->id]);
        $data['prefix_name'] = 'Mr.';
        $data['nickname'] = 'Tester';
        $data['suffix_name'] = 'Jr.';
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
        $member = Member::find($member->user_id);
        $this->assertEquals($data['prefix_name'], $member->prefix_name);
        $this->assertEquals($data['nickname'], $member->nickname);
        $this->assertEquals($data['suffix_name'], $member->suffix_name);
    }

    public function test_happy_case_without_change_username_and_new_password_and_address_and_member_data_when_user_is_active_member_and_before_member_data_is_not_null()
    {
        $member = $this->user->member()->create([
            'prefix_name' => 'Mr.',
            'nickname' => 'Tester',
            'suffix_name' => 'Jr.',
        ]);
        $member->orders()->create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => now()->year,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $data = $this->happyCaseWithoutPassportInformation;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => $data['district_id'],
            'value' => $data['address'],
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->putJson(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $expect['prefix_name'] = null;
        $expect['nickname'] = null;
        $expect['suffix_name'] = null;
        $response->assertJson($expect);
        $member = Member::find($member->user_id);
        $this->assertNull($member->prefix_name);
        $this->assertNull($member->nickname);
        $this->assertNull($member->suffix_name);
    }
}
