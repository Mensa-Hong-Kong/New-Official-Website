<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders;

use App\Jobs\Orders\AdmissionTestOrderExpiredHandle;
use App\Library\Stripe\Amount;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\AdmissionTestType;
use App\Models\ContactHasVerification;
use App\Models\Member;
use App\Models\MembershipOrder;
use App\Models\ModulePermission;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $happyCase = [
        'quota' => 1,
        'status' => 'succeeded',
        'payment_gateway_id' => 1,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test Order']);
        $this->happyCase['user_id'] = $this->user->id;
        $this->happyCase['price'] = 1 * 10 ** (-Amount::getValidationDecimal());
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
    }

    public function test_have_no_login(): void
    {
        $response = $this->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission(): void
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

    public function test_missing_user_id(): void
    {
        $data = $this->happyCase;
        unset($data['user_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The user id field is required.']);
    }

    public function test_user_id_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['user_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The user id field must be an integer.']);
    }

    public function test_user_id_is_not_exist(): void
    {
        $data = $this->happyCase;
        $data['user_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user id is invalid.']);
    }

    public function test_user_id_of_user_age_less_than_minimum_age(): void
    {
        $this->user->update(['birthday' => now()->subYears(22)->addDay()]);
        $data = $this->happyCase;
        $data['minimum_age'] = 22;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user age less than minimum age limit.']);
    }

    public function test_user_id_of_user_age_greater_than_maximum_age(): void
    {
        $this->user->update(['birthday' => now()->subYears(22)]);
        $data = $this->happyCase;
        $data['maximum_age'] = 22;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user age greater than maximum age limit.']);
    }

    public function test_with_test_id_and_user_id_of_user_have_no_any_default_contact(): void
    {
        $test = AdmissionTest::factory()->create();
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        UserHasContact::first()->delete();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user must at least has one default contact.']);
    }

    public function test_user_id_has_already_member(): void
    {
        $member = Member::create([
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
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user id has already member.']);
    }

    public function test_user_id_has_already_qualification_for_membership(): void
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

    public function test_user_id_of_user_has_future_admission_test(): void
    {
        $test1 = AdmissionTest::factory()->create();
        $test1->candidates()->attach($this->user->id);
        $test2 = AdmissionTest::factory()->create();
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => 'The selected user id has been scheduled admission test.']);
    }

    public function test_user_id_of_user_has_unused_quota_when_quota_validity_months_is_null(): void
    {
        AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'created_at' => '1970-01-01 00:00:01',
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user has unused quota.']);
    }

    public function test_user_id_of_user_has_unused_quota_when_quota_within_quota_validity_months(): void
    {
        AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'quota_validity_months' => 1,
            'created_at' => now()->subMonth()->addSecond(),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user has unused quota.']);
    }

    public function test_user_id_of_user_has_unused_quota_and_has_tested_record_order_when_unused_quota_within_quota_validity_months(): void
    {
        $order = AdmissionTestOrder::factory()->create([
            'quota' => 2,
            'quota_validity_months' => 1,
            'status' => 'succeeded',
            'created_at' => now()->subMonths(3)->addDay(),
        ]);
        AdmissionTestType::factory()->create(['interval_month' => 1]);
        $test = AdmissionTest::factory()->create(['testing_at' => now()->subMonths(2)->addDay()]);
        $test->candidates()->attach(
            $this->user->id,
            [
                'is_present' => true,
                'order_id' => $order->id,
            ]
        );
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user has unused quota.']);
    }

    public function test_user_id_of_user_has_unused_quota_when_have_no_attended_record(): void
    {
        AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'created_at' => now()->subMonths(),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user has unused quota.']);
    }

    public function test_user_id_of_user_has_other_same_passport_user_already_membership_qualification(): void
    {
        $user = User::factory()->create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
        ]);
        Member::create([
            'user_id' => $user->id,
            'expired_on' => now()->subYears(2)->endOfYear(),
            'actual_expired_on' => now()->subYear()->startOfYear()->addDays(21),
        ]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user id has other same passport user account already been qualification for membership.']);
    }

    public function test_user_id_has_other_same_passport_user_account_tested(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->subSecond(),
            'expect_end_at' => now()->subSecond()->addHour(),
        ]);
        $user = User::factory()->create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
        ]);
        $test->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $this->happyCase
        );
        $response->assertInvalid(['user_id' => 'The selected user id has other same passport user account attended admission test.']);
    }

    public function test_product_name_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['product_name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['product_name' => 'The product name field must be a string.']);
    }

    public function test_product_name_too_long(): void
    {
        $data = $this->happyCase;
        $data['product_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['product_name' => 'The product name field must not be greater than 255 characters.']);
    }

    public function test_price_name_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['price_name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must be a string.']);
    }

    public function test_price_name_too_long(): void
    {
        $data = $this->happyCase;
        $data['price_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must not be greater than 255 characters.']);
    }

    public function test_missing_price(): void
    {
        $data = $this->happyCase;
        unset($data['price']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field is required.']);
    }

    public function test_price_is_not_numeric(): void
    {
        $data = $this->happyCase;
        $data['price'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        if (Amount::getActualDecimal() > 0) {
            $response->assertInvalid(['price' => 'The price field must be a number.']);
        } else {
            $response->assertInvalid(['price' => 'The price field must be an integer.']);
        }
    }

    public function test_price_less_that_minimum_limit(): void
    {
        $data = $this->happyCase;
        $minimum = 1 * 10 ** (-Amount::getValidationDecimal());
        $data['price'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => "The price field must be at least $minimum."]);
    }

    public function test_price_greater_than_maximum_limit(): void
    {
        $data = $this->happyCase;
        $maximum = Amount::getMaximumValidation();
        $data['price'] = $maximum + 1 * 10 ** (-Amount::getActualDecimal());
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['price' => "The price field must not be greater than $maximum."]);
    }

    public function test_minimum_age_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be an integer.']);
    }

    public function test_minimum_age_less_than_1(): void
    {
        $data = $this->happyCase;
        $data['minimum_age'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be at least 1.']);
    }

    public function test_minimum_age_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must not be greater than 255.']);
    }

    public function test_minimum_age_greater_than_maximum_age(): void
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 14;
        $data['maximum_age'] = 13;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid([
            'minimum_age' => 'The minimum age field must be less than maximum age field.',
            'maximum_age' => 'The maximum age field must be greater than minimum age field.',
        ]);
    }

    public function test_maximum_age_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be an integer.']);
    }

    public function test_maximum_age_less_than_1(): void
    {
        $data = $this->happyCase;
        $data['maximum_age'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be at least 1.']);
    }

    public function test_maximum_age_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must not be greater than 255.']);
    }

    public function test_missing_quota(): void
    {
        $data = $this->happyCase;
        unset($data['quota']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field is required.']);
    }

    public function test_quota_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['quota'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be an integer.']);
    }

    public function test_quota_less_than_1(): void
    {
        $data = $this->happyCase;
        $data['quota'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be at least 1.']);
    }

    public function test_quota_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['quota'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must not be greater than 255.']);
    }

    public function test_quota_validity_months_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must be an integer.']);
    }

    public function test_quota_validity_months_less_than_0(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must be at least 0.']);
    }

    public function test_quota_validity_months_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must not be greater than 255.']);
    }

    public function test_missing_status(): void
    {
        $data = $this->happyCase;
        unset($data['status']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The status field is required.']);
    }

    public function test_status_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['status'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The status field must be a string.']);
    }

    public function test_status_is_not_in_list(): void
    {
        $data = $this->happyCase;
        $data['status'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['status' => 'The selected status is invalid.']);
    }

    public function test_missing_expired_at_where_status_equal_pending(): void
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field is required when status is pending.']);
    }

    public function test_expired_at_is_not_date_where_status_equal_pending(): void
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field must be a valid date.']);
    }

    public function test_expired_at_before_than_after_5_minutes(): void
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(4)->format('Y-m-d H:i');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field must be a date after or equal to 5 minutes.']);
    }

    public function test_expired_at_after_than_after_2_hours(): void
    {
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addHours(2)->addMinute()->format('Y-m-d H:i');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['expired_at' => 'The expired at field must be a date before or equal to 2 hours.']);
    }

    public function test_missing_payment_gateway_id(): void
    {
        $data = $this->happyCase;
        unset($data['payment_gateway_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The payment gateway field is required.']);
    }

    public function test_payment_gateway_id_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['payment_gateway_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The payment gateway id field must be an integer.']);
    }

    public function test_payment_gateway_id_is_not_exist(): void
    {
        $data = $this->happyCase;
        $data['payment_gateway_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['payment_gateway_id' => 'The selected payment gateway is invalid.']);
    }

    public function test_reference_number_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['reference_number'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['reference_number' => 'The reference number field must be a string.']);
    }

    public function test_reference_number_too_long(): void
    {
        $data = $this->happyCase;
        $data['reference_number'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['reference_number' => 'The reference number field must not be greater than 255 characters.']);
    }

    public function test_test_id_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['test_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The test id field must be an integer.']);
    }

    public function test_test_id_is_not_exist(): void
    {
        $data = $this->happyCase;
        $data['test_id'] = '0';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The selected test is invalid, may be the test is not exist or the test has been delete, The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_test_is_free(): void
    {
        $data = $this->happyCase;
        $test = AdmissionTest::factory()->create(['is_free' => true]);
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The admission test order cannot select free admission test.']);
    }

    public function test_test_is_full(): void
    {
        $data = $this->happyCase;
        $test = AdmissionTest::factory()->create(['maximum_candidates' => 1]);
        $user = User::factory()->create();
        $test->candidates()->attach($user->id);
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.']);
    }

    public function test_user_id_has_already_been_taken_within_latest_test_interval_months(): void
    {
        $newTestingAt = now()->addDay();
        $test = AdmissionTest::factory()->create([
            'testing_at' => $newTestingAt,
            'expect_end_at' => $newTestingAt->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => (clone $test->testing_at)->subMonths($test->type->interval_month)->addDay(),
            'expect_end_at' => (clone $test->expect_end_at)->subMonths($test->type->interval_month)->addDay(),
        ]);
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['user_id' => "The selected user id has admission test record within {$test->type->interval_month} months(count from testing at of this test sub {$test->type->interval_month} months to now)."]);
    }

    public function test_user_id_of_user_age_less_than_test_minimum_age(): void
    {
        $test = AdmissionTest::factory()->create();
        $test->type->update(['minimum_age' => 10]);
        $this->user->update(['birthday' => (clone $test->testing_at)->subYears(10)->addDays(2)]);
        $this->assertTrue($this->user->age < 22);
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The selected user age less than test minimum age limit.']);
    }

    public function test_user_id_of_user_age_greater_than_test_maximum_age(): void
    {
        $test = AdmissionTest::factory()->create();
        $test->type->update(['maximum_age' => 9]);
        $this->user->update(['birthday' => (clone $test->testing_at)->subYears(10)]);
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $response->assertInvalid(['test_id' => 'The selected user age greater than test maximum age limit.']);
    }

    public function test_happy_case_when_status_is_pending_and_without_all_optional_fields_and_user_has_no_unused_quota(): void
    {
        Notification::fake();
        Queue::fake();
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(5)->format('Y-m-d H:i');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
        $this->assertEquals($data['expired_at'], AdmissionTestOrder::first()->expired_at->format('Y-m-d H:i'));
        Queue::assertPushed(AdmissionTestOrderExpiredHandle::class);
        Notification::assertNothingSent();
        $order = AdmissionTestOrder::latest('id')->first();
        $this->assertNull($order->lastTest);
        $this->assertNull($order->minimum_age);
        $this->assertNull($order->maximum_age);
        $this->assertNull($order->quota_validity_months);
    }

    public function test_happy_case_when_status_is_succeeded_and_without_all_optional_fields_and_user_has_no_unused_quota(): void
    {
        Notification::fake();
        Queue::fake();
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::latest('id')->first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
        Queue::assertNothingPushed();
        Notification::assertNothingSent();
        $this->assertNull($order->lastTest);
        $this->assertNull($order->minimum_age);
        $this->assertNull($order->maximum_age);
        $this->assertNull($order->quota_validity_months);
    }

    public function test_happy_case_when_status_is_pending_and_with_all_optional_fields_and_user_has_expired_unused_quota(): void
    {
        Notification::fake();
        Queue::fake();
        AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'quota_validity_months' => 1,
            'created_at' => now()->subMonths(2),
        ]);
        $test = AdmissionTest::factory()->create();
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['test_id'] = $test->id;
        $data['expired_at'] = now()->addMinutes(5)->format('Y-m-d H:i');
        $data['minimum_age'] = 10;
        $data['maximum_age'] = 17;
        $data['quota_validity_months'] = 1;
        $this->user->update(['birthday' => (clone $test->testing_at)->subYears(15)]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::latest('id')->first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
        Queue::assertPushed(AdmissionTestOrderExpiredHandle::class);
        Notification::assertNothingSent();
        $this->assertEquals($data['expired_at'], $order->expired_at->format('Y-m-d H:i'));
        $this->assertEquals($test->id, $order->lastTest->id);
        $this->assertEquals($data['minimum_age'], $order->minimum_age);
        $this->assertEquals($data['maximum_age'], $order->maximum_age);
        $this->assertEquals($data['quota_validity_months'], $order->quota_validity_months);
    }

    public function test_happy_case_when_status_is_succeeded_and_with_all_optional_fields_and_user_has_expired_unused_quota(): void
    {
        Notification::fake();
        Queue::fake();
        AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'quota_validity_months' => 1,
            'created_at' => now()->subMonths(2),
        ]);
        $test = AdmissionTest::factory()->create();
        $data = $this->happyCase;
        $data['test_id'] = $test->id;
        $data['minimum_age'] = 10;
        $data['maximum_age'] = 17;
        $data['quota_validity_months'] = 1;
        $this->user->update(['birthday' => (clone $test->testing_at)->subYears(15)]);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::latest('id')->first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
        Queue::assertNothingPushed();
        Notification::assertSentTo([$this->user], ScheduleAdmissionTest::class);
        $this->assertEquals($test->id, $order->lastTest->id);
        $this->assertEquals($data['minimum_age'], $order->minimum_age);
        $this->assertEquals($data['maximum_age'], $order->maximum_age);
        $this->assertEquals($data['quota_validity_months'], $order->quota_validity_months);
    }

    public function test_happy_case_when_status_is_succeeded_and_with_expired_at(): void
    {
        Notification::fake();
        Queue::fake();
        $data = $this->happyCase;
        $data['expired_at'] = now()->addMinutes(5)->format('Y-m-d H:i');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::latest('id')->first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
        Queue::assertNothingPushed();
        Notification::assertNothingSent();
        $this->assertNotEquals($data['expired_at'], $order->expired_at->format('Y-m-d H:i'));
        $this->assertLessThanOrEqual(now(), $order->expired_at);
    }

    public function test_happy_case_when_with_test_id_and_the_test_with_minimum_and_maximum_age_and_user_age_within_the_limit(): void
    {
        Queue::fake();
        $test = AdmissionTest::factory()->create();
        $test->type->update(['minimum_age' => 10, 'maximum_age' => 17]);
        $this->user->update(['birthday' => (clone $test->testing_at)->subYears(15)]);
        $data = $this->happyCase;
        $data['status'] = 'pending';
        $data['expired_at'] = now()->addMinutes(5)->format('Y-m-d H:i');
        $data['test_id'] = $test->id;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.orders.store'),
            $data
        );
        $order = AdmissionTestOrder::latest('id')->first();
        $response->assertRedirectToRoute(
            'admin.admission-test.orders.show',
            ['order' => $order]
        );
    }
}
