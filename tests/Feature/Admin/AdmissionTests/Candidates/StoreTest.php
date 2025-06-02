<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\ContactHasVerification;
use App\Models\Member;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\AssignAdmissionTest;
use App\Notifications\AdmissionTest\Admin\RescheduleAdmissionTest;
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
        $this->test = AdmissionTest::factory()->create();
        $this->test->type->update(['interval_month' => 6]);
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
        $response = $this->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => 0]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertNotFound();
    }

    public function test_not_admission_test_ended_more_than_one_hour()
    {
        $this->test->update(['testing_at' => now()->subSecond()]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertGone();
        $response->assertJson(['message' => 'Can not add candidate after than testing time.']);
    }

    public function test_missing_user_id()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            ['function' => 'schedule']
        );
        $response->assertInvalid(['user_id' => 'The user id field is required.']);
    }

    public function test_user_id_is_not_integer()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => 'abc',
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The user id field must be an integer.']);
    }

    public function test_user_id_is_exists_candidate_for_this_admission_test()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The user id has already been taken.']);
    }

    public function test_user_id_is_not_exists_on_database()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => 0,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id is invalid.']);
    }

    public function test_missing_function()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertInvalid(['function' => 'The function field is required.']);
    }

    public function test_function_is_not_string()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => ['schedule'],
            ]
        );
        $response->assertInvalid(['function' => 'The function field must be a string.']);
    }

    public function test_function_is_invalid()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'abc',
            ]
        );
        $response->assertInvalid(['function' => 'The function field does not exist in schedule, reschedule.']);
    }

    public function test_user_id_has_already_member()
    {
        Member::create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expired_on' => now()->endOfYear(),
            'actual_expired_on' => now()->addYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'abc',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already member.']);
    }

    public function test_user_id_has_already_qualification_for_membership()
    {
        Member::create([
            'user_id' => $this->user->id,
            'expired_on' => now()->subYears(2)->endOfYear(),
            'actual_expired_on' => now()->subYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already qualification for membership.']);
    }

    public function test_user_id_already_schedule_this_admission_test()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already schedule this admission test.']);
    }

    public function test_schedule_function_but_user_has_other_admission_test_on_future()
    {
        $newTestTestingAt = now()->addDay();
        $test = AdmissionTest::factory()
            ->state([
                'testing_at' => $newTestTestingAt,
                'expect_end_at' => $newTestTestingAt->addHour(),
            ])->create();
        $test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already schedule other admission test.']);
    }

    public function test_reschedule_function_but_user_not_have_no_other_admission_test_on_future()
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'reschedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id have no scheduled other admission test after than now.']);
    }

    public function test_user_id_of_user_of_passport_has_already_been_qualification_for_membership()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month)->addDay(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $oldTest->candidates()->attach($user->id, [
            'is_present' => 1,
            'is_pass' => 1,
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The passport of selected user id has already been qualification for membership.']);
    }

    public function test_user_id_has_other_same_passport_user_account_tested()
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => now()->subSecond(),
                'expect_end_at' => now()->subSecond()->addHour(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $oldTest->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user id has other same passport user account tested.']);
    }

    public function test_user_id_has_already_been_taken_within_latest_test_interval_months()
    {
        $newTestingAt = now()->addDay();
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
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => "The selected user id has admission test record within {$this->test->type->interval_month} months(count from testing at of this test sub {$this->test->type->interval_month} months to now)."]);
    }

    public function test_user_id_of_user_have_no_any_default_contact()
    {
        UserHasContact::first()->delete();
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user must at least has default contact.']);
    }

    public function test_admission_test_is_fulled()
    {
        $user = User::factory()->create();
        $this->test->update(['maximum_candidates' => 1]);
        $this->test->candidates()->attach($user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The admission test is fulled.']);
    }

    public function test_schedule_happy_case_when_have_no_other_same_passport()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'user_id' => $this->user->id,
            'name' => $this->user->adornedName,
            'has_same_passport' => false,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $this->user]
            ),
            'in_testing_time_range' => $this->test->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'result_url' => route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'delete_url' => route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
        ]);
        Notification::assertSentTo(
            [$this->user], AssignAdmissionTest::class
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
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'user_id' => $this->user->id,
            'name' => $this->user->adornedName,
            'has_same_passport' => true,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $this->user]
            ),
            'in_testing_time_range' => $this->test->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'result_url' => route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'delete_url' => route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
        ]);
        Notification::assertSentTo(
            [$this->user], AssignAdmissionTest::class
        );
    }

    public function test_reschedule_happy_case_when_have_no_other_same_passport_user()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $newTestingAt = now()->addDay();
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
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'reschedule',
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'user_id' => $this->user->id,
            'name' => $this->user->adornedName,
            'has_same_passport' => false,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $this->user]
            ),
            'in_testing_time_range' => $this->test->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'result_url' => route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'delete_url' => route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
        ]);
        $this->assertEquals(0, $oldTest->candidates()->count());
        Notification::assertSentTo(
            [$this->user], RescheduleAdmissionTest::class
        );
    }

    public function test_reschedule_happy_case_when_has_other_same_passport_user()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $newTestingAt = now()->addDay();
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
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-tests.candidates.store',
                ['admission_test' => $this->test]
            ),
            [
                'user_id' => $this->user->id,
                'function' => 'reschedule',
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'user_id' => $this->user->id,
            'name' => $this->user->adornedName,
            'has_same_passport' => true,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $this->user]
            ),
            'in_testing_time_range' => $this->test->inTestingTimeRange(),
            'present_url' => route(
                'admin.admission-tests.candidates.present.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'result_url' => route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            'delete_url' => route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
        ]);
        $this->assertEquals(0, $oldTest->candidates()->count());
        Notification::assertSentTo(
            [$this->user], RescheduleAdmissionTest::class
        );
    }
}
