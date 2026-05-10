<?php

namespace Tests\Feature\Admin\AdmissionTest\Products;

use App\Jobs\Stripe\Products\SyncAdmissionTest as SyncProduct;
use App\Models\AdmissionTestProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Model $product;

    private array $happyCase = [
        'name' => 'Admission Test',
        'option_name' => 'Adult',
        'quota' => 1,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test']);
        $this->product = AdmissionTestProduct::factory()
            ->state(['synced_to_stripe' => true])
            ->create();
    }

    public function test_have_no_login(): void
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

    public function test_have_no_edit_admission_test_permission(): void
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

    public function test_product_not_exists(): void
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

    public function test_missing_name(): void
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

    public function test_name_is_not_string(): void
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

    public function test_name_too_long(): void
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

    public function test_missing_option_name(): void
    {
        $data = $this->happyCase;
        unset($data['option_name']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['option_name' => 'The option name field is required.']);
    }

    public function test_option_name_is_not_string(): void
    {
        $data = $this->happyCase;
        $data['option_name'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['option_name' => 'The option name field must be a string.']);
    }

    public function test_option_name_too_long(): void
    {
        $data = $this->happyCase;
        $data['option_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['option_name' => 'The option name field must not be greater than 255 characters.']);
    }

    public function test_minimum_age_is_not_integer(): void
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

    public function test_minimum_age_less_than_1(): void
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

    public function test_minimum_age_greater_than_255(): void
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

    public function test_minimum_age_greater_than_maximum_age(): void
    {
        $data = $this->happyCase;
        $data['minimum_age'] = 14;
        $data['maximum_age'] = 13;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid([
            'minimum_age' => 'The minimum age field must be less than maximum age field.',
            'maximum_age' => 'The maximum age field must be greater than minimum age field.',
        ]);
    }

    public function test_maximum_age_is_not_integer(): void
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

    public function test_maximum_age_less_than_1(): void
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

    public function test_maximum_age_greater_than_255(): void
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

    public function test_start_at_is_not_date(): void
    {
        $data = $this->happyCase;
        $data['start_at'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['start_at' => 'The start at field must be a valid date.']);
    }

    public function test_start_at_after_than_end_at(): void
    {
        $now = now();
        $data = $this->happyCase;
        $data['end_at'] = $now;
        $data['start_at'] = $now->addHour();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid([
            'start_at' => 'The start at field must be a date before end at field.',
            'end_at' => 'The end at field must be a date after start at field.',
        ]);
    }

    public function test_end_at_is_not_date(): void
    {
        $data = $this->happyCase;
        $data['end_at'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['end_at' => 'The end at field must be a valid date.']);
    }

    public function test_missing_quota(): void
    {
        $data = $this->happyCase;
        unset($data['quota']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field is required.']);
    }

    public function test_quota_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['quota'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be an integer.']);
    }

    public function test_quota_less_than_1(): void
    {
        $data = $this->happyCase;
        $data['quota'] = -1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must be at least 1.']);
    }

    public function test_quota_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['quota'] = 256;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota' => 'The quota field must not be greater than 255.']);
    }

    public function test_quota_validity_months_is_not_integer(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must be an integer.']);
    }

    public function test_quota_validity_months_less_than_0(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = -1;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must be at least 0.']);
    }

    public function test_quota_validity_months_greater_than_255(): void
    {
        $data = $this->happyCase;
        $data['quota_validity_months'] = 256;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $response->assertInvalid(['quota_validity_months' => 'The quota validity months field must not be greater than 255.']);
    }

    public function test_happy_case_when_name_and_quota_validity_months_have_no_change(): void
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['name'] = $this->product->name;
        $data['quota_validity_months'] = $this->product->quota_validity_months;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $data['start_at'] = null;
        $data['end_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertTrue((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_only_name_has_change(): void
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['quota_validity_months'] = $this->product->quota_validity_months;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $data['start_at'] = null;
        $data['end_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertFalse((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
        Queue::assertPushed(SyncProduct::class);
    }

    public function test_happy_case_when_only_quota_validity_months_has_change(): void
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['name'] = $this->product->name;
        $data['quota_validity_months'] = $this->product->quota_validity_months == 0 ? 1 : 0;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $data['start_at'] = null;
        $data['end_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertTrue((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
        Queue::assertNothingPushed();
    }

    public function test_happy_case_when_name_and_quota_validity_months_have_change(): void
    {
        Queue::fake();
        $data = $this->happyCase;
        $data['name'] = 'Admission Test 2';
        $data['quota_validity_months'] = 12;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-test.products.update',
                ['product' => $this->product]
            ),
            $data
        );
        $data['success'] = 'The admission test product update success.';
        $data['start_at'] = null;
        $data['end_at'] = null;
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertFalse((bool) AdmissionTestProduct::find($this->product->id)->synced_to_stripe);
        Queue::assertPushed(SyncProduct::class);
    }
}
