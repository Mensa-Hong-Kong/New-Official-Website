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

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $product;

    private $price;

    private $happyCase = [
        'name' => 'abc',
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test']);
        Queue::fake();
        $this->product = AdmissionTestProduct::factory()->create();
        $this->price = AdmissionTestPrice::factory()
            ->state([
                'product_id' => $this->product->id,
                'synced_to_stripe' => true,
            ])
            ->create();
        Queue::fake();
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
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
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_product_not_exists()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => 0,
                    'price' => $this->price,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_price_not_exists()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => 0,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
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

    public function test_happy_case_when_name_have_no_change()
    {
        $data = $this->happyCase;
        $data['name'] = $this->price->name;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
            ),
            $data
        );
        $data['success'] = 'The admission test product price update success.';
        $data['start_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertTrue((bool) AdmissionTestPrice::find($this->price->id)->synced_to_stripe);
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_name_has_change()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.prices.update',
                [
                    'product' => $this->product,
                    'price' => $this->price,
                ]
            ),
            $data
        );
        $data['success'] = 'The admission test product price update success.';
        $data['start_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertFalse((bool) AdmissionTestPrice::find($this->price->id)->synced_to_stripe);
        Queue::assertPushed(SyncPrice::class);
    }
}
