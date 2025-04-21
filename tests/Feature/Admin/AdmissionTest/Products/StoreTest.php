<?php

namespace Tests\Feature\Admin\AdmissionTest\Products;

use App\Models\AdmissionTestProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

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
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.admission-test.products.store'),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->postJson(
            route('admin.admission-test.products.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_minimum_age_is_not_integer()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be an integer.']);
    }

    public function test_minimum_age_less_than_1()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must be at least 1.']);
    }

    public function test_minimum_age_greater_than_255()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['minimum_age' => 'The minimum age field must not be greater than 255.']);
    }

    public function test_minimum_age_greater_than_maximum_age()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 13;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
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
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be an integer.']);
    }

    public function test_maximum_age_less_than_1()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must be at least 1.']);
    }

    public function test_maximum_age_greater_than_255()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['maximum_age' => 'The maximum age field must not be greater than 255.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
    }
}
