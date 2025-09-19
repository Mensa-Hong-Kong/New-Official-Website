<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders;

use App\Jobs\Orders\RemoveExpiredOrderReservedAdmissionTest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\Member;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'price' => 400,
        'quota' => 1,
        'status' => 'succeeded',
        'payment_gateway_id' => 1,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test Order']);
        $this->happyCase['user_id'] = $this->user->id;
    }


    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test Order')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_user_id()
    {
        $data = $this->happyCase;
        unset($data['user_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The user id field is required.']);
    }

    public function test_user_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['user_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The user id field must be an integer.']);
    }

    public function test_user_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['user_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user id is invalid.']);
    }

    public function test_user_id_of_user_have_no_any_default_contact()
    {
        UserHasContact::first()->delete();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            [
                'user_id' => $this->user->id,
                'function' => 'schedule',
            ]
        );
        $response->assertInvalid(['user_id' => 'The selected user must at least has default contact.']);
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
            route('admin.admission-test.orders.store'),
            $this->happyCase
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
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already qualification for membership.']);
    }

    public function test_user_id_has_other_same_passport_user_account_tested()
    {
        $testingAt = now()->subSecond();
        $test = AdmissionTest::factory()
            ->state([
                'testing_at' => $testingAt,
                'expect_end_at' => $testingAt->addHour(),
            ])->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $test->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user id has other same passport user account tested.']);
    }

    public function test_product_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['product_name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['product_name' => 'The product name field must be a string.']);
    }

    public function test_product_name_too_long()
    {
        $data = $this->happyCase;
        $data['product_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['product_name' => 'The product name field must not be greater than 255 characters.']);
    }

    public function test_price_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['price_name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must be a string.']);
    }

    public function test_price_name_too_long()
    {
        $data = $this->happyCase;
        $data['price_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must not be greater than 255 characters.']);
    }

    public function test_missing_price()
    {
        $data = $this->happyCase;
        unset($data['price']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field is required.']);
    }

    public function test_price_is_not_integer()
    {
        $data = $this->happyCase;
        $data['price'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be an integer.']);
    }

    public function test_price_less_that_1()
    {
        $data = $this->happyCase;
        $data['price'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be at least 1.']);
    }

    public function test_price_greater_than_65535()
    {
        $data = $this->happyCase;
        $data['price'] = 65536;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must not be greater than 65535.']);
    }

    public function test_missing_quota()
    {
        $data = $this->happyCase;
        unset($data['quota']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field is required.']);
    }

    public function test_quota_is_not_integer()
    {
        $data = $this->happyCase;
        $data['quota'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be an integer.']);
    }

    public function test_quota_less_than_1()
    {
        $data = $this->happyCase;
        $data['quota'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be at least 1.']);
    }

    public function test_quota_greater_than_255()
    {
        $data = $this->happyCase;
        $data['quota'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must not be greater than 255.']);
    }

    public function test_missing_status()
    {
        $data = $this->happyCase;
        unset($data['status']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The status field is required.']);
    }

    public function test_status_is_not_string()
    {
        $data = $this->happyCase;
        $data['status'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The status field must be a string.']);
    }

    public function test_status_is_not_in_list()
    {
        $data = $this->happyCase;
        $data['status'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The selected status is invalid.']);
    }

    public function test_missing_expired_at_where_status_equal_pending()
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field is required when status is pending.']);
    }

    public function test_expired_at_is_not_date_where_status_equal_pending()
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at is not a valid date.']);
    }

    public function test_expired_at_before_than_after_5_minutes()
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(4)->endOfMinute();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field must be a date after or equal to 5 minutes.']);
    }

    public function test_expired_at_after_than_after_24_hours()
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(4)->endOfMinute();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field must be a date before or equal to 24 hours.']);
    }

    public function test_missing_payment_gateway_id()
    {
        $data = $this->happyCase;
        unset($data['payment_gateway_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The payment gateway field is required.']);
    }

    public function test_payment_gateway_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['payment_gateway_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The payment gateway field must be an integer.']);
    }

    public function test_payment_gateway_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['payment_gateway_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The selected payment gateway is invalid.']);
    }

    public function test_reference_number_is_not_string()
    {
        $data = $this->happyCase;
        $data['reference_number'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['reference_number' => 'The reference number field must be a string.']);
    }

    public function test_reference_number_too_long()
    {
        $data = $this->happyCase;
        $data['reference_number'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['reference_number' => 'The reference number field must not be greater than 255 characters.']);
    }

    public function test_test_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['test_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The test id field must be an integer.']);
    }

    public function test_test_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['test_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The selected test is invalid, may be the test is not exist or the test has been delete, The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_test_is_full()
    {
        $data = $this->happyCase;
        $test = AdmissionTest::factory()
            ->state(['maximum_candidates' => 1])
            ->create();
        $user = User::factory()->create();
        $test->candidates()->attach($user->id);
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_user_id_has_already_been_taken_within_latest_test_interval_months()
    {
        $newTestingAt = now()->addDay();
        $test = AdmissionTest::factory()
            ->state([
                'testing_at' => $newTestingAt,
                'expect_end_at' => $newTestingAt->addHour(),
            ])->create();
        $oldTest = AdmissionTest::factory()
            ->state([
                'testing_at' => $test->testing_at->subMonths($test->type->interval_month)->addDay(),
                'expect_end_at' => $test->expect_end_at->subMonths($test->type->interval_month)->addDay(),
            ])->create();
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => "The selected user id has admission test record within {$test->type->interval_month} months(count from testing at of this test sub {$test->type->interval_month} months to now)."]);
    }

    public function test_happy_case_when_status_is_pending_without_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(5);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        $this->assertEquals($data['expired_at'], AdmissionTestOrder::first()->expired_at);
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_status_is_succeeded_and_without_expired_at_and_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_status_is_succeeded_with_expired_at_and_with_without_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['expired_at'] = now()->addMinutes(5);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        $this->assertNotEquals($data['expired_at'], AdmissionTestOrder::first()->expired_at);
        $this->assertEquals(now(), AdmissionTestOrder::first()->expired_at);
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_status_is_pending_with_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(5);
        $test = AdmissionTest::factory()
            ->create();
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        $order = AdmissionTestOrder::first();
        $this->assertEquals($data['expired_at'], $order->expired_at);
        $this->assertEquals(1, $test->candidates()->where('order_id', $order->id)->count());
        Queue::assertPushed(RemoveExpiredOrderReservedAdmissionTest::class);
        
    }

    public function test_happy_case_when_status_is_succeeded_and_without_expired_at_with_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $test = AdmissionTest::factory()
            ->create();
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        $this->assertEquals(1, $test->candidates()->where('order_id', AdmissionTestOrder::first()->id)->count());
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_status_is_succeeded_with_expired_at_and_test()
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['expired_at'] = now()->addMinutes(5);
        $test = AdmissionTest::factory()
            ->create();
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertRedirectToRoute('admin.index');
        $order = AdmissionTestOrder::first();
        $this->assertNotEquals($data['expired_at'], $order->expired_at);
        $this->assertEquals(now(), AdmissionTestOrder::first()->expired_at);
        $this->assertEquals(1, $test->candidates()->where('order_id', $order->id)->count());
        Queue::assertNothingPushed();
    }
}
