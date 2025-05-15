<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_search_product_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        Client::products()->search([
            'active' => 'true',
            'metadata' => ['order_id' => '6735'],
        ]);
    }

    public function test_search_product_happy_case()
    {
        $data = [
            [
                'id' => 'prod_NZOkxQ8eTZEHwN',
                'object' => 'product',
                'active' => true,
                'created' => 1679446501,
                'default_price' => null,
                'description' => null,
                'images' => [],
                'livemode' => false,
                'metadata' => ['order_id' => '6735'],
                'name' => 'Gold Plan',
                'package_dimensions' => null,
                'shippable' => null,
                'statement_descriptor' => null,
                'tax_code' => null,
                'unit_label' => null,
                'updated' => 1679446501,
                'url' => null,
            ],
        ];
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/products/search',
                'has_more' => false,
                'data' => $data,
            ]),
        ]);
        $result = Client::products()->search([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertEquals($data, $result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == Uri::of('https://api.stripe.com/v1/products/search')
                        ->withQuery(['query' => "active:'true' AND metadata['order_id']:'6735'"])
                        ->__toString();
            }
        );
    }

    public function test_search_product_first_happy_case_has_result()
    {
        $data = [
            'id' => 'prod_NZOkxQ8eTZEHwN',
            'object' => 'product',
            'active' => true,
            'created' => 1679446501,
            'default_price' => null,
            'description' => null,
            'images' => [],
            'livemode' => false,
            'metadata' => ['order_id' => '6735'],
            'name' => 'Gold Plan',
            'package_dimensions' => null,
            'shippable' => null,
            'statement_descriptor' => null,
            'tax_code' => null,
            'unit_label' => null,
            'updated' => 1679446501,
            'url' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/products/search',
                'has_more' => false,
                'data' => [$data],
            ]),
        ]);
        $result = Client::products()->first([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertEquals($data, $result);
    }

    public function test_search_product_first_happy_case_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/products/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = Client::products()->first([
            'active' => 'true',
            'metadata' => ['order_id' => 6735],
        ]);
        $this->assertNull($result);
    }

    public function test_create_product_happy_case()
    {
        $response = [
            'id' => 'prod_NWjs8kKbJWmuuc',
            'object' => 'product',
            'active' => true,
            'created' => 1678833149,
            'default_price' => null,
            'description' => null,
            'images' => [],
            'marketing_features' => [],
            'livemode' => false,
            'metadata' => [],
            'name' => 'Gold Plan',
            'package_dimensions' => null,
            'shippable' => null,
            'statement_descriptor' => null,
            'tax_code' => null,
            'unit_label' => null,
            'updated' => 1678833149,
            'url' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::products()->create(['name' => 'Gold Plan']);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/products';
            }
        );
        $this->assertEquals($response, $result);
    }

    public function test_find_product_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response(status: 404),
        ]);
        $result = Client::products()->find('prod_NWjs8kKbJWmuuc');
        $this->assertNull($result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/products/prod_NWjs8kKbJWmuuc';
            }
        );
    }

    public function test_find_product_has_result()
    {
        $response = [
            'id' => 'prod_NWjs8kKbJWmuuc',
            'object' => 'product',
            'active' => true,
            'created' => 1678833149,
            'default_price' => null,
            'description' => null,
            'images' => [],
            'marketing_features' => [],
            'livemode' => false,
            'metadata' => [],
            'name' => 'Gold Plan',
            'package_dimensions' => null,
            'shippable' => null,
            'statement_descriptor' => null,
            'tax_code' => null,
            'unit_label' => null,
            'updated' => 1678833149,
            'url' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::products()->find('prod_NWjs8kKbJWmuuc');
        $this->assertEquals($response, $result);
    }

    public function test_update_product_happy_case()
    {
        $response = [
            'id' => 'prod_NWjs8kKbJWmuuc',
            'object' => 'product',
            'active' => true,
            'created' => 1678833149,
            'default_price' => null,
            'description' => null,
            'images' => [],
            'marketing_features' => [],
            'livemode' => false,
            'metadata' => ['order_id' => '6735'],
            'name' => 'Gold Plan',
            'package_dimensions' => null,
            'shippable' => null,
            'statement_descriptor' => null,
            'tax_code' => null,
            'unit_label' => null,
            'updated' => 1678833149,
            'url' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response($response),
        ]);
        $result = Client::products()->update(
            'prod_NWjs8kKbJWmuuc',
            ['metadata' => ['order_id' => 6735]]
        );
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/products/prod_NWjs8kKbJWmuuc';
            }
        );
        $this->assertEquals($response, $result);
    }
}
