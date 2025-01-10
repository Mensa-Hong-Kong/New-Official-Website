<?php

namespace Tests\Feature\Admin\Users;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.users.reset-password',
                ['user' => User::factory()->create()]
            ), ['contact_type' => fake()->randomElement(['email', 'mobile'])]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:User')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $user]
                ), ['contact_type' => fake()->randomElement(['email', 'mobile'])]
            );
        $response->assertForbidden();
    }

    public function test_not_exists_user()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => 0]
                ), ['contact_type' => fake()->randomElement(['email', 'mobile'])]
            );
        $response->assertNotFound();
    }

    public function test_missing_contact_type()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $this->user]
                )
            );
        $response->assertInvalid(['contact_type' => 'The contact type field is required, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_contact_type_is_not_string()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $this->user]
                ), ['contact_type' => [fake()->randomElement(['email', 'mobile'])]]
            );
        $response->assertInvalid(['contact_type' => 'The contact type field must be a string, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_contact_type_is_not_in_list()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $this->user]
                ), ['contact_type' => 'abc']
            );
        $response->assertInvalid(['contact_type' => 'The selected contact type is invalid, if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_user_have_no_default_contact_of_contact_type()
    {
        $contactType = fake()->randomElement(['email', 'mobile']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $this->user]
                ), ['contact_type' => $contactType]
            );
        $contact = UserHasContact::where('user_id', $this->user->id)
            ->where('type', $contactType)
            ->where('is_default', true)
            ->first();
        $this->assertNull($contact);
        $response->assertInvalid(['contact_type' => "This user have no default $contactType, cannot reset password by $contactType."]);
    }

    public function test_happy_case()
    {
        Notification::fake();
        $contact = UserHasContact::factory()
            ->state(['is_default' => true])
            ->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.users.reset-password',
                    ['user' => $this->user]
                ), ['contact_type' => $contact->type]
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The new password has been send to user default {$contact->type}."]);
        Notification::assertSentTo(
            [$contact], ResetPasswordNotification::class
        );
    }
}
