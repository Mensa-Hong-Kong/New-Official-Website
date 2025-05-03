<?php

namespace Tests\Feature\User;

use App\Models\ContactHasVerification;
use App\Models\ResetPasswordLog;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function verified(UserHasContact $contact)
    {
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => User::inRandomOrder()->first()->id,
            'creator_ip' => fake()->ipv4(),
            'middleware_should_count' => false,
        ]);
    }

    public function test_view(): void
    {
        $response = $this->get(route('forget-password'));
        $response->assertSuccessful();
    }

    public function test_has_been_login_submit_reset_password(): void
    {
        $user = User::factory()->create();
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $response = $this->actingAs($user)
            ->putJson(route('reset-password'),
                [
                    'passport_type_id' => $user->passport_type_id,
                    'passport_number' => $user->passport_number,
                    'birthday' => $user->birthday,
                    'verified_contact_type' => $contact->contact,
                    'verified_contact' => $contact->type,
                ]
            );
        $response->assertRedirectToRoute('index');
    }

    public function test_missing_passport_type_id()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function test_passport_type_id_is_not_integer()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 'abc',
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_is_not_exist()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 0,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function test_missing_passport_number()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 1,
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_not_match()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '1234567$',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function test_passport_number_too_short()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '1234567',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '1234567890123456789',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_missing_birthday()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['birthday' => 'The birthday field is required.']);
    }

    public function test_birthday_is_not_date()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(route(
            'reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => 'abc',
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['birthday' => 'The birthday field must be a valid date.']);
    }

    public function test_birthday_too_close()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->addDay()->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $beforeTwoYear = now()->subYears(2)->format('Y-m-d');
        $response->assertInvalid(['birthday' => "The birthday field must be a date before or equal to $beforeTwoYear."]);
    }

    public function test_missing_verified_contact_type()
    {
        $contact = '';
        switch (fake()->randomElement(['email', 'mobile'])) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['verified_contact_type' => 'The verified contact type field is required.']);
    }

    public function test_verified_contact_type_is_not_string()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => [$type],
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['verified_contact_type' => 'The verified contact type field must be a string.']);
    }

    public function test_verified_contact_type_is_not_in_list()
    {
        $contact = '';
        switch (fake()->randomElement(['email', 'mobile'])) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => 'abc',
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['verified_contact_type' => 'The selected verified contact type is invalid.']);
    }

    public function test_missing_contact()
    {
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => fake()->randomElement(['email', 'mobile']),
            ]
        );
        $response->assertInvalid(['verified_contact' => 'The verified contact field is required.']);
    }

    public function test_email_invalid()
    {
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => 'email',
                'verified_contact' => 'abc',
            ]
        );
        $response->assertInvalid(['verified_contact' => 'The verified contact of email must be a valid email address.']);
    }

    public function test_mobile_not_integer()
    {
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => 'mobile',
                'verified_contact' => 'abc',
            ]
        );
        $response->assertInvalid(['verified_contact' => 'The verified contact of mobile must be an integer.']);
    }

    public function test_mobile_too_short()
    {
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => 'mobile',
                'verified_contact' => '1234',
            ]
        );
        $response->assertInvalid(['verified_contact' => 'The verified contact of mobile must have at least 5 digits.']);
    }

    public function test_mobile_too_long()
    {
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => 'mobile',
                'verified_contact' => '1234567890123456',
            ]
        );
        $response->assertInvalid(['verified_contact' => 'The verified contact of mobile must not have more than 15 digits.']);
    }

    public function test_account_not_found()
    {
        $type = fake()->randomElement(['email', 'mobile']);
        $contact = '';
        switch ($type) {
            case 'email':
                $contact = fake()->freeEmail();
                break;
            case 'mobile':
                $contact = fake()->numberBetween(10000, 999999999999999);
                break;
        }
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => 3,
                'passport_number' => '12345678',
                'birthday' => now()->subYears(2)->format('Y-m-d'),
                'verified_contact_type' => $type,
                'verified_contact' => $contact,
            ]
        );
        $response->assertInvalid(['failed' => 'The provided passport, birthday or verified contact is incorrect.']);
        $this->assertTrue(
            ResetPasswordLog::where('passport_type_id', 3)
                ->where('passport_number', '12345678')
                ->where('contact_type', $type)
                ->exists()
        );
    }

    public function test_reset_password_failed_too_many_time_within_24_hours()
    {
        $user = User::factory()->create();
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $failedAt = now()->format('Y-m-d H:i:s');
        $insert = array_fill(0, 10, [
            'passport_type_id' => $user->passport_type_id,
            'passport_number' => $user->passport_number,
            'contact_type' => $contact->type,
            'creator_ip' => fake()->ipv4(),
            'created_at' => $failedAt,
        ]);
        ResetPasswordLog::insert($insert);
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'birthday' => $user->birthday,
                'verified_contact_type' => $contact->type,
                'verified_contact' => $contact->contact,
            ]
        );
        $response->assertTooManyRequests();
        $response->assertJson(['message' => "Too many failed reset password attempts. Please try again later than $failedAt."]);
    }

    public function test_happy_case_when_have_no_failed_record()
    {
        Notification::fake();
        $user = User::factory()->create();
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'birthday' => $user->birthday,
                'verified_contact_type' => $contact->type,
                'verified_contact' => $contact->contact,
            ]
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The new password has been send to {$contact->type} of {$contact->contact}"]);
        Notification::assertSentTo(
            [$contact], ResetPasswordNotification::class
        );
    }

    public function test_happy_case_when_reset_password_have_a_lot_of_failed_but_under_limit_within_24_hours()
    {
        Notification::fake();
        $user = User::factory()
            ->state(['birthday' => now()->subYears(2)->format('Y-m-d')])
            ->create();
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $insert = array_fill(
            0, 9, [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'contact_type' => $contact->type,
                'creator_ip' => fake()->ipv4(),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]
        );
        ResetPasswordLog::insert($insert);
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'birthday' => $user->birthday,
                'verified_contact_type' => $contact->type,
                'verified_contact' => $contact->contact,
            ]
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The new password has been send to {$contact->type} of {$contact->contact}"]);
        Notification::assertSentTo(
            [$contact], ResetPasswordNotification::class
        );
    }

    public function test_happy_case_when_reset_password_have_number_of_limit_failed_but_has_one_without_24_hours()
    {
        Notification::fake();
        $user = User::factory()->create();
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $insert = array_fill(
            1, 9, [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'contact_type' => $contact->type,
                'creator_ip' => fake()->ipv4(),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]
        );
        $insert[0] = [
            'passport_type_id' => $user->passport_type_id,
            'passport_number' => $user->passport_number,
            'contact_type' => $contact->type,
            'creator_ip' => fake()->ipv4(),
            'created_at' => now()->subDay()->subSecond()->format('Y-m-d H:i:s'),
        ];
        ResetPasswordLog::insert($insert);
        $response = $this->putJson(
            route('reset-password'),
            [
                'passport_type_id' => $user->passport_type_id,
                'passport_number' => $user->passport_number,
                'birthday' => $user->birthday,
                'verified_contact_type' => $contact->type,
                'verified_contact' => $contact->contact,
            ]
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The new password has been send to {$contact->type} of {$contact->contact}"]);
        Notification::assertSentTo(
            [$contact], ResetPasswordNotification::class
        );
    }
}
