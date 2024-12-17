<?php

namespace Tests\Feature\Contact;

use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\VerifyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $contact;

    protected function setUp(): void
    {
        parent::setup();
        Queue::fake();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->post(
            route('verify', ['contact' => $this->contact]),
            ['code' => '123456']
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_user_contact_is_not_zirself()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertForbidden();
    }

    public function test_the_contact_has_been_verified()
    {
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertGone();
        // $response->assertSee("The {$this->contact->type} verified.");
    }

    public function test_have_no_verify_code_record()
    {
        $this->contact->lastVerification()->delete();
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertNotFound();
        // $response->assertSee("The verify request record is not found, the new verify code sent.");
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_verify_code_expired_and_not_request_too_many_time()
    {
        $this->contact->lastVerification()->update(['closed_at' => now()->subSecond()]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertInvalid(['failed' => 'The verify code expired, the new verify code sent.']);
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_verify_code_expired_and_request_too_many_time_in_same_user_and_diff_contact()
    {
        $contact = UserHasContact::factory()
            ->{$this->contact->type}()
            ->state(['user_id' => $this->user->id])->create();
        $contact->sendVerifyCode();
        $contact->sendVerifyCode();
        $contact->sendVerifyCode();
        $contact->lastVerification()->update(['closed_at' => now()->subSecond()]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $contact]),
                ['code' => '123456']
            );
        $response->assertStatus(422);
        $response->assertInvalid(['failed' => "The verify code expired, your account have sent 5 {$contact->type} verify code and each user each day only can send 5 {$contact->type} verify code, please try again on tomorrow or contact us to verify by manual."]);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_tried_too_many_time_and_not_request_too_many_time()
    {
        $this->contact->lastVerification()->update(['tried_time' => 5]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertInvalid(['failed' => 'The verify code tried more than 5 times, the new verify code sent.']);
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_tried_too_many_time_and_request_too_many_time_in_same_ip_and_diff_user()
    {
        $user = User::factory()->create();
        $contact = UserHasContact::factory()
            ->state([
                'user_id' => $user->id,
                'type' => $this->contact->type,
                'contact' => $this->contact->contact,
            ])->create();
        $contact->sendVerifyCode();
        $contact->sendVerifyCode();
        $contact->sendVerifyCode();
        $contact->lastVerification()->update(['tried_time' => 5]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($user)
            ->post(
                route('verify', ['contact' => $contact]),
                ['code' => '123456']
            );
        $response->assertInvalid(['failed' => "The verify code tried more than 5 times, include other user(s), this {$contact->type} have sent 5 times verify code and each {$contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual."]);
        Notification::assertNotSentTo(
            [$contact], VerifyContact::class
        );
    }

    public function test_missing_code()
    {
        $response = $this->actingAs($this->user)
            ->post(route('verify', ['contact' => $this->contact]));
        $response->assertInvalid(['code' => 'The code field is required.']);
    }

    public function test_code_is_not_string()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => ['123456']]
            );
        $response->assertInvalid(['code' => 'The code field must be a string.']);
    }

    public function test_code_size_is_not_match()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '1234567']
            );
        $response->assertInvalid(['code' => 'The code field must be 6 characters.']);
    }

    public function test_code_is_not_alpha_number()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '!@#$%^']
            );
        $response->assertInvalid(['code' => 'The code field must only contain letters and numbers.']);
    }

    public function test_incorrect_verify_code_and_not_tried_too_many_time()
    {
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '234567']
            );
        $response->assertInvalid(['failed' => 'The verify code is incorrect.']);
        $this->assertEquals(1, $this->contact->lastVerification->tried_time);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_incorrect_verify_code_and_tried_too_many_time_and_not_request_too_many_time()
    {
        $this->contact->lastVerification()->update(['tried_time' => 4]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '234567']
            );
        $response->assertInvalid(['failed' => 'The verify code is incorrect, the verify code tried 5 time, the new verify code sent.']);
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_incorrect_verify_code_and_tried_too_many_time_and_request_too_many_time()
    {
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->lastVerification()->update(['tried_time' => 4]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '234567']
            );
        $response->assertInvalid(['failed' => "The verify code is incorrect, the verify code tried 5 time, include other user(s), this {$this->contact->type} have sent 5 times verify code and each {$this->contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual."]);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function test_happy_case_have_no_default_contact()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} verifiy success."]);
        $this->assertTrue($this->contact->refresh()->isVerified());
        $type = ucfirst($this->contact->type);
        $this->assertEquals(
            $this->contact->id,
            $this->user->refresh()->{"default{$type}"}->id ?? ''
        );
    }

    public function test_happy_case_has_default_contact()
    {
        $contact = UserHasContact::factory()
            ->{$this->contact->type}()
            ->create();
        $contact->lastVerification->update(['verified_at' => now()]);
        $contact->update(['is_default' => true]);
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} verifiy success."]);
        $this->assertTrue($this->contact->refresh()->isVerified());
        $type = ucfirst($this->contact->type);
        $this->assertEquals(
            $contact->id,
            $this->user->refresh()->{"default{$type}"}->id
        );
    }
}
