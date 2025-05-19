<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Stripe\Customers\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
    }

    public function test_user_has_stripe_id()
    {
        Http::fake();
        $this->user->update(['stripe_id' => 'cus_NeGfPRiPKxeBi1']);
        app()->call([new CreateUser($this->user->id), 'handle']);
        Http::assertNothingSent();
    }

    public function test_search_first_stripe_customer_for_user_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        app()->call([new CreateUser($this->user->id), 'handle']);
    }

    public function test_already_stripe_user_just_missing_save_stripe_id()
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
        $this->user->update([
            'given_name' => 'Jane',
            'middle_name' => null,
            'family_name' => 'Doe',
        ]);
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [$data],
            ]),
        ]);
        app()->call([new CreateUser($this->user->id), 'handle']);
        $getCustomerUrl = Uri::of('https://api.stripe.com/v1/customers/search')
            ->withQuery(['query' => "metadata['type']:'".User::class."' AND metadata['id']:'{$this->user->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getCustomerUrl) {
                return $request->url() == $getCustomerUrl;
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getCustomerUrl) {
                return $request->url() != $getCustomerUrl;
            }
        );
        $this->user = User::find($this->user->id);
        $this->assertEquals($data['id'], $this->user->stripe_id);
        $this->assertTrue((bool) $this->user->synced_to_stripe);
    }

    public function test_not_found_stripe_customer_and_create_customer_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        app()->call([new CreateUser($this->user->id), 'handle']);
    }

    public function test_create_stripe_customer_happy_case()
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
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $this->user->update([
            'given_name' => 'Jenny',
            'middle_name' => null,
            'family_name' => 'Rosen',
        ]);
        app()->call([new CreateUser($this->user->id), 'handle']);
        $getCustomerUrl = Uri::of('https://api.stripe.com/v1/customers/search')
            ->withQuery(['query' => "metadata['type']:'".User::class."' AND metadata['id']:'{$this->user->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getCustomerUrl) {
                return in_array(
                    $request->url(),
                    [
                        $getCustomerUrl,
                        'https://api.stripe.com/v1/customers',
                    ]
                );
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getCustomerUrl) {
                return ! in_array(
                    $request->url(),
                    [
                        $getCustomerUrl,
                        'https://api.stripe.com/v1/customers',
                    ]
                );
            }
        );
        $this->user = User::find($this->user->id);
        $this->assertEquals($response['id'], $this->user->stripe_id);
        $this->assertFalse((bool) $this->user->synced_to_stripe);
    }
}
