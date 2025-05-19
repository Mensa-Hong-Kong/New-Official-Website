<?php

namespace Tests\Feature\Admin\AdmissionTest\Products;

use App\Jobs\Stripe\Products\SyncAdmissionTest as SyncProduct;
use App\Models\AdmissionTestProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'name' => 'Admission Test',
        'quota' => 1,
        'price' => 400,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test']);
        Queue::fake();
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
        $data['minimum_age'] = 14;
        $data['maximum_age'] = 13;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid([
            'minimum_age' => 'The minimum age field must be less than maximum age field.',
            'maximum_age' => 'The maximum age field must be greater than minimum age field.',
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

    public function test_start_at_after_than_end_at()
    {
        $now = now();
        $data = $this->happyCase;
        $data['end_at'] = $now;
        $data['start_at'] = $now->addHour();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid([
            'start_at' => 'The start at field must be a date before end at field.',
            'end_at' => 'The end at field must be a date after start at field.',
        ]);
    }

    public function test_end_at_is_not_date()
    {
        $data = $this->happyCase;
        $data['end_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['end_at' => 'The end at field must be a valid date.']);
    }

    public function test_missing_quota()
    {
        $data = $this->happyCase;
        unset($data['quota']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field is required.']);
    }

    public function test_quota_is_not_integer()
    {
        $data = $this->happyCase;
        $data['quota'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be an integer.']);
    }

    public function test_quota_less_than_1()
    {
        $data = $this->happyCase;
        $data['quota'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be at least 1.']);
    }

    public function test_quota_greater_than_255()
    {
        $data = $this->happyCase;
        $data['quota'] = 256;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must not be greater than 255.']);
    }

    public function test_price_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['price_name'] = ['abe'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must be a string.']);
    }

    public function test_price_name_too_long()
    {
        $data = $this->happyCase;
        $data['price_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price_name' => 'The price name field must not be greater than 255 characters.']);
    }

    public function test_missing_price()
    {
        $data = $this->happyCase;
        unset($data['price']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field is required.']);
    }

    public function test_price_is_not_integer()
    {
        $data = $this->happyCase;
        $data['price'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be an integer.']);
    }

    public function test_price_less_that_1()
    {
        $data = $this->happyCase;
        $data['price'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must be at least 1.']);
    }

    public function test_price_greater_than_65535()
    {
        $data = $this->happyCase;
        $data['price'] = 65536;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertInvalid(['price' => 'The price field must not be greater than 65535.']);
    }

    public function test_happy_case_without_all_nullable_field()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_minimum_age()
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 14;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_maximum_age()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 22;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_minimum_and_maximum_age()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 14;
        $data['maximum_age'] = 22;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_start_at()
    {
        $data = $this->happyCase;
        $data['start_at'] = now()->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_end_at()
    {
        $data = $this->happyCase;
        $data['end_at'] = now()->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_start_and_end_at()
    {
        $data = $this->happyCase;
        $data['start_at'] = now()->format('Y-m-d H:i:s');
        $data['end_at'] = now()->addDay()->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_price_name()
    {
        $data = $this->happyCase;
        $data['price_name'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_with_all_nullable_field()
    {
        $data = $this->happyCase;
        $data['maximum_age'] = 14;
        $data['maximum_age'] = 22;
        $data['start_at'] = now()->format('Y-m-d H:i:s');
        $data['end_at'] = now()->addDay()->format('Y-m-d H:i:s');
        $data['price_name'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test.products.store'),
            $data
        );
        $response->assertRedirectToRoute(
            'admin.admission-test.products.show',
            ['product' => AdmissionTestProduct::latest('id')->first()]
        );
        Queue::assertPushed(SyncProduct::class);
    }
}
