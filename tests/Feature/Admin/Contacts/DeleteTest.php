<?php

namespace Tests\Feature\Admin\Contacts;

use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:User');
    }

    public function test_have_no_login()
    {
        $contact = UserHasContact::factory()->create();
        $response = $this->deleteJson(
            route(
                'admin.contacts.destroy',
                ['contact' => $contact]
            )
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_user_permission()
    {
        $contact = UserHasContact::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:User')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.contacts.destroy',
                ['contact' => $contact]
            )
        );
        $response->assertForbidden();
    }

    public function test_happy_casee()
    {
        $contact = UserHasContact::factory()
            ->create();
        $response = $this->actingAs($this->user)
            ->deleteJson(
                route(
                    'admin.contacts.destroy',
                    ['contact' => $contact]
                )
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => "The {$contact->type} delete success!"]);
        $this->assertNull(UserHasContact::firstWhere('id', $contact->id));
    }
}
