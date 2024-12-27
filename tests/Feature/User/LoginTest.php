<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_view(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('login'));
        $response->assertRedirectToRoute('index');
    }

    public function test_has_been_login_submit_login(): void
    {
        $data = [
            'username' => '12345678',
            'password' => '12345678',
        ];
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('login'), $data);
        $response->assertRedirectToRoute('index');
    }

    public function test_missing_username()
    {
        $data['password'] = '12345678';
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['username' => 'The username field is required.']);
    }

    public function test_username_is_not_string()
    {
        $data = [
            'username' => ['12345678'],
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['username' => 'The username field must be a string.']);
    }

    public function test_username_too_short()
    {
        $data = [
            'username' => '1234567',
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['username' => 'The username field must be at least 8 characters.']);
    }

    public function test_username_too_long()
    {
        $data = [
            'username' => '12345678901234567',
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['username' => 'The username field must not be greater than 16 characters.']);
    }

    public function test_missing_password()
    {
        $data['username'] = '12345678';
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['password' => 'The password field is required.']);
    }

    public function test_password_is_not_string()
    {
        $data = [
            'username' => '12345678',
            'password' => ['12345678'],
        ];
        $data['password_confirmation'] = ['12345678'];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['password' => 'The password field must be a string.']);
    }

    public function test_password_too_short()
    {
        $data = [
            'username' => '12345678',
            'password' => '1234567',
        ];
        $data['password_confirmation'] = '1234567';
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['password' => 'The password field must be at least 8 characters.']);
    }

    public function test_password_too_long()
    {
        $data = [
            'username' => '12345678',
            'password' => '12345678901234567',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['password' => 'The password field must not be greater than 16 characters.']);
    }

    public function test_user_is_not_exist()
    {
        $data = [
            'username' => '12345678',
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['failed' => 'The provided username or password is incorrect.']);
    }

    public function test_username_is_not_match()
    {
        $user = User::factory()->state([
            'username' => '12345678',
            'password' => '12345678',
        ])->create();
        $data = [
            'username' => '87654321',
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['failed' => 'The provided username or password is incorrect.']);
    }

    public function test_password_is_not_match()
    {
        $user = User::factory()->state(['password' => 12345678])->create();
        $data = [
            'username' => $user->username,
            'password' => '87654321',
        ];
        $this->assertEquals(0, $user->loginLogs->count());
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['failed' => 'The provided username or password is incorrect.']);
        $countLoginLogs = UserLoginLog::where('user_id', $user->id)
            ->count();
        $this->assertEquals(1, $countLoginLogs);
    }

    public function test_login_failed_too_more()
    {
        $user = User::factory()->state(['password' => 12345678])->create();
        $loginAt = now()->format('Y-m-d H:i:s');
        $insert = array_fill(0, 10, [
            'user_id' => $user->id,
            'created_at' => $loginAt,
        ]);
        (new UserLoginLog)->fillable(['user_id', 'created_at'])
            ->insert($insert);
        $data = [
            'username' => $user->username,
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertInvalid(['throttle' => "Too many failed login attempts. Please try again later than {$loginAt}."]);
    }

    private function hasRememberWebCooky(array $cookyJar): bool
    {
        foreach ($cookyJar as $cooky) {
            if (Str::startsWith($cooky->getName(), 'remember_web_')) {
                return true;
            }
        }

        return false;
    }

    public function test_happy_case_without_remember_me()
    {
        $user = User::factory()->state(['password' => 12345678])->create();
        $data = [
            'username' => $user->username,
            'password' => '12345678',
        ];
        $response = $this->post(route('login'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $cookieJar = $response->headers->getCookies();
        $this->assertFalse($this->hasRememberWebCooky($cookieJar));
    }

    public function test_happy_case_with_remember_me()
    {
        $user = User::factory()->state(['password' => 12345678])->create();
        $data = [
            'username' => $user->username,
            'password' => '12345678',
            'remember_me' => true,
        ];
        $response = $this->post(route('login'), $data);
        $response->assertValid();
        $response->assertRedirectToRoute('profile.show');
        $cookieJar = $response->headers->getCookies();
        $this->assertTrue($this->hasRememberWebCooky($cookieJar));
    }
}
