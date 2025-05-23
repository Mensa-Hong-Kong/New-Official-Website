<?php

namespace Tests\Feature\Models\User;

use App\Library\Stripe\Exceptions\AlreadyCreatedCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeTraitTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
    }

    public function test_get_stripe_data_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->user->getStripe();
    }

    public function test_get_stripe_data_happy_case_when_user_have_no_stripe_id_and_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = $this->user->getStripe();
        $this->assertNull($this->user->stripe);
        $this->assertNull($result);
    }

    public function test_get_stripe_data_happy_case_when_user_have_no_stripe_id_and_have_result()
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
        $result = $this->user->getStripe();
        $this->assertEquals($data, $this->user->stripe->data);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->user->stripe->id);
    }

    public function test_get_stripe_data_happy_case_when_user_has_stripe_id()
    {
        $this->user->stripe()->create(['id' => 'cus_NeGfPRiPKxeBi1']);
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
        $result = $this->user->getStripe();
        $this->assertEquals($response, $this->user->stripe->data);
        $this->assertEquals($response, $result);
    }

    public function test_create_stripe_but_stripe_id_already()
    {
        $this->user->stripe()->create(['id' => 'abc']);
        $this->expectException(AlreadyCreatedCustomer::class);
        $this->expectExceptionMessage('User is already a Stripe customer with ID abc.');
        $this->user->stripeCreate();
    }

    public function test_create_exists_stripe_user_just_missing_save_stripe_id()
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
        $result = $this->user->stripeCreate();
        $this->assertEquals($data, $this->user->stripe->data);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->user->stripe->id);
    }

    public function test_get_stripe_user_not_found_and_create_user_that_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [],
                ])->pushStatus(503),
        ]);
        $this->expectException(RequestException::class);
        $result = $this->user->stripeCreate();
    }

    public function test_create_stripe_happy_case()
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
            'https://api.stripe.com/v1/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [],
                ])->push($response),
        ]);
        $result = $this->user->stripeCreate();
        $this->assertEquals($response, $this->user->stripe->data);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->user->stripe->id);
    }
}
