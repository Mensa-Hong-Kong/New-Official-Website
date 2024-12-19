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
        $response = $this->get(route('contacts.send-verify-code', ['contact' => $this->contact]));
        $response->assertRedirectToRoute('login');
    }

    public function test_user_contact_is_not_zirself()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('contacts.send-verify-code', ['contact' => $this->contact]));
        $response->assertForbidden();
    }

    public function test_the_contact_has_been_verified()
    {
        $this->contact->lastVerification
            ->update(['verified_at' => now()]);
        $response = $this->actingAs($this->user)->get(route('contacts.send-verify-code', ['contact' => $this->contact]));
        $response->assertGone();
        // $response->assertSee("The {$this->contact->type} verified.");
    }

    public function test_request_too_fast()
    {
        $response = $this->actingAs($this->user)->get(route('contacts.send-verify-code', ['contact' => $this->contact]));
        $response->assertTooManyRequests();
        // $response->assertSee('For each contact each minute only can get 1 time verify code, please try again later.');
    }

    public function test_request_too_many_time_in_same_contact_and_diff_user()
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
        $contact->lastVerification->fillable(['created_at'])
            ->update(['created_at' => now()->subMinute()]);
        $response = $this->actingAs($user)->get(route(
            'send-verify-code', ['contact' => $contact]
        ));
        $response->assertTooManyRequests();
        // $response->assertSee("For each {$contact->type} each day only can send 5 verify code, please try again on tomorrow or contact us to verify by manual.);
    }

    public function test_request_too_many_time_in_same_user_and_diff_contact()
    {
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $this->contact->sendVerifyCode();
        $contact = UserHasContact::factory()
            ->{$this->contact->type}()
            ->state(['user_id' => $this->user->id])->create();
        $contact->lastVerification
            ->fillable(['created_at', 'user_ip'])
            ->update(['created_at' => now()->subMinute()]);
        $response = $this->actingAs($this->user)->get(route('contacts.send-verify-code', ['contact' => $contact]));
        $response->assertTooManyRequests();
        // $response->assertSee('For each user each day only can send 5 {$contact->type} verify code, please try again on tomorrow or contact us to verify by manual.');
    }

    public function test_happy_case()
    {
        $this->contact->lastVerification
            ->fillable(['created_at'])
            ->update(['created_at' => now()->subMinute()]);
        // return to zero
        Notification::fake();
        $response = $this->actingAs($this->user)->get(route('contacts.send-verify-code', ['contact' => $this->contact]));
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The verify code sent!']);
        Notification::assertSentTo(
            [$this->contact], VerifyContact::class
        );
    }
}
