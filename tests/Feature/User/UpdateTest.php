<?php

namespace Tests\Feature\User;

use App\Models\Address;
use App\Models\District;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'username' => 'testing123',
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

    public function test_missing_password_when_username_present()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The password field is required when you change the username or password.']);
    }

    public function test_missing_password_when_new_password_present()
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

    public function test_password_incorrect()
    {
        $data = $this->happyCase;
        $data['password'] = 'wrong_password';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['password' => 'The provided password is incorrect.']);
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
        $data = $this->happyCase;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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
        $data = $this->happyCase;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The district field is required when you are an active member or have membership order in progress.']);
    }

    public function test_district_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['district_id'] = 'abc';
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['district_id' => 'The district field must be an integer.']);
    }

    public function test_district_id_not_exists()
    {
        $data = $this->happyCase;
        $data['district_id'] = 0;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field is required when you are an active member or have membership order in progress.']);
    }

    public function test_address_required_when_district_id_present()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field is required when district is present.']);
    }

    public function test_address_not_string()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = ['123 Street'];
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field must be a string.']);
    }

    public function test_address_too_long()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertInvalid(['address' => 'The address field must not be greater than 255 characters.']);
    }

    public function test_without_change_username_and_new_password_and_address_happy_case()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_with_change_username_without_new_password_and_address_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_with_new_password_without_change_username_and_address_happy_case()
    {
        $data = $this->happyCase;
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_with_change_address_when_before_user_have_no_address_and_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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

    public function test_with_change_address_when_before_user_has_address_and_the_user_address_have_no_other_object_using_and_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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

    public function test_without_address_when_before_user_has_address_and_the_user_address_have_no_other_object_using_and_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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

    public function test_with_change_address_when_before_user_has_address_and_the_user_address_have_other_object_using_and_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $data['district_id'] = District::inRandomOrder()->first()->id;
        $data['address'] = '123 Street';
        $address = Address::create([
            'district_id' => District::inRandomOrder()->first()->id,
            'value' => '456 Street',
        ]);
        User::factory()->state(['address_id' => $address->id])->create();
        $this->user->update(['address_id' => $address->id]);
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
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

    public function test_with_change_username_and_new_password_and_without_address_happy_case()
    {
        $data = $this->happyCase;
        $data['username'] = 'testing2';
        $data['password'] = '12345678';
        $data['new_password'] = '98765432';
        $data['new_password_confirmation'] = '98765432';
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }
}
