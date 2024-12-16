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

    public function setUp(): void
    {
        parent::setup();
        Queue::fake();
        Notification::fake();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
    }

    public function testHaveNoLogin()
    {
        $response = $this->post(
            route('verify', ['contact' => $this->contact]),
            ['code' => '123456']
        );
        $response->assertRedirectToRoute('login');
    }

    public function testUserContactIsNotZirself()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertForbidden();
    }

    public function testTheContactHasBeenVerified()
    {
        $this->contact->lastVerification()->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertGone();
        $response->assertSee("The {$this->contact->type} verified.");
    }

    public function testHaveNoVerifyCodeRecord()
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

    public function testVerifyCodeExpiredAndNotRequestTooManyTime()
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

    public function testVerifyCodeExpiredAndRequestTooManyTime()
    {
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->lastVerification()->update(['closed_at' => now()->subSecond()]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertInvalid(['failed' => 'The verify code expired, this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow.']);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function testTriedTooManyTimeAndNotRequestTooManyTime()
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

    public function testTriedTooManyTimeAndRequestTooManyTime()
    {
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->lastVerification()->update(['tried_time' => 5]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertInvalid(['failed' => 'The verify code tried more than 5 times, this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow.']);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function testMissingCode()
    {
        $response = $this->actingAs($this->user)
            ->post(route('verify', ['contact' => $this->contact]));
        $response->assertInvalid(['code' => 'The code field is required.']);
    }

    public function testCodeIsNotString()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => ['123456']]
            );
        $response->assertInvalid(['code' => 'The code field must be a string.']);
    }

    public function testCodeSizeIsNotMatch()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '1234567']
            );
        $response->assertInvalid(['code' => 'The code field must be 6 characters.']);
    }

    public function testCodeIsNotAlphaNumber()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '!@#$%^']
            );
        $response->assertInvalid(['code' => 'The code field must only contain letters and numbers.']);
    }

    public function testIncorrectVerifyCodeAndNotTriedTooManyTime()
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

    public function testIncorrectVerifyCodeAndTriedTooManyTimeAndNotRequestTooManyTime()
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

    public function testIncorrectVerifyCodeAndTriedTooManyTimeAndRequestTooManyTime()
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
        $response->assertInvalid(['failed' => 'The verify code is incorrect, the verify code tried 5 time, this contact have sent 5 time verify code and each contact each day only can try 5 verify code, please again on tomorrow.']);
        Notification::assertNotSentTo(
            [$this->contact], VerifyContact::class
        );
    }

    public function testHappyCase()
    {
        $response = $this->actingAs($this->user)
            ->post(
                route('verify', ['contact' => $this->contact]),
                ['code' => '123456']
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} verifiy success."]);
        $this->assertTrue($this->contact->refresh()->isVerified());
    }
}
