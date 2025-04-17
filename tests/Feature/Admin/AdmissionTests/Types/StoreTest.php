<?php

namespace Tests\Feature\Admin\AdmissionTests\Types;

use App\Models\AdmissionTestType;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $happyCase = [
        'name' => 'abc',
        'interval_month' => 6,
        'is_active' => true,
        'display_order' => 0,
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test');
    }

    public function test_have_no_login()
    {
        $response = $this->postJson(
            route('admin.admission-test-types.store'),
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
            route('admin.admission-test-types.store'),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_missing_name()
    {
        $data = $this->happyCase;
        unset($data['name']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field is required.']);
    }

    public function test_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['name'] = ['abc'];
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must be a string.']);
    }

    public function test_name_too_long()
    {
        $data = $this->happyCase;
        $data['name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name field must not be greater than 255 characters.']);
    }

    public function test_name_is_used()
    {
        $data = $this->happyCase;
        AdmissionTestType::factory()->state(['name' => $data['name']])->create();
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['name' => 'The name has already been taken.']);
    }

    public function test_missing_interval_month()
    {
        $data = $this->happyCase;
        unset($data['interval_month']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['interval_month' => 'The interval month field is required.']);
    }

    public function test_interval_month_is_not_integer()
    {
        $data = $this->happyCase;
        $data['interval_month'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['interval_month' => 'The interval month field must be an integer.']);
    }

    public function test_interval_month_less_than_zero()
    {
        $data = $this->happyCase;
        $data['interval_month'] = -1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['interval_month' => 'The interval month field must be at least 0.']);
    }

    public function test_interval_month_more_than_60()
    {
        $data = $this->happyCase;
        $data['interval_month'] = 61;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['interval_month' => 'The interval month field must not be greater than 60.']);
    }

    public function test_missing_is_active()
    {
        $data = $this->happyCase;
        unset($data['is_active']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['is_active' => 'The is active field is required.']);
    }

    public function test_is_active_not_boolean()
    {
        $data = $this->happyCase;
        $data['is_active'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['is_active' => 'The is active field must be true or false.']);
    }

    public function test_missing_display_order()
    {
        $data = $this->happyCase;
        unset($data['display_order']);
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_integer()
    {
        $data = $this->happyCase;
        $data['display_order'] = 'abc';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an integer.']);
    }

    public function test_display_order_less_than_zero()
    {
        $data = $this->happyCase;
        $data['display_order'] = '-1';
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must be at least 0.']);
    }

    public function test_display_order_more_than_zero_when_have_no_types_on_database()
    {
        $data = $this->happyCase;
        $data['display_order'] = 1;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than 0.']);
    }

    public function test_display_order_more_than_max_plus_one_when_has_type_on_database()
    {
        $data = $this->happyCase;
        AdmissionTestType::factory()->create();
        $data['display_order'] = 2;
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $data
        );
        $response->assertInvalid(['display_order' => 'The display order field must not be greater than '.$data['display_order'] - 1 .'.']);
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)->postJson(
            route('admin.admission-test-types.store'),
            $this->happyCase
        );
        $response->assertRedirectToRoute('admin.index');
    }
}
