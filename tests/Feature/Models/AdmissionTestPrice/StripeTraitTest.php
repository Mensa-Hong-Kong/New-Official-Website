<?php

namespace Tests\Feature\Models\AdmissionTestPrice;

use App\Library\Stripe\Exceptions\AlreadyCreatedPrice;
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
        $this->price = AdmissionTestPrice::find(AdmissionTestPrice::factory()->create()->id);
    }

    public function test_get_stripe_data_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->price->getStripe('one_time');
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
        $result = $this->price->getStripe('one_time');
        $this->assertNull($this->price->stripeData['one_time']);
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
            'recurring' => null,
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
        $result = $this->price->getStripe('one_time');
        $this->assertEquals($data, $this->price->stripeData['one_time']);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->price->stripe_one_time_type_id);
    }

    public function test_get_stripe_data_happy_case_when_user_has_stripe_id()
    {
        $this->price->update(['stripe_one_time_type_id' => 'price_1MoBy5LkdIwHu7ixZhnattbh']);
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
            'recurring' => null,
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
        $result = $this->price->getStripe('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
    }

    public function test_create_stripe_price_but_stripe_id_already()
    {
        $this->price->update(['stripe_one_time_type_id' => 'abc']);
        $this->expectException(AlreadyCreatedPrice::class);
        $this->expectExceptionMessage('AdmissionTestPrice is already a Stripe one time type price with ID abc.');
        $this->price->stripeCreate('one_time');
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
            'recurring' => null,
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
        $result = $this->price->stripeCreate('one_time');
        $this->assertEquals($data, $this->price->stripeData['one_time']);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->price->stripe_one_time_type_id);
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
        $this->price->stripeCreate('one_time');
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
        $this->price->stripeCreate('one_time');
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
            'recurring' => null,
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
        $result = $this->price->stripeCreate('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->price->stripe_one_time_type_id);
        $this->assertTrue($this->price->synced_one_time_type_to_stripe);
    }

    public function test_update_stripe_price_but_have_no_stripe_id()
    {
        $this->expectException(NotYetCreated::class);
        $this->expectExceptionMessage('AdmissionTestPrice is not a Stripe price yet. See the stripeUpdate method.');
        $this->price->stripeUpdate('one_time');
    }

    public function test_update_stripe_price_but_stripe_under_maintenance()
    {
        $this->price->update(['stripe_one_time_type_id' => 'price_1MoBy5LkdIwHu7ixZhnattbh']);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->price->stripeUpdate('one_time');
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
            'recurring' => null,
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        $this->price->update([
            'stripe_one_time_type_id' => $response['id'],
            'name' => $response['nickname'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdate('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_one_time_type_to_stripe);
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
            'recurring' => null,
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
        $result = $this->price->stripeUpdateOrCreate('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->price->stripe_one_time_type_id);
        $this->assertTrue($this->price->synced_one_time_type_to_stripe);
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
            'recurring' => null,
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        $this->price->update([
            'stripe_one_time_type_id' => $response['id'],
            'name' => $response['nickname'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdate('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_one_time_type_to_stripe);
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
            'recurring' => null,
            'tax_behavior' => 'unspecified',
            'tiers_mode' => null,
            'transform_quantity' => null,
            'type' => 'recurring',
            'unit_amount' => 1000,
            'unit_amount_decimal' => '1000',
        ];
        $this->price->update([
            'stripe_one_time_type_id' => $response['id'],
            'name' => $response['nickname'],
            'synced_one_time_type_to_stripe' => true,
        ]);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = $this->price->stripeUpdateOrCreate('one_time');
        $this->assertEquals($response, $this->price->stripeData['one_time']);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->price->synced_one_time_type_to_stripe);
    }
}
