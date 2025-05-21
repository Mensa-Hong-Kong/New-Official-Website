<?php

namespace Tests\Feature\Admin\AdmissionTest\Products\Prices;

use App\Jobs\Stripe\Prices\SyncAdmissionTest as SyncPrice;
use App\Models\AdmissionTestPrice;
use App\Models\AdmissionTestProduct;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $product;

    private $happyCase = [
        'name' => 'abc',
        'price' => 1,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test']);
        Queue::fake();
        $this->product = AdmissionTestProduct::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_product_not_exists()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_missing_price()
    {
        $data = $this->happyCase;
        unset($data['price']);
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['price' => 'The price field is required.']);
    }

    public function test_price_is_not_integer()
    {
        $data = $this->happyCase;
        $data['price'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be an integer.']);
    }

    public function test_price_less_than_1()
    {
        $data = $this->happyCase;
        $data['price'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be at least 1.']);
    }

    public function test_price_greater_than_65535()
    {
        $data = $this->happyCase;
        $data['price'] = 65536;
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must not be greater than 65535.']);
    }

    public function test_start_at_is_not_date()
    {
        $data = $this->happyCase;
        $data['start_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['start_at' => 'The start at field must be a valid date.']);
    }

    public function test_happy_case()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->postJson(
            route(
                'admin.admission-test.products.prices.store',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product price create success.';
        $data['start_at'] = null;
        $response->assertSuccessful();
        $price = AdmissionTestPrice::latest('id')->first();
        $data['id'] = $price->id;
        $data['update_url'] = route(
            'admin.admission-test.products.prices.update',
            [
                'product' => $this->product,
                'price' => $price,
            ]
        );
        $response->assertJson($data);
        Queue::assertPushed(SyncPrice::class);
    }
}
