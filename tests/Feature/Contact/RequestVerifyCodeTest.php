<?php

namespace Tests\Feature\Contact;

use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\VerifyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestVerifyCodeTest extends TestCase
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
        $response = $this->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertRedirectToRoute('login');
    }

    public function test_user_contact_is_not_zirself()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertForbidden();
    }

    public function test_the_contact_has_been_verified()
    {
        $this->contact->lastVerification
            ->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertGone();
        $response->assertSee("The {$this->contact->type} verified.");
    }

    public function test_request_too_fast()
    {
        $response = $this->actingAs($this->user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertTooManyRequests();
        // $response->assertSee('For each contact each minute only can get 1 time verify code, please again later.');
    }

    public function test_request_too_many_time()
    {
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->lastVerification
            ->fillable(['created_at'])
            ->update(['created_at' => now()->subMinute()]);
        $this->actingAs($this->user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response = $this->actingAs($this->user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertTooManyRequests();
        // $response->assertSee('For each contact each day only can send 5 verify code, please again on tomorrow.');
    }

    public function test_happy_case()
    {
        $this->contact->lastVerification
            ->fillable(['created_at'])
            ->update(['created_at' => now()->subMinute()]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)->get(route('send-verify-code', ['contact' => $this->contact]));
        $response->assertSuccessful();
        $response->assertJson(['message' => 'The verify code sent!']);
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }
}
