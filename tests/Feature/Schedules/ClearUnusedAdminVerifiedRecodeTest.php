<?php

namespace Tests\Feature\Schedules;

use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Schedules\ClearUnusedAdminVerifiedRecode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ClearUnusedAdminVerifiedRecodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setup();
        User::factory()->create();
        Queue::fake();
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

    public function test_only_has_using_user_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $this->assertEquals(
            1, ContactHasVerification::whereNotNull('code')
                ->whereNull('expired_at')
                ->count()
        );
        $this->assertEquals(
            0, ContactHasVerification::whereNotNull('code')
                ->where('expired_at', '<=', now())
                ->count()
        );
        $this->assertEquals(
            0, ContactHasVerification::whereNull('code')
                ->whereNull('expired_at')
                ->count()
        );
        $this->assertEquals(
            0, ContactHasVerification::whereNull('code')
                ->where('expired_at', '<=', now())
                ->count()
        );
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(
            1, ContactHasVerification::whereNotNull('code')
                ->whereNull('expired_at')
                ->count()
        );
    }

    public function test_only_has_unused_user_verify_record()
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
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(
            1, ContactHasVerification::whereNotNull('code')
                ->count()
        );
    }

    public function test_only_has_using_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(
            1, ContactHasVerification::whereNull('code')
                ->whereNull('expired_at')
                ->count()
        );
    }

    public function test_only_has_unused_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(
            0, ContactHasVerification::whereNull('code')
                ->where('expired_at', '<=', now())
                ->count()
        );
    }

    public function test_only_has_using_user_verify_record_and_unused_user_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
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
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_using_user_verify_record_and_using_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_using_user_verify_record_and_unused_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_unused_user_verify_record_and_using_admin_verify_record()
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_has_unused_user_verify_record_and_unused_admin_verify_record()
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_has_using_admin_verify_record_and_unused_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(1, ContactHasVerification::count());
    }

    public function test_only_have_no_using_user_verify_record()
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_unused_user_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_using_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
        $contact->lastVerification()->update(['expired_at' => now()]);
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(2, ContactHasVerification::count());
    }

    public function test_only_have_no_has_unused_admin_verify_record()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(3, ContactHasVerification::count());
    }

    public function test_have_all()
    {
        $contact = UserHasContact::factory()->create();
        $contact->sendVerifyCode();
        if (fake()->randomElement([true, false])) {
            $contact->lastVerification()->update(['verified_at' => now()]);
        }
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
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact = UserHasContact::factory()->create();
        $this->verified($contact);
        $contact->lastVerification()->update(['expired_at' => now()]);
        (new ClearUnusedAdminVerifiedRecode)();
        $this->assertEquals(3, ContactHasVerification::count());
    }
}
