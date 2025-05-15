<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Tests\TestCase;

class PriceTest extends TestCase
{
    public function test_search_price_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        Client::prices()->search([
            'active' => 'true',
            'metadata' => ['order_id' => '6735'],
        ]);
    }

    public function test_search_price_happy_case()
    {
        $data = [
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
            ],
        ];
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => $data,
            ]),
        ]);
        $result = Client::prices()->search([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertEquals($data, $result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == Uri::of('https://api.stripe.com/v1/prices/search')
                        ->withQuery(['query' => "active:'true' AND metadata['order_id']:'6735'"])
                        ->__toString();
            }
        );
    }

    public function test_search_price_first_happy_case_has_result()
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
        $result = Client::prices()->first([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertEquals($data, $result);
    }

    public function test_search_price_first_happy_case_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/prices/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/prices/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = Client::prices()->first([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertNull($result);
    }

    public function test_create_price_happy_case()
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
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::prices()->create([
            'currency' => 'usd',
            'unit_amount' => 1000,
            'recurring' => ['interval' => 'month'],
            'product_data' => ['name' => 'Gold Plan'],
        ]);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/prices';
            }
        );
        $this->assertEquals($response, $result);
    }

    public function test_find_price_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response(status: 404),
        ]);
        $result = Client::prices()->find('price_1MoBy5LkdIwHu7ixZhnattbh');
        $this->assertNull($result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/prices/price_1MoBy5LkdIwHu7ixZhnattbh';
            }
        );
    }

    public function test_find_price_has_result()
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
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::prices()->find('price_1MoBy5LkdIwHu7ixZhnattbh');
        $this->assertEquals($response, $result);
    }

    public function test_update_price_happy_case()
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
        $result = Client::prices()->update(
            'prod_NWjs8kKbJWmuuc',
            ['metadata' => ['order_id' => 6735]]
        );
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/prices/prod_NWjs8kKbJWmuuc';
            }
        );
        $this->assertEquals($response, $result);
    }
}
