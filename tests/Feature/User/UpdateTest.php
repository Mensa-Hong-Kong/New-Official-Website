<?php

namespace Tests\Feature\User;

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

    public function test_without_change_username_and_new_password_happy_case()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->put(route('profile.update'), $data);
        $response->assertSuccessful();
        $unsetKeys = ['password', 'new_password', 'new_password_confirmation'];
        $expect = array_diff_key($data, array_flip($unsetKeys));
        $expect['success'] = 'The profile update success!';
        $response->assertJson($expect);
    }

    public function test_with_change_username_without_new_password_happy_case()
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

    public function test_with_new_password_without_change_username_happy_case()
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

    public function test_with_change_username_and_new_password_happy_case()
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
