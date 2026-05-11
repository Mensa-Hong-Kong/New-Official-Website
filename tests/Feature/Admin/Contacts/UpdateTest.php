<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->putJson(
            route(
                'admin.contacts.update',
                ['contact' => $contact]
            ), [$contact->type => $contact->contact]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_user_permission(): void
    {
        $contact = UserHasContact::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:User')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.contacts.update',
                ['contact' => $contact]
            ), [$contact->type => $contact->contact]
        );
        $response->assertForbidden();
    }

    public function test_missing_contact(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                )
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} field is required."]);
    }

    public function test_email_invalid(): void
    {
        $contact = UserHasContact::factory()->email()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), ['email' => 'abc']
            );
        $response->assertInvalid(['email' => 'The email field must be a valid email address']);
    }

    public function test_mobile_not_integer(): void
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => 'abc']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must be an integer.']);
    }

    public function test_mobile_too_short(): void
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $response = $this->actingAs($this->user)
            ->patch(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => '1234']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must have at least 5 digits.']);
    }

    public function test_mobile_too_long(): void
    {
        $contact = UserHasContact::factory()->mobile()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), ['mobile' => '1234567890123456']
            );
        $response->assertInvalid(['mobile' => 'The mobile field must not have more than 15 digits.']);
    }

    public function test_contact_exist_with_same_user(): void
    {
        $contact = UserHasContact::factory()->create();
        $newContact = UserHasContact::factory()->{$contact->type}()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $newContact->contact]
            );
        $response->assertInvalid([$contact->type => "The {$contact->type} has already been taken."]);
    }

    public function test_with_is_verified_and_verified_field_is_not_boolean(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_verified' => 'abc',
                ]
            );
        $response->assertInvalid(['is_verified' => 'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_with_is_verified_and_default_field_is_not_boolean(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_default' => 'abc',
                ]
            );
        $response->assertInvalid(['is_default' => 'The default field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case_for_not_verified_email_only_change_contact(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), ['email' => 'example@live.com']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertFalse($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_not_verified_mobile_only_change_contact(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), ['mobile' => '87654321']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertFalse($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }

    public function test_happy_case_for_not_verified_contact_only_change_to_is_verified(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => true,
            'is_default' => false,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertTrue($contactModel->isVerified);
        $this->assertFalse($contactModel->is_default);
    }

    public function test_happy_case_for_not_verified_contact_only_change_to_is_default(): void
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => true,
            'is_default' => true,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertTrue($contactModel->isVerified);
        $this->assertTrue($contactModel->is_default);
    }

    public function test_happy_case_for_not_verified_email_change_contact_and_is_verified(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_not_verified_email_change_contact_and_is_default(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertTrue($email->is_default);
    }

    public function test_happy_case_for_not_verified_mobile_change_contact_and_is_verified(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }

    public function test_happy_case_for_not_verified_mobile_change_contact_and_is_default(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertTrue($mobile->is_default);
    }

    public function test_happy_case_for_verified_email_only_change_contact(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_verified_mobile_only_change_contact(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }

    public function test_happy_case_for_verified_contact_only_change_to_is_not_verified(): void
    {
        $contact = UserHasContact::factory()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $contact->contact]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => false,
            'is_default' => false,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertFalse($contactModel->isVerified);
        $this->assertFalse($contactModel->is_default);
    }

    public function test_happy_case_for_verified_contact_only_change_to_is_default(): void
    {
        $contact = UserHasContact::factory()->create();
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => true,
            'is_default' => true,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertTrue($contactModel->isVerified);
        $this->assertTrue($contactModel->is_default);
    }

    public function test_happy_case_for_verified_email_change_contact_and_is_not_verified(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), ['email' => 'example@live.com']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertFalse($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_verified_email_change_contact_and_is_default(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertTrue($email->is_default);
    }

    public function test_happy_case_for_verified_mobile_change_contact_and_is_not_verified(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), ['mobile' => '87654321']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertFalse($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }

    public function test_happy_case_for_verified_mobile_change_contact_and_is_default(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertTrue($mobile->is_default);
    }

    public function test_happy_case_for_default_email_only_change_contact(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
            'is_default' => true,
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertTrue($email->is_default);
    }

    public function test_happy_case_for_default_mobile_only_change_contact(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
            'is_default' => true,
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_default' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => true,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertTrue($mobile->is_default);
    }

    public function test_happy_case_for_default_contact_only_change_to_is_not_verified(): void
    {
        $contact = UserHasContact::factory()->create(['is_default' => true]);
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [$contact->type => $contact->contact]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertFalse($contactModel->isVerified);
        $this->assertFalse($contactModel->is_default);
    }

    public function test_happy_case_for_default_contact_only_change_to_is_not_default(): void
    {
        $contact = UserHasContact::factory()->create(['is_default' => true]);
        $contact->newVerifyCode();
        $contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $contact]
                ), [
                    $contact->type => $contact->contact,
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The {$contact->type} update success!",
            $contact->type => $contact->contact,
            'is_verified' => true,
            'is_default' => false,
        ]);
        $contactModel = UserHasContact::firstWhere('id', $contact->id);
        $this->assertEquals($contact->contact, $contactModel->contact);
        $this->assertTrue($contactModel->isVerified);
        $this->assertFalse($contactModel->is_default);
    }

    public function test_happy_case_for_default_email_change_contact_and_is_not_verified(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
            'is_default' => true,
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), ['email' => 'example@live.com']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertFalse($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_default_email_change_contact_and_is_not_default(): void
    {
        $email = UserHasContact::factory()->create([
            'type' => 'email',
            'contact' => 'example@gamil.com',
            'is_default' => true,
        ]);
        $email->newVerifyCode();
        $email->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $email]
                ), [
                    'email' => 'example@live.com',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The email update success!',
            'email' => 'example@live.com',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $email = UserHasContact::firstWhere('id', $email->id);
        $this->assertEquals('example@live.com', $email->contact);
        $this->assertTrue($email->isVerified);
        $this->assertFalse($email->is_default);
    }

    public function test_happy_case_for_default_mobile_change_contact_and_is_not_verified(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
            'is_default' => true,
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), ['mobile' => '87654321']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => false,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertFalse($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }

    public function test_happy_case_for_default_mobile_only_change_contact_and_is_not_default(): void
    {
        $mobile = UserHasContact::factory()->create([
            'type' => 'mobile',
            'contact' => '12345678',
            'is_default' => true,
        ]);
        $mobile->newVerifyCode();
        $mobile->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.contacts.update',
                    ['contact' => $mobile]
                ), [
                    'mobile' => '87654321',
                    'is_verified' => true,
                ]
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The mobile update success!',
            'mobile' => '87654321',
            'is_verified' => true,
            'is_default' => false,
        ]);
        $mobile = UserHasContact::firstWhere('id', $mobile->id);
        $this->assertEquals('87654321', $mobile->contact);
        $this->assertTrue($mobile->isVerified);
        $this->assertFalse($mobile->is_default);
    }
}
