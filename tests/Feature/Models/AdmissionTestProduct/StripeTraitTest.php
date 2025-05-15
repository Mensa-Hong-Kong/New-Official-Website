<?php

namespace Tests\Feature\Models\AdmissionTestProduct;

use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Models\AdmissionTestProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeTraitTest extends TestCase
{
    use RefreshDatabase;

    private $product;

    protected function setUp(): void
    {
        parent::setup();
        $this->product = AdmissionTestProduct::factory()->create();
    }

    public function test_get_stripe_data_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->product->getStripe();
    }

    public function test_get_stripe_data_happy_case_when_product_have_no_stripe_id_and_no_result()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/products/search',
                'has_more' => false,
                'data' => [],
            ]),
        ]);
        $result = $this->product->getStripe();
        $this->assertNull($this->product->stripe);
        $this->assertNull($result);
    }

    public function test_get_stripe_data_happy_case_when_product_have_no_stripe_id_and_have_result()
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
        $result = $this->product->getStripe();
        $this->assertEquals($data, $this->product->stripe);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->product->stripe_id);
    }

    public function test_get_stripe_data_happy_case_when_product_has_stripe_id()
    {
        $this->product->update(['stripe_id' => 'cus_NeGfPRiPKxeBi1']);
        $response = [
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
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = $this->product->getStripe();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
    }

    public function test_create_stripe_product_but_stripe_id_already()
    {
        $this->product->update(['stripe_id' => 'abc']);
        $this->expectException(AlreadyCreated::class);
        $this->expectExceptionMessage('AdmissionTestProduct is already a Stripe product with ID abc.');
        $this->product->stripeCreate();
    }

    public function test_create_exists_stripe_product_just_missing_save_stripe_id()
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
        $result = $this->product->stripeCreate();
        $this->assertEquals($data, $this->product->stripe);
        $this->assertEquals($data, $result);
        $this->assertEquals($data['id'], $this->product->stripe_id);
    }

    public function test_get_stripe_product_not_found_and_create_product_that_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/products/search',
                    'has_more' => false,
                    'data' => [],
                ])->pushStatus(503),
        ]);
        $this->expectException(RequestException::class);
        $result = $this->product->stripeCreate();
    }

    public function test_create_stripe_product_happy_case()
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
        $this->product->update(['name' => $response['name']]);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/products/search',
                    'has_more' => false,
                    'data' => [],
                ])->push($response),
        ]);
        $result = $this->product->stripeCreate();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->product->stripe_id);
        $this->assertTrue($this->product->synced_to_stripe);
    }

    public function test_update_stripe_product_but_have_no_stripe_id()
    {
        $this->expectException(NotYetCreated::class);
        $this->expectExceptionMessage('AdmissionTestProduct is not a Stripe product yet. See the stripeUpdate method.');
        $this->product->stripeUpdate();
    }

    public function test_update_stripe_product_but_stripe_under_maintenance()
    {
        $this->product->update(['stripe_id' => 'prod_NWjs8kKbJWmuuc']);
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        $this->product->stripeUpdate();
    }

    public function test_update_stripe_product_happy_case()
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
        $this->product->update([
            'stripe_id' => $response['id'],
            'name' => $response['name'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response($response),
        ]);
        $result = $this->product->stripeUpdate();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->product->synced_to_stripe);
    }

    public function test_update_or_create_product_when_have_no_stripe_id()
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
        $this->product->update(['name' => $response['name']]);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/products/search',
                    'has_more' => false,
                    'data' => [],
                ])->push($response),
        ]);
        $result = $this->product->stripeUpdateOrCreate();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
        $this->assertEquals($response['id'], $this->product->stripe_id);
        $this->assertTrue($this->product->synced_to_stripe);
    }

    public function test_update_or_create_product_when_has_stripe_id_and_not_synced_to_stripe()
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
        $this->product->update([
            'stripe_id' => $response['id'],
            'name' => $response['name'],
        ]);
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response($response),
        ]);
        $result = $this->product->stripeUpdate();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->product->synced_to_stripe);
    }

    public function test_update_or_create_product_when_has_stripe_id_and_synced_to_stripe()
    {
        $response = [
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
        $this->product->update([
            'stripe_id' => $response['id'],
            'name' => $response['name'],
            'synced_to_stripe' => true,
        ]);
        Http::fake([
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $result = $this->product->stripeUpdateOrCreate();
        $this->assertEquals($response, $this->product->stripe);
        $this->assertEquals($response, $result);
        $this->assertTrue($this->product->synced_to_stripe);
    }
}
