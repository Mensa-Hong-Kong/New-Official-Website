<?php

namespace Tests\Feature\Candidates;

use App\Models\AdmissionTest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->test = AdmissionTest::factory()->state(['is_public' => true])->create();
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => 0]
            ),
        );
        $response->assertNotFound();
    }

    public function test_user_have_no_register_this_admission_test_and_the_test_is_private()
    {
        $this->test->update(['is_public' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and this test is private, please register other admission test.']);
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
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and you has already been member.']);
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
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and you has already been qualification for membership.']);
    }

    public function test_user_passed_admission_test()
    {
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths(6)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths(6)->addDay(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id, [
            'is_present' => 1,
            'is_pass' => 1,
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and you has already been qualification for membership.']);
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
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and your passport has already been qualification for membership.']);
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
                'testing_at' => $this->test->testing_at->subMonths(6)->subDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths(6)->subDay(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $oldTest->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and your passport has other same passport user account tested.']);
    }

    public function test_user_has_already_been_taken_within_6_months()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths(6)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths(6)->addDay(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and You has admission test record within 6 months(count from testing at of this test sub 6 months to now).']);
    }

    public function test_user_have_no_register_this_admission_test_and_after_than_register_deadline()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and cannot register after than before testing date two days, please register other admission test.']);
    }

    public function test_user_have_no_register_this_admission_test_and_the_test_not_fulled()
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.candidates.create', ['admission_test' => $this->test]);
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test, please register first.']);
    }

    public function test_user_have_no_register_this_admission_test_and_the_test_fulled()
    {
        $this->test->update(['maximum_candidates' => 0]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have no register this admission test and this test is fulled, please register other admission test.']);
    }

    public function test_happy_case()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.show',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
    }
}
