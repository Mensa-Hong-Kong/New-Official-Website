<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function test_search_customer_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        Client::customers()->search([
            'name' => 'Jane Doe',
            'metadata' => ['foo' => 'bar'],
        ]);
    }

    public function test_search_customer_happy_case()
    {
        $data = [
            [
                'id' => 'cus_NeGfPRiPKxeBi1',
                'object' => 'customer',
                'address' => null,
                'balance' => 0,
                'created' => 1680569616,
                'currency' => null,
                'default_source' => null,
                'delinquent' => false,
                'description' => null,
                'email' => null,
                'invoice_prefix' => '47D37F8F',
                'invoice_settings' => [
                    'custom_fields' => null,
                    'default_payment_method' => 'pm_1Msy7wLkdIwHu7ixsxmFvcz7',
                    'footer' => null,
                    'rendering_options' => null,
                ],
                'livemode' => false,
                'metadata' => ['foo' => 'bar'],
                'name' => 'Jane Doe',
                'next_invoice_sequence' => 1,
                'phone' => null,
                'preferred_locales' => [],
                'shipping' => null,
                'tax_exempt' => 'none',
                'test_clock' => null,
            ],
        ];
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => $data,
            ]),
        ]);
        $result = Client::customers()->search([
            'name' => 'Jane Doe',
            'metadata' => ['foo' => 'bar'],
        ]);
        $this->assertEquals($data, $result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == Uri::of('https://api.stripe.com/v1/customers/search')
                        ->withQuery(['query' => "name:'Jane Doe' AND metadata['foo']:'bar'"])
                        ->__toString();
            }
        );
    }

    public function test_search_customer_first_happy_case_has_result()
    {
        $data = [
            'id' => 'cus_NeGfPRiPKxeBi1',
            'object' => 'customer',
            'address' => null,
            'balance' => 0,
            'created' => 1680569616,
            'currency' => null,
            'default_source' => null,
            'delinquent' => false,
            'description' => null,
            'email' => null,
            'invoice_prefix' => '47D37F8F',
            'invoice_settings' => [
                'custom_fields' => null,
                'default_payment_method' => 'pm_1Msy7wLkdIwHu7ixsxmFvcz7',
                'footer' => null,
                'rendering_options' => null,
            ],
            'livemode' => false,
            'metadata' => ['foo' => 'bar'],
            'name' => 'Jane Doe',
            'next_invoice_sequence' => 1,
            'phone' => null,
            'preferred_locales' => [],
            'shipping' => null,
            'tax_exempt' => 'none',
            'test_clock' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [$data],
            ]),
        ]);
        $result = Client::customers()->first([
            'name' => 'Jane Doe',
            'metadata' => ['foo' => 'bar'],
        ]);
        $this->assertEquals($data, $result);
    }

    public function test_search_customer_first_happy_case_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = Client::customers()->first([
            'name' => 'Jane Doe',
            'metadata' => ['foo' => 'bar'],
        ]);
        $this->assertNull($result);
    }

    public function test_create_customer_happy_case()
    {
        $response = [
            'id' => 'cus_NffrFeUfNV2Hib',
            'object' => 'customer',
            'address' => null,
            'balance' => 0,
            'created' => 1680893993,
            'currency' => null,
            'default_source' => null,
            'delinquent' => false,
            'description' => null,
            'email' => 'jennyrosen@example.com',
            'invoice_prefix' => '0759376C',
            'invoice_settings' => [
                'custom_fields' => null,
                'default_payment_method' => null,
                'footer' => null,
                'rendering_options' => null,
            ],
            'livemode' => false,
            'metadata' => [],
            'name' => 'Jenny Rosen',
            'next_invoice_sequence' => 1,
            'phone' => null,
            'preferred_locales' => [],
            'shipping' => null,
            'tax_exempt' => 'none',
            'test_clock' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::customers()->create([
            'name' => 'Jenny Rosen',
            'email' => 'jennyrosen@example.com',
        ]);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/customers';
            }
        );
        $this->assertEquals($response, $result);
    }

    public function test_find_customer_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response(status: 404),
        ]);
        $result = Client::customers()->find('cus_NffrFeUfNV2Hib');
        $this->assertNull($result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/customers/cus_NffrFeUfNV2Hib';
            }
        );
    }

    public function test_find_customer_has_result()
    {
        $response = [
            'id' => 'cus_NffrFeUfNV2Hib',
            'object' => 'customer',
            'address' => null,
            'balance' => 0,
            'created' => 1680893993,
            'currency' => null,
            'default_source' => null,
            'delinquent' => false,
            'description' => null,
            'email' => 'jennyrosen@example.com',
            'invoice_prefix' => '0759376C',
            'invoice_settings' => [
                'custom_fields' => null,
                'default_payment_method' => null,
                'footer' => null,
                'rendering_options' => null,
            ],
            'livemode' => false,
            'metadata' => [],
            'name' => 'Jenny Rosen',
            'next_invoice_sequence' => 1,
            'phone' => null,
            'preferred_locales' => [],
            'shipping' => null,
            'tax_exempt' => 'none',
            'test_clock' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = Client::customers()->find('cus_NffrFeUfNV2Hib');
        $this->assertEquals($response, $result);
    }

    public function test_update_customer_happy_case()
    {
        $response = [
            'id' => 'cus_NffrFeUfNV2Hib',
            'object' => 'customer',
            'address' => null,
            'balance' => 0,
            'created' => 1680893993,
            'currency' => null,
            'default_source' => null,
            'delinquent' => false,
            'description' => null,
            'email' => 'jennyrosen@example.com',
            'invoice_prefix' => '0759376C',
            'invoice_settings' => [
                'custom_fields' => null,
                'default_payment_method' => null,
                'footer' => null,
                'rendering_options' => null,
            ],
            'livemode' => false,
            'metadata' => ['order_id' => '6735'],
            'name' => 'Jenny Rosen',
            'next_invoice_sequence' => 1,
            'phone' => null,
            'preferred_locales' => [],
            'shipping' => null,
            'tax_exempt' => 'none',
            'test_clock' => null,
        ];
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response($response),
        ]);
        $result = Client::customers()->update(
            'cus_NffrFeUfNV2Hib',
            ['metadata' => ['order_id' => 6735]]
        );
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/customers/cus_NffrFeUfNV2Hib';
            }
        );
        $this->assertEquals($response, $result);
    }
}
