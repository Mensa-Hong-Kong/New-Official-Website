<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\ContactHasVerification;
use App\Models\Member;
use App\Models\MembershipOrder;
use App\Models\ModulePermission;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\AssignAdmissionTest;
use App\Notifications\AdmissionTest\Admin\RescheduleAdmissionTest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var User&Authenticatable */
    private User $user;

    private AdmissionTest $test;

    private AdmissionTestOrder $order;

    private array $happyCase = [
        'function' => 'schedule',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'Edit:Admission Test Order',
            'Edit:Admission Test Candidate',
        ]);
        $contact = UserHasContact::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $this->user->id,
            'creator_ip' => '127.0.0.1',
        ]);
        $this->order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
        ]);
        $this->test = AdmissionTest::factory()->create();
        $this->happyCase['test_id'] = $this->test->id;
    }

    public function test_have_no_login(): void
    {
        $response = $this->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_order_permission(): void
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Order');
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_have_no_edit_admission_test_candidate_permission(): void
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Candidate');
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_has_no_edit_admission_test_order_and_candidate_permission(): void
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNotIn(
                    'name',
                    [
                        'Edit:Admission Test Order',
                        'Edit:Admission Test Candidate',
                    ]
                )->first()
                ->name
        );
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_admission_test_order_is_not_exist(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => 0]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_bypass_expiration_date_checking_is_not_boolean(): void
    {
        $data = $this->happyCase;
        $data['bypass_expiration_date_checking'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['bypass_expiration_date_checking' => 'The bypass expiration date checking field must be true or false.']);
    }

    public function test_missing_function(): void
    {
        $data = $this->happyCase;
        $data['function'] = ['schedule'];
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['function' => 'The function field must be a string.']);
    }

    public function test_function_is_invalid(): void
    {
        $data = $this->happyCase;
        $data['function'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['function' => 'The function field does not exist in schedule, reschedule.']);
    }

    public function test_schedule_function_but_user_already_been_schedule_other_admission(): void
    {
        AdmissionTest::factory()->create()->candidates()->attach($this->order->user_id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['function' => 'The order of user has already been scheduled other admission test.']);
    }

    public function test_reschedule_function_but_user_have_no_scheduled_any_admission_test()
    {
        $data = $this->happyCase;
        $data['function'] = 'reschedule';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['function' => 'The order of user have no scheduled other admission test.']);
    }

    public function test_reschedule_function_but_after_than_old_test_testing_time_before_2_hours(): void
    {
        $testingAt = now()->addHours(2)->subSecond();
        AdmissionTest::factory()->create([
            'testing_at' => $testingAt,
            'expect_end_at' => (clone $testingAt)->addHour(),
        ])->candidates()->attach($this->order->user_id);
        $data = $this->happyCase;
        $data['function'] = 'reschedule';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['function' => 'The order of user id scheduled other admission test and after than before testing time 2 hours, please wait proctor to confirm the user is absent first.']);
    }

    public function test_missing_test_id(): void
    {
        $data = $this->happyCase;
        unset($data['test_id']);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['test_id' => 'The test id field is required.']);
    }

    public function test_test_id_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['test_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['test_id' => 'The test id field must be an integer.']);
    }

    public function test_test_id_is_not_exist(): void
    {
        $data = $this->happyCase;
        $data['test_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertInvalid(['test_id' => 'The selected test is invalid, may be the test is not exist or the test has been delete, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_selected_test_is_free()
    {
        $this->test->update(['is_free' => true]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['test_id' => 'The admission test order cannot select free admission test.']);
    }

    public function test_selected_admission_test_has_been_fulled()
    {
        $this->test->update(['maximum_candidates' => 1]);
        $this->test->candidates()->attach(User::factory()->create()->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['test_id' => 'The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_order_of_user_age_under_admission_test_minimum_age()
    {
        $this->test->type->update(['minimum_age' => 10]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(9)]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['test_id' => 'The order of user age less than selected test minimum age limit.']);
    }

    public function test_order_of_user_age_under_admission_test_maximum_age()
    {
        $this->test->type->update(['maximum_age' => 9]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['test_id' => 'The order of user age greater than selected test maximum age limit.']);
    }

    public function test_order_of_user_already_scheduled_this_test()
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['test_id' => 'The order of user has already been scheduled for the selected admission test.']);
    }

    public function test_order_have_no_unused_quota()
    {
        $this->order->update(['quota' => 1]);
        $testingAt = now()->subMonths(2);
        $test = AdmissionTest::factory()->create([
            'testing_at' => $testingAt,
            'expect_end_at' => (clone $testingAt)->addHour(),
        ]);
        $test->candidates()->attach(
            $this->user->id,
            [
                'order_id' => $this->order->id,
                'is_present' => true,
            ]
        );
        $test->type->update(['interval_month' => 1]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order have no unused quota.']);
    }

    public function test_order_quota_expired_and_have_no_select_bypass_expiration_date_checking()
    {
        $this->order->update([
            'quota_validity_months' => 1,
            'create_at' => now()->subMonths(2),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order admission test quota expired before the testing time of selected admission test, please select other admission test or bypass expiration date checking.']);
    }

    public function test_order_of_user_has_already_been_taken_within_latest_test_interval_months()
    {
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => (clone $this->test->testing_at)->subMonths($this->test->type->interval_month)->addDay(),
            'expect_end_at' => (clone $this->test->expect_end_at)->subMonths($this->test->type->interval_month)->addDay(),
        ]);
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => "The order of user has admission test record within {$oldTest->type->interval_month} months(count from testing at of this test sub {$oldTest->type->interval_month} months to now)."]);
    }

    public function test_user_of_order_have_no_default_contact()
    {
        UserHasContact::first()->delete();
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order of user must at least has one default contact.']);
    }

    public function test_order_of_user_has_already_been_member()
    {
        Member::create([
            'user_id' => $this->user->id,
        ]);
        $thisYear = now()->year;
        MembershipOrder::create([
            'user_id' => $this->user->id,
            'price' => 200,
            'status' => 'succeeded',
            'from_year' => $thisYear,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order of user has already been member.']);
    }

    public function test_order_of_user_has_already_qualification_for_membership()
    {
        Member::create([
            'user_id' => $this->user->id,
            'expired_on' => now()->subYears(2)->endOfYear(),
            'actual_expired_on' => now()->subYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order of user has already been qualification for membership.']);
    }

    public function test_order_of_user_has_other_same_passport_user_account_tested()
    {
        $user = User::factory()->create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
        ]);
        AdmissionTest::factory()->create([
            'testing_at' => now()->subSecond(),
            'expect_end_at' => now()->subSecond()->addHour(),
        ])->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertInvalid(['failed' => 'The order of user has other same passport user account attended admission test.']);
    }

    public function test_schedule_happy_case_when_have_no_bypass_expiration_date_checking()
    {
        Notification::fake();
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $this->happyCase
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], AssignAdmissionTest::class);
    }

    public function test_reschedule_happy_case_when_have_no_bypass_expiration_date_checking()
    {
        Notification::fake();
        AdmissionTest::factory()->create()->candidates()->attach($this->user->id);
        $data = $this->happyCase;
        $data['function'] = 'reschedule';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], RescheduleAdmissionTest::class);
    }

    public function test_schedule_happy_case_when_bypass_expiration_date_checking_and_quota_has_no_expired()
    {
        Notification::fake();
        $data = $this->happyCase;
        $data['bypass_expiration_date_checking'] = true;
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], AssignAdmissionTest::class);
    }

    public function test_reschedule_happy_case_when_bypass_expiration_date_checking_and_quota_has_no_expired()
    {
        Notification::fake();
        AdmissionTest::factory()->create()->candidates()->attach($this->user->id);
        $data = $this->happyCase;
        $data['bypass_expiration_date_checking'] = true;
        $data['function'] = 'reschedule';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], RescheduleAdmissionTest::class);
    }

    public function test_schedule_happy_case_when_bypass_expiration_date_checking_and_quota_expired()
    {
        Notification::fake();
        $this->order->update([
            'quota_validity_months' => 1,
            'create_at' => now()->subMonths(2),
        ]);
        $data = $this->happyCase;
        $data['bypass_expiration_date_checking'] = true;
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], AssignAdmissionTest::class);
    }

    public function test_reschedule_happy_case_when_bypass_expiration_date_checking_and_quota_expired()
    {
        Notification::fake();
        $this->order->update([
            'quota_validity_months' => 1,
            'create_at' => now()->subMonths(2),
        ]);
        AdmissionTest::factory()->create()->candidates()->attach($this->user->id);
        $data = $this->happyCase;
        $data['bypass_expiration_date_checking'] = true;
        $data['function'] = 'reschedule';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.orders.admission-tests.store',
                ['order' => $this->order]
            ),
            $data
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The candidate create success',
            'id' => $this->test->id,
            'type' => $this->test->type->name,
            'testing_at' => $this->test->testing_at->toJSON(),
            'location' => $this->test->location->name,
        ]);
        Notification::assertSentTo([$this->user], RescheduleAdmissionTest::class);
    }
}
