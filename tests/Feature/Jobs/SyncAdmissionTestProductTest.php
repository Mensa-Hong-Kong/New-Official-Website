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
        $this->assertTrue(! $this->product->stripe_id);
        $this->assertEquals($data['name'], $this->product->name);
        $this->product->getStripe();
        $this->assertFalse(! $this->product->synced_to_stripe);
        $this->assertFalse(! $this->product->stripe);
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
}
