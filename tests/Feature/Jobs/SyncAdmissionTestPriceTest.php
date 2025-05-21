<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Stripe\Prices\SyncAdmissionTest;
use App\Models\AdmissionTestPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Uri;
use Tests\TestCase;

class SyncAdmissionTestPriceTest extends TestCase
{
    use RefreshDatabase;

    private $price;

    protected function setUp(): void
    {
        parent::setup();
        Queue::fake();
        $this->price = AdmissionTestPrice::factory()->create();
        $this->price->product->update(['stripe_id' => 'prod_NZOkxQ8eTZEHwN']);
    }

    public function test_price_have_no_stripe_id()
    {
        $this->price->product->update(['stripe_id' => null]);
        Http::fake();
        $job = (new SyncAdmissionTest($this->price->id))->withFakeQueueInteractions();
        $job->handle();
        $job->assertReleased(60);
        Http::assertNothingSent();
    }

    public function test_synced_admission_test_price()
    {
        $data = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => [],
            'nickname' => null,
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($data),
        ]);
        $this->price->update([
            'stripe_id' => $data['id'],
            'synced_to_stripe' => true,
        ]);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
        Http::assertNothingSent();
    }

    public function test_search_first_stripe_price_for_admission_test_price_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
    }

    public function test_stripe_created_and_price_update_to_date_just_missing_save_stripe_id()
    {
        $data = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => ['order_id' => '6735'],
            'nickname' => null,
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => [$data],
            ]),
        ]);
        $this->price->update(['name' => $data['nickname']]);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
        $getProductUrl = Uri::of('https://api.stripe.com/v1/prices/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestPrice::class."' AND metadata['id']:'{$this->price->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getProductUrl) {
                return $request->url() == $getProductUrl;
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getProductUrl) {
                return $request->url() != $getProductUrl;
            }
        );
        $this->price = AdmissionTestPrice::find($this->price->id);
        $this->assertEquals($data['id'], $this->price->stripe_id);
        $this->assertTrue((bool) $this->price->synced_to_stripe);
    }

    public function test_has_stripe_id_just_data_not_update_to_date_and_updata_stripe_that_strip_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->price->update(['stripe_id' => 'price_1MoBy5LkdIwHu7ixZhnattbh']);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
    }

    public function test_happy_case_has_stripe_id_just_data_not_update_to_date()
    {
        $response = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => ['order_id' => '6735'],
            'nickname' => null,
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($response),
        ]);
        $this->price->update([
            'stripe_id' => $response['id'],
            'name' => $response['nickname'],
        ]);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
        Http::assertSent(
            function (Request $request) use ($response) {
                return $request->url() == "https://api.stripe.com/v1/prices/{$response['id']}";
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($response) {
                return $request->url() != "https://api.stripe.com/v1/prices/{$response['id']}";
            }
        );
        $this->price = AdmissionTestPrice::find($this->price->id);
        $this->assertTrue((bool) $this->price->refresh()->synced_to_stripe);
    }

    public function test_stripe_first_not_found_and_create_stripe_price_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/prices/search',
                    'has_more' => false,
                    'data' => [],
                ])->pushStatus(503),
        ]);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
    }

    public function test_happy_case_stripe_first_not_found_and_create_stripe_price()
    {
        $response = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => [],
            'nickname' => null,
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/prices' => Http::response($response),
        ]);
        $this->price->update(['name' => $response['nickname']]);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
        $urls = [
            Uri::of('https://api.stripe.com/v1/prices/search')
                ->withQuery(['query' => "metadata['type']:'".AdmissionTestPrice::class."' AND metadata['id']:'{$this->price->id}'"])
                ->__toString(),
            'https://api.stripe.com/v1/prices',
        ];
        Http::assertSent(
            function (Request $request) use ($urls) {
                return in_array($request->url(), $urls);
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($urls) {
                return ! in_array($request->url(), $urls);
            }
        );
        $this->price = AdmissionTestPrice::find($this->price->id);
        $this->assertTrue((bool) $this->price->synced_to_stripe);
    }

    public function test_stripe_created_but_missing_save_stripe_id_and_stripe_data_not_update_to_date_and_updata_stripe_that_strip_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/prices/search',
                    'has_more' => false,
                    'data' => [
                        [
                            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
                            'object' => 'price',
                            'active' => true,
                            'billing_scheme' => 'per_unit',
                            'created' => 1679431181,
                            'currency' => 'usd',
                            'custom_unit_amount' => null,
                            'livemode' => false,
                            'lookup_key' => null,
                            'metadata' => ['order_id' => '6735'],
                            'nickname' => 'Old Name',
                            'product' => 'prod_NZKdYqrwEYx6iK',
                            'recurring' => [
                                'interval' => 'month',
                                'interval_count' => 1,
                                'trial_period_days' => null,
                                'usage_type' => 'licensed',
                            ],
                            'tax_behavior' => 'unspecified',
                            'tiers_mode' => null,
                            'transform_quantity' => null,
                            'type' => 'recurring',
                            'unit_amount' => 1000,
                            'unit_amount_decimal' => '1000',
                        ],
                    ],
                ])->pushStatus(503),
        ]);
        $this->price->update(['name' => null]);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
    }

    public function test_happy_case_stripe_created_but_missing_save_stripe_id_and_stripe_data_not_update_to_date()
    {
        $response = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => ['order_id' => '6735'],
            'nickname' => null,
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        $data = [
            'id' => 'price_1MoBy5LkdIwHu7ixZhnattbh',
            'object' => 'price',
            'active' => true,
            'billing_scheme' => 'per_unit',
            'created' => 1679431181,
            'currency' => 'usd',
            'custom_unit_amount' => null,
            'livemode' => false,
            'lookup_key' => null,
            'metadata' => ['order_id' => '6735'],
            'nickname' => 'Old Name',
            'product' => 'prod_NZKdYqrwEYx6iK',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => null,
                'usage_type' => 'licensed',
            ],
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/prices/search',
                    'has_more' => false,
                    'data' => [$data],
                ])->push($response),
        ]);
        $this->price->update(['name' => $response['nickname']]);
        app()->call([new SyncAdmissionTest($this->price->id), 'handle']);
        $urls = [
            Uri::of('https://api.stripe.com/v1/prices/search')
                ->withQuery(['query' => "metadata['type']:'".AdmissionTestPrice::class."' AND metadata['id']:'{$this->price->id}'"])
                ->__toString(),
            "https://api.stripe.com/v1/prices/{$data['id']}",
        ];
        Http::assertSent(
            function (Request $request) use ($urls) {
                return in_array($request->url(), $urls);
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($urls) {
                return ! in_array($request->url(), $urls);
            }
        );
        $this->assertTrue((bool) $this->price->refresh()->synced_to_stripe);
    }
}
