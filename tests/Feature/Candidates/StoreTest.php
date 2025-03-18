<?php

namespace Tests\Feature\Candidates;

use App\Models\AdmissionTest;
use App\Models\ContactHasVerification;
use App\Models\Member;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\RescheduleAdmissionTest;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test', 'View:User']);
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
        $response = $this->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => 0]
            ),
        );
        $response->assertNotFound();
    }

    public function test_admission_test_is_private()
    {
        $this->test->update(['is_public' => false]);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'The admission test is private.']);
    }

    public function test_user_already_schedule_this_admission_test()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You has already been qualification for membership.']);
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
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
                'testing_at' => $this->test->testing_at->subMonths(6)->subDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths(6)->subDay(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $oldTest->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You other same passport user account tested.']);
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You has admission test record within 6 months(count from testing at of this test sub 6 months to now).']);
    }

    public function test_user_id_of_user_have_no_any_default_contact()
    {
        UserHasContact::first()->delete();
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'You must at least has default contact.']);
    }

    public function test_after_than_deadline()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
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
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSessionHasErrors(['message' => 'The admission test is fulled.']);
    }

    public function test_schedule_happy_case_when_have_no_other_same_passport()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHas('success', 'Your schedule request successfully, the ticket will be to your default contact(s).');
        Notification::assertSentTo(
            [$this->user], ScheduleAdmissionTest::class
        );
    }

    public function test_schedule_happy_case_when_has_other_same_passport()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $this->test->candidates()->attach($user->id);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHas('success', 'Your schedule request successfully, the ticket will be to your default contact(s).');
        Notification::assertSentTo(
            [$this->user], ScheduleAdmissionTest::class
        );
    }

    public function test_reschedule_happy_case_when_have_no_other_same_passport_user()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $newTestingAt = now()->addDays(3);
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $newTestingAt = now()->addDay();
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $newTestingAt,
                'expect_end_at' => $newTestingAt->addHour(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHas('success', 'Your reschedule request successfully, the new ticket will be to your default contact(s).');
        $this->assertEquals(0, $oldTest->candidates()->count());
        Notification::assertSentTo(
            [$this->user], RescheduleAdmissionTest::class
        );
    }

    public function test_reschedule_happy_case_when_has_other_same_passport_user()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $newTestingAt = now()->addDays(3);
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $newTestingAt = now()->addDay();
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $newTestingAt,
                'expect_end_at' => $newTestingAt->addHour(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id);
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $this->test->candidates()->attach($user->id);
        $response = $this->actingAs($this->user)->post(
            route(
                'admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHas('success', 'Your reschedule request successfully, the new ticket will be to your default contact(s).');
        $this->assertEquals(0, $oldTest->candidates()->count());
        Notification::assertSentTo(
            [$this->user], RescheduleAdmissionTest::class
        );
    }
}
