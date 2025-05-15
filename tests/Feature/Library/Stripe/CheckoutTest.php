<?php

namespace Tests\Feature\Library\Stripe;

use App\Library\Stripe\Client;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_create_checkout_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/checkouts/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        Client::checkouts()->create([
            'success_url' => 'https://example.com/success',
            'line_items' => [
                [
                    'price' => 'price_1MotwRLkdIwHu7ixYcPLm5uZ',
                    'quantity' => 2,
                ],
            ],
            'mode' => 'payment',
        ]);
    }

    public function test_create_checkout_happy_case()
    {
        $response = [
            'id' => 'cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u',
            'object' => 'checkout.session',
            'after_expiration' => null,
            'allow_promotion_codes' => null,
            'amount_subtotal' => 2198,
            'amount_total' => 2198,
            'automatic_tax' => [
                'enabled' => false,
                'liability' => null,
                'status' => null,
            ],
            'billing_address_collection' => null,
            'cancel_url' => null,
            'client_reference_id' => null,
            'consent' => null,
            'consent_collection' => null,
            'created' => 1679600215,
            'currency' => 'usd',
            'custom_fields' => [],
            'custom_text' => [
                'shipping_address' => null,
                'submit' => null,
            ],
            'customer' => null,
            'customer_creation' => 'if_required',
            'customer_details' => null,
            'customer_email' => null,
            'expires_at' => 1679686615,
            'invoice' => null,
            'invoice_creation' => [
                'enabled' => false,
                'invoice_data' => [
                    'account_tax_ids' => null,
                    'custom_fields' => null,
                    'description' => null,
                    'footer' => null,
                    'issuer' => null,
                    'metadata' => [],
                    'rendering_options' => null,
                ],
            ],
            'livemode' => false,
            'locale' => null,
            'metadata' => [],
            'mode' => 'payment',
            'payment_intent' => null,
            'payment_link' => null,
            'payment_method_collection' => 'always',
            'payment_method_options' => [],
            'payment_method_types' => ['card'],
            'payment_status' => 'unpaid',
            'phone_number_collection' => ['enabled' => false],
            'recovered_from' => null,
            'setup_intent' => null,
            'shipping_address_collection' => null,
            'shipping_cost' => null,
            'shipping_details' => null,
            'shipping_options' => [],
            'status' => 'open',
            'submit_type' => null,
            'subscription' => null,
            'success_url' => 'https://example.com/success',
            'total_details' => [
                'amount_discount' => 0,
                'amount_shipping' => 0,
                'amount_tax' => 0,
            ],
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u#fidkdWxOYHwnPyd1blpxYHZxWjA0SDdPUW5JbmFMck1wMmx9N2BLZjFEfGRUNWhqTmJ%2FM2F8bUA2SDRySkFdUV81T1BSV0YxcWJcTUJcYW5rSzN3dzBLPUE0TzRKTTxzNFBjPWZEX1NKSkxpNTVjRjN8VHE0YicpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl',
        ];
        Http::fake([
            'https://api.stripe.com/v1/checkouts/*' => Http::response($response),
        ]);
        $result = Client::checkouts()->create([
            'success_url' => 'https://example.com/success',
            'line_items' => [
                [
                    'price' => 'price_1MotwRLkdIwHu7ixYcPLm5uZ',
                    'quantity' => 2,
                ],
            ],
            'mode' => 'payment',
        ]);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/checkouts/sessions';
            }
        );
        $this->assertEquals($response, $result);
    }

    public function test_find_checkout_have_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/checkouts/*' => Http::response(status: 404),
        ]);
        $result = Client::checkouts()->find('cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u');
        $this->assertNull($result);
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'GET' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/checkouts/sessions/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u';
            }
        );
    }

    public function test_find_checkout_has_result()
    {
        $response = [
            'id' => 'cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u',
            'object' => 'checkout.session',
            'after_expiration' => null,
            'allow_promotion_codes' => null,
            'amount_subtotal' => 2198,
            'amount_total' => 2198,
            'automatic_tax' => [
                'enabled' => false,
                'liability' => null,
                'status' => null,
            ],
            'billing_address_collection' => null,
            'cancel_url' => null,
            'client_reference_id' => null,
            'consent' => null,
            'consent_collection' => null,
            'created' => 1679600215,
            'currency' => 'usd',
            'custom_fields' => [],
            'custom_text' => [
                'shipping_address' => null,
                'submit' => null,
            ],
            'customer' => null,
            'customer_creation' => 'if_required',
            'customer_details' => null,
            'customer_email' => null,
            'expires_at' => 1679686615,
            'invoice' => null,
            'invoice_creation' => [
                'enabled' => false,
                'invoice_data' => [
                    'account_tax_ids' => null,
                    'custom_fields' => null,
                    'description' => null,
                    'footer' => null,
                    'issuer' => null,
                    'metadata' => [],
                    'rendering_options' => null,
                ],
            ],
            'livemode' => false,
            'locale' => null,
            'metadata' => [],
            'mode' => 'payment',
            'payment_intent' => null,
            'payment_link' => null,
            'payment_method_collection' => 'always',
            'payment_method_options' => [],
            'payment_method_types' => ['card'],
            'payment_status' => 'unpaid',
            'phone_number_collection' => ['enabled' => false],
            'recovered_from' => null,
            'setup_intent' => null,
            'shipping_address_collection' => null,
            'shipping_cost' => null,
            'shipping_details' => null,
            'shipping_options' => [],
            'status' => 'open',
            'submit_type' => null,
            'subscription' => null,
            'success_url' => 'https://example.com/success',
            'total_details' => [
                'amount_discount' => 0,
                'amount_shipping' => 0,
                'amount_tax' => 0,
            ],
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u#fidkdWxOYHwnPyd1blpxYHZxWjA0SDdPUW5JbmFMck1wMmx9N2BLZjFEfGRUNWhqTmJ%2FM2F8bUA2SDRySkFdUV81T1BSV0YxcWJcTUJcYW5rSzN3dzBLPUE0TzRKTTxzNFBjPWZEX1NKSkxpNTVjRjN8VHE0YicpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl',
        ];
        Http::fake([
            'https://api.stripe.com/v1/checkouts/*' => Http::response($response),
        ]);
        $result = Client::checkouts()->find('cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u');
        $this->assertEquals($response, $result);
    }

    public function test_update_checkout_happy_case()
    {
        $response = [
            'id' => 'cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u',
            'object' => 'checkout.session',
            'after_expiration' => null,
            'allow_promotion_codes' => null,
            'amount_subtotal' => 2198,
            'amount_total' => 2198,
            'automatic_tax' => [
                'enabled' => false,
                'liability' => null,
                'status' => null,
            ],
            'billing_address_collection' => null,
            'cancel_url' => null,
            'client_reference_id' => null,
            'consent' => null,
            'consent_collection' => null,
            'created' => 1679600215,
            'currency' => 'usd',
            'custom_fields' => [],
            'custom_text' => [
                'shipping_address' => null,
                'submit' => null,
            ],
            'customer' => null,
            'customer_creation' => 'if_required',
            'customer_details' => null,
            'customer_email' => null,
            'expires_at' => 1679686615,
            'invoice' => null,
            'invoice_creation' => [
                'enabled' => false,
                'invoice_data' => [
                    'account_tax_ids' => null,
                    'custom_fields' => null,
                    'description' => null,
                    'footer' => null,
                    'issuer' => null,
                    'metadata' => [],
                    'rendering_options' => null,
                ],
            ],
            'livemode' => false,
            'locale' => null,
            'metadata' => ['order_id' => '6735'],
            'mode' => 'payment',
            'payment_intent' => null,
            'payment_link' => null,
            'payment_method_collection' => 'always',
            'payment_method_options' => [],
            'payment_method_types' => ['card'],
            'payment_status' => 'unpaid',
            'phone_number_collection' => ['enabled' => false],
            'recovered_from' => null,
            'setup_intent' => null,
            'shipping_address_collection' => null,
            'shipping_cost' => null,
            'shipping_details' => null,
            'shipping_options' => [],
            'status' => 'open',
            'submit_type' => null,
            'subscription' => null,
            'success_url' => 'https://example.com/success',
            'total_details' => [
                'amount_discount' => 0,
                'amount_shipping' => 0,
                'amount_tax' => 0,
            ],
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u#fidkdWxOYHwnPyd1blpxYHZxWjA0SDdPUW5JbmFMck1wMmx9N2BLZjFEfGRUNWhqTmJ%2FM2F8bUA2SDRySkFdUV81T1BSV0YxcWJcTUJcYW5rSzN3dzBLPUE0TzRKTTxzNFBjPWZEX1NKSkxpNTVjRjN8VHE0YicpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl',
        ];
        Http::fake([
            'https://api.stripe.com/v1/checkouts/sessions/*' => Http::response($response),
        ]);
        $result = Client::checkouts()->update(
            'cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u',
            ['metadata' => ['order_id' => 6735]]
        );
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/checkouts/sessions/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u';
            }
        );
        $this->assertEquals($response, $result);
    }

    public function test_expire_checkout_happy_case()
    {
        $response = [
            'id' => 'cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u',
            'object' => 'checkout.session',
            'after_expiration' => null,
            'allow_promotion_codes' => null,
            'amount_subtotal' => 2198,
            'amount_total' => 2198,
            'automatic_tax' => [
                'enabled' => false,
                'liability' => null,
                'status' => null,
            ],
            'billing_address_collection' => null,
            'cancel_url' => null,
            'client_reference_id' => null,
            'consent' => null,
            'consent_collection' => null,
            'created' => 1679600215,
            'currency' => 'usd',
            'custom_fields' => [],
            'custom_text' => [
                'shipping_address' => null,
                'submit' => null,
            ],
            'customer' => null,
            'customer_creation' => 'if_required',
            'customer_details' => null,
            'customer_email' => null,
            'expires_at' => 1679686615,
            'invoice' => null,
            'invoice_creation' => [
                'enabled' => false,
                'invoice_data' => [
                    'account_tax_ids' => null,
                    'custom_fields' => null,
                    'description' => null,
                    'footer' => null,
                    'issuer' => null,
                    'metadata' => [],
                    'rendering_options' => null,
                ],
            ],
            'livemode' => false,
            'locale' => null,
            'metadata' => ['order_id' => '6735'],
            'mode' => 'payment',
            'payment_intent' => null,
            'payment_link' => null,
            'payment_method_collection' => 'always',
            'payment_method_options' => [],
            'payment_method_types' => ['card'],
            'payment_status' => 'unpaid',
            'phone_number_collection' => ['enabled' => false],
            'recovered_from' => null,
            'setup_intent' => null,
            'shipping_address_collection' => null,
            'shipping_cost' => null,
            'shipping_details' => null,
            'shipping_options' => [],
            'status' => 'open',
            'submit_type' => null,
            'subscription' => null,
            'success_url' => 'https://example.com/success',
            'total_details' => [
                'amount_discount' => 0,
                'amount_shipping' => 0,
                'amount_tax' => 0,
            ],
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u#fidkdWxOYHwnPyd1blpxYHZxWjA0SDdPUW5JbmFMck1wMmx9N2BLZjFEfGRUNWhqTmJ%2FM2F8bUA2SDRySkFdUV81T1BSV0YxcWJcTUJcYW5rSzN3dzBLPUE0TzRKTTxzNFBjPWZEX1NKSkxpNTVjRjN8VHE0YicpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl',
        ];
        Http::fake([
            'https://api.stripe.com/v1/checkouts/sessions/*' => Http::response($response),
        ]);
        $result = Client::checkouts()->expire('cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u');
        Http::assertSent(
            function (Request $request) {
                return $request->method() == 'POST' &&
                    $request->hasHeader('Stripe-Version', '2025-04-30.basil') &&
                    $request->hasHeader('Authorization', config('service.stripe.keys.secret')) &&
                    $request->url() == 'https://api.stripe.com/v1/checkouts/sessions/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u/expire';
            }
        );
        $this->assertEquals($response, $result);
    }
}
