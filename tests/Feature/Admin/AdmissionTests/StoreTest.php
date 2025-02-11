<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'district_id' => 1,
        'address' => 'abc',
        'location' => 'xyz',
        'maximum_candidates' => 40,
        'is_public' => true,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test');
        $testingAt = now()->addMinute();
        $this->happyCase['testing_at'] = $testingAt->format('Y-m-d H:i:s');
        $this->happyCase['expect_end_at'] = $testingAt->addMinutes(30)->format('Y-m-d H:i:s');
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.admission-tests.store'),
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
            route('admin.admission-tests.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_district_id()
    {
        $data = $this->happyCase;
        unset($data['district_id']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['district_id' => 'The district field is required.']);
    }

    public function test_district_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['district_id'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['district_id' => 'The district field must be an integer.']);
    }

    public function test_district_id_is_not_exists_on_database()
    {
        $data = $this->happyCase;
        $data['district_id'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['district_id' => 'The selected district is invalid.']);
    }

    public function test_missing_address()
    {
        $data = $this->happyCase;
        unset($data['address']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['address' => 'The address field is required.']);
    }

    public function test_address_is_not_string()
    {
        $data = $this->happyCase;
        $data['address'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['address' => 'The address field must be a string.']);
    }

    public function test_address_too_long()
    {
        $data = $this->happyCase;
        $data['address'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['address' => 'The address field must not be greater than 255 characters.']);
    }

    public function test_missing_location()
    {
        $data = $this->happyCase;
        unset($data['location']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['location' => 'The location field is required.']);
    }

    public function test_location_is_not_string()
    {
        $data = $this->happyCase;
        $data['location'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['location' => 'The location field must be a string.']);
    }

    public function test_location_too_long()
    {
        $data = $this->happyCase;
        $data['location'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['location' => 'The location field must not be greater than 255 characters.']);
    }

    public function test_missing_testing_at()
    {
        $data = $this->happyCase;
        unset($data['testing_at']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['testing_at' => 'The testing at field is required.']);
    }

    public function test_testing_at_is_not_date()
    {
        $data = $this->happyCase;
        $data['testing_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['testing_at' => 'The testing at field must be a valid date.']);
    }

    public function test_missing_expect_end_at()
    {
        $data = $this->happyCase;
        unset($data['expect_end_at']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['expect_end_at' => 'The expect end at field is required.']);
    }

    public function test_expect_end_at_is_not_date()
    {
        $data = $this->happyCase;
        $data['expect_end_at'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['expect_end_at' => 'The expect end at field must be a valid date.']);
    }

    public function test_expect_end_at_before_testing_at()
    {
        $data = $this->happyCase;
        $data['expect_end_at'] = now()->subMinute()->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['expect_end_at' => 'The expect end at field must be a date after testing at.']);
    }

    public function test_missing_maximum_candidates()
    {
        $data = $this->happyCase;
        unset($data['maximum_candidates']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field is required.']);
    }

    public function test_maximum_candidates_is_not_integer()
    {
        $data = $this->happyCase;
        $data['maximum_candidates'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field must be an integer.']);
    }

    public function test_maximum_candidates_less_than_one()
    {
        $data = $this->happyCase;
        $data['maximum_candidates'] = 0;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field must be at least 1.']);
    }

    public function test_missing_is_public()
    {
        $data = $this->happyCase;
        unset($data['is_public']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['is_public' => 'The is public field is required.']);
    }

    public function test_is_public_is_not_boolean()
    {
        $data = $this->happyCase;
        $data['is_public'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $data
        );
        $response->assertInvalid(['is_public' => 'The is public field must be true or false.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-tests.store'),
            $this->happyCase
        );
        $test = AdmissionTest::first();
        $response->assertRedirectToRoute(
            'admin.admission-tests.show',
            ['admission_test' => $test]
        );

    }
}
