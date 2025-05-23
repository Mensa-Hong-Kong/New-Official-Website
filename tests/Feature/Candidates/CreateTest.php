<?php

namespace Tests\Feature\Candidates;

use App\Models\AdmissionTest;
use App\Models\ContactHasVerification;
use App\Models\Member;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->stripe()->create(['id' => 123]);
        $this->test = AdmissionTest::factory()->state(['is_public' => true])->create();
        $contact = UserHasContact::factory()
            ->state([
                'user_id' => $this->user->id,
                'is_default' => true,
            ])->create();
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $this->user->id,
            'creator_ip' => '127.0.0.1',
        ]);
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => 0]
            ),
        );
        $response->assertNotFound();
    }

    public function test_admission_test_is_private()
    {
        $this->test->update(['is_public' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'The admission test is private.']);
    }

    public function test_user_not_exist_stripe_customer()
    {
        $this->user->stripe->delete();
        $response = $this->actingAs($this->user->refresh())->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'We are creating you customer account on stripe, please try again in a few minutes.']);
    }

    public function test_user_already_schedule_this_admission_test()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You has already schedule this admission test.']);
    }

    public function test_user_already_member()
    {
        Member::create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expired_on' => now()->endOfYear(),
            'actual_expired_on' => now()->addYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You has already been member.']);
    }

    public function test_user_is_inactive_member()
    {
        Member::create([
            'user_id' => $this->user->id,
            'expired_on' => now()->subYears(2)->endOfYear(),
            'actual_expired_on' => now()->subYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You has already been qualification for membership.']);
    }

    public function test_user_passed_admission_test()
    {
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month)->addDay(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id, [
            'is_present' => 1,
            'is_pass' => 1,
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You has already been qualification for membership.']);
    }

    public function test_user_of_passport_has_already_been_qualification_for_membership()
    {
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        Member::create([
            'user_id' => $user->id,
            'expired_on' => now()->endOfYear(),
            'actual_expired_on' => now()->addYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'Your passport has already been qualification for membership.']);
    }

    public function test_user_has_other_same_passport_user_account_tested()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month)->subDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month)->subDay(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $oldTest->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You other same passport user account tested.']);
    }

    public function test_user_has_already_been_taken_within_6_months()
    {
        $newTestingAt = now()->addDays(2);
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month)->addDay(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => "You has admission test record within {$this->test->type->interval_month} months(count from testing at of this test sub {$this->test->type->interval_month} months to now)."]);
    }

    public function test_after_than_deadline()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'Cannot register after than before testing date two days.']);
    }

    public function test_admission_test_is_fulled()
    {
        $user = User::factory()->create();
        $this->test->update(['maximum_candidates' => 1]);
        $this->test->candidates()->attach($user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'The admission test is fulled.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
    }
}
