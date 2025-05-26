<?php

namespace Tests\Feature\Webhooks\Stripe;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class CustomerDeletedTest extends TestCase
{
    use RefreshDatabase;

    protected function sign($timestamp, $payload)
    {
        return hash_hmac(
            'sha256',
            "$timestamp.$payload",
            config('services.stripe.keys.webhook')
        );
    }

    protected function signature($timestamp, $signature)
    {
        return "t=$timestamp,v1=$signature";
    }

    protected function signatureHeader($payload)
    {
        $timestamp = time();
        $signature = $this->sign(
            time(),
            json_encode($payload)
        );

        return ['Stripe-Signature' => "t=$timestamp,v1=$signature"];
    }

    public function test_missing_data_object_id()
    {
        $data = ['type' => 'customer.deleted'];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertInvalid(['data.object.id' => 'The data.object.id field is required.']);
    }

    public function test_customer_is_not_exists()
    {
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertSuccessful();
        $response->assertSee('Webhook Handled');
    }

    public function test_customer_exist_and_customer_is_user_and_search_first_stripe_customer_for_user_but_stripe_under_maintenance()
    {
        Queue::fake();
        $user = User::factory()->create();
        $user->stripe()->create(['id' => 'cus_NeGfPRiPKxeBi1']);
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response(status: 503),
        ]);
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertStatus(500);
    }

    public function test_customer_exist_and_customer_is_user_and_create_customer_but_stripe_under_maintenance()
    {
        Queue::fake();
        $user = User::factory()->create();
        $user->stripe()->create(['id' => 'cus_NeGfPRiPKxeBi1']);
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/*' => Http::response(status: 503),
        ]);
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertStatus(500);
    }

    public function test_customer_exist_and_customer_is_user_and_create_customer_happy_case()
    {
        Queue::fake();
        $user = User::factory()->create();
        $user->stripe()->create(['id' => 'cus_NeGfPRiPKxeBi1']);
        $stripeCreateRresponse = [
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
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/*' => Http::response($stripeCreateRresponse),
        ]);
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertSuccessful();
        $response->assertSee('Webhook Handled');
        $this->assertEquals(
            $stripeCreateRresponse['id'],
            $user->refresh()->stripe->id
        );
    }
}
