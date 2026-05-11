<?php

namespace Tests\Feature\Candidates;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use App\Models\Member;
use App\Models\MembershipOrder;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private AdmissionTest $test;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->stripe()->create(['id' => 123]);
        $this->test = AdmissionTest::factory()->create([
            'is_free' => true,
            'is_public' => true,
        ]);
    }

    public function test_have_no_login(): void
    {
        $response = $this->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_admission_test_is_not_exist(): void
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => 0]
            ),
        );
        $response->assertNotFound();
    }

    public function test_admission_test_is_private(): void
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

    public function test_user_not_exist_stripe_customer_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        $this->user->stripe->delete();
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user->refresh())->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'We are creating you customer account on stripe, please try again in a few minutes.']);
    }

    public function test_user_scheduled_other_admission_test_testing_time_less_than_before_2_hours(): void
    {
        $newTestingAt = now()->addHours(2);
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => $newTestingAt,
            'expect_end_at' => (clone $newTestingAt)->addHour(),
        ]);
        $oldTest->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You has already schedule other admission test and after than before testing time 2 hours, please wait proctor to confirm the user is absent first.']);
    }

    public function test_user_already_schedule_this_admission_test(): void
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute(
            'admission-tests.candidates.show',
            ['admission_test' => $this->test]
        );
        $response->assertSessionHasErrors(['message' => 'You has already schedule this admission test.']);
    }

    public function test_user_already_member(): void
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
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You has already been member.']);
    }

    public function test_user_is_inactive_member(): void
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You has already been qualification for membership.']);
    }

    public function test_user_passed_admission_test(): void
    {
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => (clone $this->test->testing_at)->subMonths($this->test->type->interval_month)->addDay(),
            'expect_end_at' => (clone $this->test->expect_end_at)->subMonths($this->test->type->interval_month)->addDay(),
        ]);
        $oldTest->candidates()->attach($this->user->id, [
            'is_present' => 1,
            'is_passed' => 1,
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You has already been qualification for membership.']);
    }

    public function test_user_of_passport_has_already_been_qualification_for_membership(): void
    {
        $user = User::factory()->create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
        ]);
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have other same passport user account already been qualification for membership.']);
    }

    public function test_user_has_other_same_passport_user_account_tested(): void
    {
        $newTestingAt = now()->addDays(2);
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => (clone $newTestingAt)->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => (clone $this->test->testing_at)->subMonths($this->test->type->interval_month)->subDay(),
            'expect_end_at' => (clone $this->test->expect_end_at)->subMonths($this->test->type->interval_month)->subDay(),
        ]);
        $user = User::factory()->create([
            'passport_type_id' => $this->user->passport_type_id,
            'passport_number' => $this->user->passport_number,
        ]);
        $oldTest->candidates()->attach($user->id, ['is_present' => 1]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You have other same passport user account attended other admission test, if you forgot account, please contact us.']);
    }

    public function test_user_has_already_been_taken_within_interval_months(): void
    {
        $newTestingAt = now()->addDays(2);
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => (clone $newTestingAt)->addHour(),
        ]);
        $oldTest = AdmissionTest::factory()->create([
            'testing_at' => (clone $this->test->testing_at)->subMonths($this->test->type->interval_month)->addDay(),
            'expect_end_at' => (clone $this->test->expect_end_at)->subMonths($this->test->type->interval_month)->addDay(),
        ]);
        $oldTest->candidates()->attach($this->user->id, ['is_present' => true]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => "You has admission test record within {$this->test->type->interval_month} months(count from testing at of this test sub {$this->test->type->interval_month} months to now)."]);
    }

    public function test_user_age_less_than_last_order_minimum_age_limit_when_test_is_not_free(): void
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $order->created_at)->subYears(22)->addDay()]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age less than the last order minimum age limit, please contact us.']);
    }

    public function test_user_age_greater_than_last_order_maximum_age_limit_when_test_is_not_free(): void
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $order->created_at)->subYears(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age greater than the last order maximum age limit, please contact us.']);
    }

    public function test_last_order_in_progress_when_order_handle_by_manual_and_test_is_not_free(): void
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your last admission test order in progress by manual, please wait a few minutes.']);
    }

    public function test_after_than_deadline(): void
    {
        $newTestingAt = now()->addDay();
        $this->test->update([
            'testing_at' => $newTestingAt,
            'expect_end_at' => (clone $newTestingAt)->addHour(),
        ]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Cannot register after than before testing date two days.']);
    }

    public function test_admission_test_is_fulled(): void
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'The admission test is fulled.']);
    }

    public function test_user_age_less_than_test_type_minimum_age_limit(): void
    {
        $this->test->type->update(['minimum_age' => 10]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age less than test minimum age limit.']);
    }

    public function test_user_age_greater_than_test_type_maximum_age_limit(): void
    {
        $this->test->type->update(['maximum_age' => 9]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age greater than test maximum age limit.']);
    }

    public function test_price_id_is_not_integer_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create();
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => 'abc',
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The price id field must be an integer.');
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_is_not_exist_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create();
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => 123,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product is invalid.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_is_not_update_to_date_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $price = AdmissionTestPrice::factory()->create(['value' => 200]);
        AdmissionTestPrice::factory()->create([
            'product_id' => $price->product->id,
            'value' => 300,
            'start_at' => now(),
        ]);
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The price of selected product is not up to date, please try again on this up to date version.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_is_not_yet_released_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        $product = AdmissionTestProduct::factory()->create([
            'start_at' => now()->addSeconds(5),
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $price = AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product is not yet released, please try again later or select other product.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_was_taken_down_when_test_is_not_free_and_user_have_no_unused_quota_order(): void
    {
        $product = AdmissionTestProduct::factory()->create([
            'end_at' => now()->subSecond(),
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $price = AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product was taken down, please select other product.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_user_age_less_than_price_id_of_product_minimum_age_limit_and_user_have_no_unused_quota_order(): void
    {
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => 22,
            'maximum_age' => null,
        ]);
        $price = AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => 21,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $this->user->update(['birthday' => now()->subYears(22)->addDay()]);
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'Your age less than product minimum age limit.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_user_age_greater_than_price_id_of_product_maximum_age_limit_and_user_have_no_unused_quota_order(): void
    {
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => 21,
        ]);
        $price = AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => 22,
        ]);
        AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $this->user->update(['birthday' => now()->subYears(22)]);
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'Your age greater than product maximum age limit.');
                        }
                    )->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );

            }
        );
    }

    public function test_have_no_product_when_test_is_not_free_and_user_have_no_unused_quota_order_and_have_no_selected_product(): void
    {
        $this->test->update(['is_free' => false]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Sorry, admission test product(s) is not yet ready, please try again later.']);
    }

    public function test_happy_case_when_test_type_and_order_have_no_any_age_limit_and_test_and_test_is_free_and_have_no_selected_product(): void
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $order->created_at)->subYears(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 4,
            'maximum_age' => 21,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $order->created_at)->subYears(22)->addDay()]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'maximum_age' => 8,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product(): void
    {
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 10,
            'maximum_age' => 21,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_is_free_and_selected_not_exist_product(): void
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => 'abc',
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_is_free_and_selected_exist_product(): void
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => AdmissionTestPrice::factory()->create()->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_have_no_any_age_limit_and_test_and_test_is_not_free_have_no_selected_product(): void
    {
        $this->test->update(['is_free' => false]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $product = AdmissionTestProduct::find($product->id);
        AdmissionTestPrice::factory()->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->has('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_test_is_not_free_have_no_selected_price(): void
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
            'synced_to_stripe' => true,
        ]);
        AdmissionTestPrice::factory()->create();
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $order->created_at)->subYears(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_test_is_not_free_have_no_selected_price(): void
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
            'synced_to_stripe' => true,
        ]);
        AdmissionTestPrice::factory()->create();
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->where('isReschedule', false)
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_test_is_not_free_and_have_no_selected_price(): void
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
            'synced_to_stripe' => true,
        ]);
        AdmissionTestPrice::factory()->create();
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'minimum_age' => 4,
            'maximum_age' => 21,
            'quota' => 1,
            'status' => 'succeeded',
        ]);
        $this->user->update(['birthday' => (clone $this->test->testing_at)->subYears(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_test_is_not_free_and_selected_price(): void
    {
        $this->test->update(['is_free' => false]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $product = AdmissionTestProduct::find($product->id);
        $price = AdmissionTestPrice::factory()->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->has('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_user_has_unused_quota_and_selected_not_exist_product(): void
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
            'quota' => 1,
        ]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => 123,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_user_has_unused_quota_and_selected_exist_product(): void
    {
        AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
            'quota' => 1,
        ]);
        $product = AdmissionTestProduct::factory()->create([
            'minimum_age' => null,
            'maximum_age' => null,
        ]);
        $price = AdmissionTestPrice::factory()->create(['product_id' => $product->id]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', false)
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '')
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_user_has_scheduled_admission_test(): void
    {
        $test = AdmissionTest::factory()->create(['is_free' => true]);
        $test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->where('isReschedule', true);
            }
        );
    }
}
