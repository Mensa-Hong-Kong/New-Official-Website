<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Stripe\Products\SyncAdmissionTest;
use App\Models\AdmissionTestProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Uri;
use Tests\TestCase;

class SyncAdmissionTestProductTest extends TestCase
{
    use RefreshDatabase;

    private $product;

    protected function setUp(): void
    {
        parent::setup();
        Queue::fake();
        $this->product = AdmissionTestProduct::factory()->create();
    }

    public function test_synced_admission_test_product()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
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
            ]),
        ]);
        $this->product->update([
            'stripe_id' => 'prod_NWjs8kKbJWmuuc',
            'synced_to_stripe' => true,
        ]);
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
        Http::assertNothingSent();
    }

    public function test_search_first_stripe_product_for_admission_test_product_but_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response(status: 503),
        ]);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
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
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getCustomUrl) {
                return $request->url() == $getCustomUrl;
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getCustomUrl) {
                return $request->url() != $getCustomUrl;
            }
        );
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertEquals($data['id'], $this->product->stripe_id);
        $this->assertTrue((bool) $this->product->synced_to_stripe);
    }

    public function test_has_stripe_id_just_data_not_update_to_date_and_updata_stripe_that_strip_stripe_under_maintenance()
    {
        Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response(status: 503),
        ]);
        $this->product->update(['stripe_id' => 'prod_NWjs8kKbJWmuuc']);
        $this->expectException(RequestException::class);
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
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
            'stripe_id' => 'prod_NWjs8kKbJWmuuc',
            'name' => $response['name'],
        ]);
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
        Http::assertSent(
            function (Request $request) {
                return $request->url() == 'https://api.stripe.com/v1/products/prod_NWjs8kKbJWmuuc';
            }
        );
        Http::assertNotSent(
            function (Request $request) {
                return $request->url() != 'https://api.stripe.com/v1/products/prod_NWjs8kKbJWmuuc';
            }
        );
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertTrue((bool) $this->product->refresh()->synced_to_stripe);
    }

    public function test_stripe_first_not_found_and_create_stripe_product_but_stripe_under_maintenance()
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
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
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
            'https://api.stripe.com/v1/products' => Http::response($response),
        ]);
        $this->product->update(['name' => $response['name']]);
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getCustomUrl) {
                return in_array(
                    $request->url(),
                    [
                        $getCustomUrl,
                        'https://api.stripe.com/v1/products',
                    ]
                );
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getCustomUrl) {
                return ! in_array(
                    $request->url(),
                    [
                        $getCustomUrl,
                        'https://api.stripe.com/v1/products',
                    ]
                );
            }
        );
        $this->product = AdmissionTestProduct::find($this->product->id);
        $this->assertTrue((bool) $this->product->synced_to_stripe);
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
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
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
        app()->call([new SyncAdmissionTest($this->product->id), 'handle']);
        $getCustomUrl = Uri::of('https://api.stripe.com/v1/products/search')
            ->withQuery(['query' => "metadata['type']:'".AdmissionTestProduct::class."' AND metadata['id']:'{$this->product->id}'"])
            ->__toString();
        Http::assertSent(
            function (Request $request) use ($getCustomUrl) {
                return in_array(
                    $request->url(),
                    [
                        $getCustomUrl,
                        'https://api.stripe.com/v1/products/prod_NZOkxQ8eTZEHwN',
                    ]
                );
            }
        );
        Http::assertNotSent(
            function (Request $request) use ($getCustomUrl) {
                return ! in_array(
                    $request->url(),
                    [
                        $getCustomUrl,
                        'https://api.stripe.com/v1/products/prod_NZOkxQ8eTZEHwN',
                    ]
                );
            }
        );
        $this->assertTrue((bool) $this->product->refresh()->synced_to_stripe);
    }
}
