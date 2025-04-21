<?php

namespace Tests\Feature\Admin\AdmissionTest\Products;

use App\Models\AdmissionTestProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $product;

    private $happyCase = [
        'name' => 'Admission Test - Team',
        'minimum_age' => 14,
        'maximum_age' => 22,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test']);
        $this->product = AdmissionTestProduct::factory()
            ->state(['synced_to_stripe' => true])
            ->create();
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-test.products.update',
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
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
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
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_minimum_age_is_not_integer()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be an integer.']);
    }

    public function test_minimum_age_less_than_1()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = -1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be at least 1.']);
    }

    public function test_minimum_age_greater_than_255()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 256;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must not be greater than 255.']);
    }

    public function test_minimum_age_greater_than_maximum_age()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 13;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid([
            'minimum_age' => 'The minimum age field must be less than maximum age.',
            'maximum_age' => 'The maximum age field must be greater than minimum age.',
        ]);
    }

    public function test_maximum_age_is_not_integer()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be an integer.']);
    }

    public function test_maximum_age_less_than_1()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = -1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be at least 1.']);
    }

    public function test_maximum_age_greater_than_255()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 256;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must not be greater than 255.']);
    }

    public function test_happy_case_when_name_have_no_change()
    {
        $data = $this->happyCase;
        $data['name'] = $this->product->name;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertTrue((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
    }

    public function test_happy_case_when_name_has_change()
    {
        $data = $this->happyCase;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertFalse((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
    }
}
