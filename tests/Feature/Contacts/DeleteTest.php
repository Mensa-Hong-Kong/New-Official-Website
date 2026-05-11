<?php

namespace Tests\Feature\Contacts;

use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserHasContact $contact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->contact = UserHasContact::factory()->create();
    }

    public function test_have_no_login(): void
    {
        $response = $this->deleteJson(route(
            'contacts.destroy',
            ['contact' => $this->contact]
        ));
        $response->assertUnauthorized();
    }

    public function test_user_contact_is_not_zirself(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertForbidden();
    }

    public function test_happy_case(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson(route(
                'contacts.destroy',
                ['contact' => $this->contact]
            ));
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$this->contact->type} delete success!"]);
    }
}
