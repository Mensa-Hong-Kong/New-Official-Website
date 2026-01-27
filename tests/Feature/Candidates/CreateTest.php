<?php

namespace Tests\Feature\Candidates;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
        $this->test = AdmissionTest::factory()->state([
            'is_free' => true,
            'is_public' => true,
        ])->create();
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

    public function test_user_not_exist_stripe_customer_when_test_is_not_free_and_user_have_no_unused_quota_order()
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

    public function test_user_already_schedule_this_admission_test()
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
        $response->assertRedirectToRoute('admission-tests.index');
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
        $response->assertRedirectToRoute('admission-tests.index');
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
        $response->assertRedirectToRoute('admission-tests.index');
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your passport has already been qualification for membership.']);
    }

    public function test_user_has_other_same_passport_user_account_tested()
    {
        $newTestingAt = now()->addDays(2);
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'You other same passport user account tested.']);
    }

    public function test_user_has_already_been_taken_within_interval_months()
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => "You has admission test record within {$this->test->type->interval_month} months(count from testing at of this test sub {$this->test->type->interval_month} months to now)."]);
    }

    public function test_user_age_less_than_last_order_minimum_age_limit_when_test_is_not_free()
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $order->created_at->subYear(22)->addDay()]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age less than the last order minimum age limit, please contact us.']);
    }

    public function test_user_age_greater_than_last_order_maximum_age_limit_when_test_is_not_free()
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $order->created_at->subYear(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age greater than the last order maximum age limit, please contact us.']);
    }

    public function test_last_order_in_progress_when_order_handle_by_manual_and_test_is_not_free()
    {
        $this->test->update(['is_free' => false]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ])->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your last admission test order in progress by manual, please wait a few minutes.']);
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
        $response->assertRedirectToRoute('admission-tests.index');
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
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'The admission test is fulled.']);
    }

    public function test_user_age_less_than_test_type_minimum_age_limit()
    {
        $this->test->type->update(['minimum_age' => 10]);
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age less than test minimum age limit.']);
    }

    public function test_user_age_greater_than_test_type_maximum_age_limit()
    {
        $this->test->type->update(['maximum_age' => 9]);
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertRedirectToRoute('admission-tests.index');
        $response->assertSessionHasErrors(['message' => 'Your age greater than test maximum age limit.']);
    }

    public function test_price_id_is_not_integer_when_test_is_not_free_and_user_have_no_unused_quota_order()
    {
        AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
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

    public function test_price_id_of_product_is_not_exist_when_test_is_not_free_and_user_have_no_unused_quota_order()
    {
        AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product is invalid.');
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_is_not_update_to_date_when_test_is_not_free_and_user_have_no_unused_quota_order()
    {
        AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['value' => 200])->create();
        AdmissionTestPrice::factory()->state([
            'product_id' => $price->product->id,
            'value' => 300,
            'start_at' => now(),
        ])->create();
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The price of selected product is not up to date, please try again on this up to date version.');
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_is_not_yet_released_when_test_is_not_free_and_user_have_no_unused_quota_order()
    {
        $product = AdmissionTestProduct::factory()->state([
            'start_at' => now()->addSeconds(2),
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product is not yet released, please try again later or select other product.');
                        }
                    );
            }
        );
    }

    public function test_price_id_of_product_was_taken_down_when_test_is_not_free_and_user_have_no_unused_quota_order()
    {
        $product = AdmissionTestProduct::factory()->state([
            'end_at' => now()->subSecond(),
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
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
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'The selected product was taken down, please select other product.');
                        }
                    );
            }
        );
    }

    public function test_user_age_less_than_price_id_of_product_minimum_age_limit_and_user_have_no_unused_quota_order()
    {
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => 22,
            'maximum_age' => null,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => 21,
            'maximum_age' => null,
        ])->create();
        AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
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
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'Your age less than product minimum age limit.');
                        }
                    );
            }
        );
    }

    public function test_user_age_greater_than_price_id_of_product_maximum_age_limit_and_user_have_no_unused_quota_order()
    {
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => 21,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => 22,
        ])->create();
        AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
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
                    ->has('products')
                    ->missing('price')
                    ->has(
                        'flash', function (Assert $page) {
                            $page->where('error', 'Your age greater than product maximum age limit.');
                        }
                    );

            }
        );
    }

    public function test_have_no_product_when_test_is_not_free_and_user_have_no_unused_quota_order_and_have_no_selected_product()
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

    public function test_happy_case_when_test_type_and_order_have_no_any_age_limit_and_test_and_test_is_free_and_have_no_selected_product()
    {
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $order->created_at->subYear(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_user_match_the_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 4,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $order->created_at->subYear(22)->addDay()]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'maximum_age' => 8,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_user_not_match_the_order_age_limit_and_test_is_free_have_no_selected_product()
    {
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 10,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_is_free_and_selected_not_exist_product()
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_is_free_and_selected_exist_product()
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_have_no_any_age_limit_and_test_and_test_is_not_free_have_no_selected_product()
    {
        $this->test->update(['is_free' => false]);
        $product = AdmissionTestProduct::factory()
            ->state([
                'minimum_age' => null,
                'maximum_age' => null,
            ])->create();
        $product = AdmissionTestProduct::find($product->id);
        AdmissionTestPrice::factory()->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->has('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_minimum_age_limit_and_test_is_not_free_have_no_selected_price()
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()
            ->state([
                'minimum_age' => null,
                'maximum_age' => null,
                'synced_to_stripe' => true,
            ])->create();
        AdmissionTestPrice::factory()->create();
        $this->test->type->update(['minimum_age' => 10]);
        $order = AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 22,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $order->created_at->subYear(22)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_only_has_maximum_age_limit_and_test_is_not_free_have_no_selected_price()
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()
            ->state([
                'minimum_age' => null,
                'maximum_age' => null,
                'synced_to_stripe' => true,
            ])->create();
        AdmissionTestPrice::factory()->create();
        $this->test->type->update(['maximum_age' => 9]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_type_and_order_has_minimum_and_maximum_age_limit_and_test_is_not_free_and_have_no_selected_price()
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestProduct::factory()
            ->state([
                'minimum_age' => null,
                'maximum_age' => null,
                'synced_to_stripe' => true,
            ])->create();
        AdmissionTestPrice::factory()->create();
        $this->test->type->update([
            'minimum_age' => 4,
            'maximum_age' => 9,
        ]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'minimum_age' => 4,
            'maximum_age' => 21,
            'status' => 'succeeded',
        ])->create();
        $this->user->update(['birthday' => $this->test->testing_at->subYear(10)->addDays(2)]);
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                ['admission_test' => $this->test]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_test_is_not_free_and_selected_price()
    {
        $this->test->update(['is_free' => false]);
        $product = AdmissionTestProduct::factory()
            ->state([
                'minimum_age' => null,
                'maximum_age' => null,
            ])->create();
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
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->has('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_user_has_unused_quota_and_selected_not_exist_product()
    {
        $this->test->update(['is_free' => false]);
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
        ])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => 123,
                ]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }

    public function test_happy_case_when_user_has_unused_quota_and_selected_exist_product()
    {
        AdmissionTestOrder::factory()->state([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
        ])->create();
        $product = AdmissionTestProduct::factory()->state([
            'minimum_age' => null,
            'maximum_age' => null,
        ])->create();
        $price = AdmissionTestPrice::factory()->state(['product_id' => $product->id])->create();
        $response = $this->actingAs($this->user)->get(
            route(
                'admission-tests.candidates.create',
                [
                    'admission_test' => $this->test,
                    'price_id' => $price->id,
                ]
            ),
        );
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Create')
                    ->missing('products')
                    ->missing('price')
                    ->where('flash.error', '');
            }
        );
    }
}
