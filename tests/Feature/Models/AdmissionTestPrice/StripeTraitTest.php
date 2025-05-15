<?php

namespace Tests\Feature\Models\AdmissionTestPrice;

use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Exceptions\NotYetCreatedProduct;
use App\Models\AdmissionTestPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeTraitTest extends TestCase
{
    use RefreshDatabase;

    private $price;

    protected function setUp(): void
    {
        parent::setup();
        $this->price = AdmissionTestPrice::factory()->create();
    }

    public function test_get_stripe_data_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->price->getStripe();
    }

    public function test_get_stripe_data_happy_case_when_user_have_no_stripe_id_and_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = $this->price->getStripe();
        $this->assertNull($this->price->stripe);
        $this->assertNull($result);
    }

    public function test_get_stripe_data_happy_case_when_user_have_no_stripe_id_and_have_result()
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
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => [$data],
            ]),
        ]);
        $result = $this->price->getStripe();
        $this->assertEquals($data, $this->price->stripe);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->price->stripe_id);
    }

    public function test_get_stripe_data_happy_case_when_user_has_stripe_id()
    {
        $this->price->update(['stripe_id' => 'cus_NeGfPRiPKxeBi1']);
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
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = $this->price->getStripe();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
    }

    public function test_create_stripe_price_but_stripe_id_already()
    {
        $this->price->update(['stripe_id' => 'abc']);
        $this->expectException(AlreadyCreated::class);
        $this->expectExceptionMessage('AdmissionTestPrice is already a Stripe price with ID abc.');
        $this->price->stripeCreate();
    }

    public function test_create_exists_stripe_price_just_missing_save_stripe_id()
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
        $result = $this->price->stripeCreate();
        $this->assertEquals($data, $this->price->stripe);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->price->stripe_id);
    }

    public function test_get_stripe_price_not_found_and_product_stripe_not_yet_created()
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
        $this->expectException(NotYetCreatedProduct::class);
        $this->price->stripeCreate();
        $this->expectExceptionMessage('Product of AdmissionTestPrice is not a Stripe product yet. See the stripeCreate method.');
    }

    public function test_get_stripe_price_not_found_and_create_price_that_stripe_under_maintenance()
    {
        $this->price->product->update(['stripe_id' => 'prod_NWjs8kKbJWmuuc']);
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
        $this->price->stripeCreate();
    }

    public function test_create_stripe_price_happy_case()
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
        $this->price->update(['name' => $response['nickname']]);
        $this->price->product->update(['stripe_id' => 'prod_NWjs8kKbJWmuuc']);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/prices/search',
                    'has_more' => false,
                    'data' => [],
                ])->push($response),
        ]);
        $result = $this->price->stripeCreate();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->price->stripe_id);
        $this->assertTrue($this->price->synced_to_stripe);
    }

    public function test_update_stripe_price_but_have_no_stripe_id()
    {
        $this->expectException(NotYetCreated::class);
        $this->expectExceptionMessage('AdmissionTestPrice is not a Stripe price yet. See the stripeUpdate method.');
        $this->price->stripeUpdate();
    }

    public function test_update_stripe_price_but_stripe_under_maintenance()
    {
        $this->price->update(['stripe_id' => 'price_1MoBy5LkdIwHu7ixZhnattbh']);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->price->stripeUpdate();
    }

    public function test_update_stripe_price_happy_case()
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
        $this->price->update([
            'stripe_id' => $response['id'],
            'name' => $response['nickname'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdate();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_to_stripe);
    }

    public function test_update_or_create_price_when_have_no_stripe_id()
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
        $this->price->update(['name' => $response['nickname']]);
        $this->price->product->update(['stripe_id' => 'prod_NWjs8kKbJWmuuc']);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/prices/search',
                    'has_more' => false,
                    'data' => [],
                ])->push($response),
        ]);
        $result = $this->price->stripeUpdateOrCreate();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->price->stripe_id);
        $this->assertTrue($this->price->synced_to_stripe);
    }

    public function test_update_or_create_price_when_has_stripe_id_and_not_synced_to_stripe()
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
        $this->price->update([
            'stripe_id' => $response['id'],
            'name' => $response['nickname'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdate();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_to_stripe);
    }

    public function test_update_or_create_price_when_has_stripe_id_and_synced_to_stripe()
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
        $this->price->update([
            'stripe_id' => $response['id'],
            'name' => $response['nickname'],
            'synced_to_stripe' => true,
        ]);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdateOrCreate();
        $this->assertEquals($response, $this->price->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_to_stripe);
    }
}
