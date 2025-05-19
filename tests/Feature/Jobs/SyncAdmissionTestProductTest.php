<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Stripe\Products\SyncAdmissionTest;
use App\Models\AdmissionTestProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Tests\TestCase;

class SyncAdmissionTestProductTest extends TestCase
{
    use RefreshDatabase;

    private $product;

    protected function setUp(): void
    {
        parent::setup();
        $this->product = AdmissionTestProduct::factory()->create();
    }

    public function test_stripe_created_and_product_update_to_date_just_missing_save_stripe_id()
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
        $this->product->update(['name' => $data['name']]);
        $this->product->stripeUpdateOrCreate();
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertEquals($data['id'], $this->product->stripe_id);
        $this->assertTrue((bool) $this->product->synced_to_stripe);
        $urls = [];
        foreach(Http::recorded() as $record) {
            [$request, $response] = $record;
            $urls[] = $request->url();
        }
        $this->assertEquals($urls, [$getCustomUrl]);
    }

    public function test_happy_case_has_stripe_id_just_data_not_update_to_date()
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
        $this->product->update([
            'stripe_id' => $response['id'],
            'name' => $response['name'],
        ]);
        $this->product->stripeUpdateOrCreate();
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertTrue((bool) $this->product->refresh()->synced_to_stripe);
        $urls = [];
        foreach(Http::recorded() as $record) {
            [$request, $response] = $record;
            $urls[] = $request->url();
        }
        $this->assertEquals($urls, ['https://api.stripe.com/v1/products/prod_NWjs8kKbJWmuuc']);
    }
    public function test_happy_case_stripe_first_not_found_and_create_stripe_product()
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
            'https://api.stripe.com/v1/products/*' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/products/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/*' => Http::response($response),
        ]);
        $this->product->update(['name' => $response['name']]);
        $this->product->stripeUpdateOrCreate();
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertTrue((bool) $this->product->synced_to_stripe);
        $urls = [];
        foreach(Http::recorded() as $record) {
            [$request, $response] = $record;
            $urls[] = $request->url();
        }
        $this->assertEquals(
            $urls,
            [
                $getCustomUrl,
                'https://api.stripe.com/v1/products',
            ]
        );
    }

    public function test_stripe_created_but_missing_save_stripe_id_and_stripe_data_not_update_to_date_and_updata_stripe_that_strip_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/products/search',
                    'has_more' => false,
                    'data' => [
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
                            'name' => 'old Gold Plan',
                            'package_dimensions' => null,
                            'shippable' => null,
                            'statement_descriptor' => null,
                            'tax_code' => null,
                            'unit_label' => null,
                            'updated' => 1679446501,
                            'url' => null,
                        ],
                    ],
                ])->pushStatus(503),
        ]);
        $this->product->update(['name' => 'Gold Plan']);
        $this->expectException(RequestException::class);
        $this->product->stripeUpdateOrCreate();
    }

    public function test_happy_case_stripe_created_but_missing_save_stripe_id_and_stripe_data_not_update_to_date()
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
            'https://api.stripe.com/v1/products/*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/products/search',
                    'has_more' => false,
                    'data' => [
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
                            'name' => 'Old Gold Plan',
                            'package_dimensions' => null,
                            'shippable' => null,
                            'statement_descriptor' => null,
                            'tax_code' => null,
                            'unit_label' => null,
                            'updated' => 1679446501,
                            'url' => null,
                        ],
                    ],
                ])->push($response),
        ]);
        $this->product->update(['name' => $response['name']]);
        $this->product->stripeUpdateOrCreate();
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        $this->assertTrue((bool) $this->product->refresh()->synced_to_stripe);
        $urls = [];
        foreach(Http::recorded() as $record) {
            [$request, $response] = $record;
            $urls[] = $request->url();
        }
        $this->assertEquals(
            $urls,
            [
                $getCustomUrl,
                'https://api.stripe.com/v1/products',
            ]
        );
    }
}
