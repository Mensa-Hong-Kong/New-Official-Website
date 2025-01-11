<?php

namespace Tests\Feature\Schedules;

use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Schedules\ClearUnusedAdminVerifiedRecode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClearUnusedAdminVerifiedRecodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setup();
        User::factory()->create();
        Notification::fake();
    }

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

    public function test_have_no_verify_record()
    {
        $this->assertEquals(0, ContactHasVerification::count());
        (new ClearUnusedAdminVerifiedRecode)();
    }

    private function create_using_user_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
    }

    private function create_unused_user_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        $contact->lastVerification()->update(
            fake()->randomElement([
                [
                    'verified_at' => now(),
                    'expired_at' => now(),
                ],
                ['closed_at' => now()],
            ])
        );
    }

    private function create_using_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
    }

    private function create_unused_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
    }

    public function test_only_has_using_user_verify_record()
    {
        $this->create_using_user_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_unused_user_verify_record()
    {
        $this->create_unused_user_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_using_admin_verify_record()
    {
        $this->create_using_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_unused_admin_verify_record()
    {
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(0, ContactHasVerification::count());
    }

    public function test_only_has_using_user_verify_record_and_unused_user_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_unused_user_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_using_user_verify_record_and_using_admin_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_using_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_using_user_verify_record_and_unused_admin_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_unused_user_verify_record_and_using_admin_verify_record()
    {
        $this->create_unused_user_verify_record();
        $this->create_using_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_unused_user_verify_record_and_unused_admin_verify_record()
    {
        $this->create_unused_user_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_using_admin_verify_record_and_unused_admin_verify_record()
    {
        $this->create_using_admin_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_have_no_using_user_verify_record()
    {
        $this->create_unused_user_verify_record();
        $this->create_using_admin_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_unused_user_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_using_admin_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_using_admin_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_unused_user_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_unused_admin_verify_record()
    {
        $this->create_using_user_verify_record();
        $this->create_unused_user_verify_record();
        $this->create_using_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(3, ContactHasVerification::count());
    }

    public function test_have_all()
    {
        $this->create_using_user_verify_record();
        $this->create_unused_user_verify_record();
        $this->create_using_admin_verify_record();
        $this->create_unused_admin_verify_record();
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(3, ContactHasVerification::count());
    }
}
