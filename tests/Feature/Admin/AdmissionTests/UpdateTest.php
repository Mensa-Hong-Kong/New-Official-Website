<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\District;
use App\Models\Location;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
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

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test');
        $testingAt = now()->addMinute();
        $this->happyCase['testing_at'] = $testingAt->format('Y-m-d H:i:s');
        $this->happyCase['expect_end_at'] = $testingAt->addMinutes(30)->format('Y-m-d H:i:s');
        $this->test = AdmissionTest::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
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
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_permission_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => 0]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_missing_district_id()
    {
        $data = $this->happyCase;
        unset($data['district_id']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['district_id' => 'The district field is required.']);
    }

    public function test_district_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['district_id'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['district_id' => 'The district field must be an integer.']);
    }

    public function test_district_id_is_not_exists_on_database()
    {
        $data = $this->happyCase;
        $data['district_id'] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['district_id' => 'The selected district is invalid.']);
    }

    public function test_missing_address()
    {
        $data = $this->happyCase;
        unset($data['address']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['address' => 'The address field is required.']);
    }

    public function test_address_is_not_string()
    {
        $data = $this->happyCase;
        $data['address'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['address' => 'The address field must be a string.']);
    }

    public function test_address_too_long()
    {
        $data = $this->happyCase;
        $data['address'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['address' => 'The address field must not be greater than 255 characters.']);
    }

    public function test_missing_location()
    {
        $data = $this->happyCase;
        unset($data['location']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['location' => 'The location field is required.']);
    }

    public function test_location_is_not_string()
    {
        $data = $this->happyCase;
        $data['location'] = ['abc'];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['location' => 'The location field must be a string.']);
    }

    public function test_location_too_long()
    {
        $data = $this->happyCase;
        $data['location'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['location' => 'The location field must not be greater than 255 characters.']);
    }

    public function test_missing_testing_at()
    {
        $data = $this->happyCase;
        unset($data['testing_at']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['testing_at' => 'The testing at field is required.']);
    }

    public function test_testing_at_is_not_date()
    {
        $data = $this->happyCase;
        $data['testing_at'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
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
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field is required.']);
    }

    public function test_maximum_candidates_is_not_integer()
    {
        $data = $this->happyCase;
        $data['maximum_candidates'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field must be an integer.']);
    }

    public function test_maximum_candidates_less_than_one()
    {
        $data = $this->happyCase;
        $data['maximum_candidates'] = 0;
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['maximum_candidates' => 'The maximum candidates field must be at least 1.']);
    }

    public function test_missing_is_public()
    {
        $data = $this->happyCase;
        unset($data['is_public']);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['is_public' => 'The is public field is required.']);
    }

    public function test_is_public_is_not_boolean()
    {
        $data = $this->happyCase;
        $data['is_public'] = 'abc';
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $response->assertInvalid(['is_public' => 'The is public field must be true or false.']);
    }

    public function test_happy_case_with_no_change()
    {
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $this->test->address->district_id,
            'address' => $this->test->address->address,
            'location' => $this->test->location->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $response->assertSuccessful();
        $response->assertJson($data);
    }

    public function test_happy_case_change_all_without_address_and_location()
    {
        $this->test->update([
            'district_id' => 1,
            'maximum_candidates' => 40,
            'is_public' => true,
        ]);
        $now = now();
        $data = [
            'testing_at' => $now->format('Y-m-d H:i:s'),
            'expect_end_at' => $now->addMinutes(30)->format('Y-m-d H:i:s'),
            'district_id' => 2,
            'address' => 'abc',
            'location' => 'xyz',
            'maximum_candidates' => 80,
            'is_public' => 0,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $response->assertSuccessful();
        $response->assertJson($data);
    }

    public function test_happy_case_only_change_address_have_no_other_location_using_and_new_address_is_not_exist_on_database()
    {
        $addressID = $this->test->address->id;
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => District::inRandomOrder()
                ->whereNot('id', $this->test->address->district_id)
                ->first()
                ->id,
            'address' => $this->test->address->address,
            'location' => $this->test->location->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Address::find($addressID));
        $this->assertEquals($addressID, $this->test->address->id);
    }

    public function test_happy_case_only_change_address_have_no_other_location_using_and_new_address_exist_on_database()
    {
        $addressID = $this->test->address->id;
        $newAddress = Address::factory()->create();
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $newAddress->district_id,
            'address' => $newAddress->address,
            'location' => $this->test->location->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNull(Address::find($addressID));
        $this->assertEquals($newAddress->id, $this->test->address->id);
    }

    public function test_happy_case_only_change_address_has_other_location_using_and_new_address_is_not_exist_on_database()
    {
        AdmissionTest::factory()
            ->state([
                'address_id' => $this->test->address_id,
            ])->create();
        $addressID = $this->test->address->id;
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => District::inRandomOrder()
                ->whereNot('id', $this->test->address->district_id)
                ->first()
                ->id,
            'address' => $this->test->address->address,
            'location' => $this->test->location->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Address::find($addressID));
        $this->assertNotNull($this->test->address);
        $this->assertNotEquals($addressID, $this->test->address->id);
    }

    public function test_happy_case_only_change_address_has_other_location_using_and_new_address_exist_on_database()
    {
        AdmissionTest::factory()
            ->state([
                'address_id' => $this->test->address_id,
            ])->create();
        $addressID = $this->test->address->id;
        $newAddress = Address::factory()->create();
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $newAddress->district_id,
            'address' => $newAddress->address,
            'location' => $this->test->location->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Address::find($addressID));
        $this->assertEquals($newAddress->id, $this->test->address->id);
    }

    public function test_happy_case_only_change_location_have_no_other_test_using_and_new_location_is_not_exist_on_database()
    {
        $locationID = $this->test->location->id;
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $this->test->address->district_id,
            'address' => $this->test->address->address,
            'location' => fake()->company(),
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Location::find($locationID));
        $this->assertEquals($locationID, $this->test->location->id);
    }

    public function test_happy_case_only_change_location_have_no_other_test_using_and_new_location_exist_on_database()
    {
        $locationID = $this->test->location->id;
        $newLocation = Location::factory()->create();
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $this->test->address->district_id,
            'address' => $this->test->address->address,
            'location' => $newLocation->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNull(Location::find($locationID));
        $this->assertEquals($newLocation->id, $this->test->location->id);
    }

    public function test_happy_case_only_change_location_has_other_test_using_and_new_location_is_not_exist_on_database()
    {
        AdmissionTest::factory()
            ->state([
                'location_id' => $this->test->location_id,
            ])->create();
        $locationID = $this->test->location->id;
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $this->test->address->district_id,
            'address' => $this->test->address->address,
            'location' => fake()->company(),
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Location::find($locationID));
        $this->assertNotNull($this->test->location);
        $this->assertNotEquals($locationID, $this->test->location->id);
    }

    public function test_happy_case_only_change_location_has_other_test_using_and_new_location_exist_on_database()
    {
        AdmissionTest::factory()
            ->state([
                'location_id' => $this->test->location_id,
            ])->create();
        $locationID = $this->test->location->id;
        $newLocation = Location::factory()->create();
        $data = [
            'testing_at' => $this->test->testing_at,
            'expect_end_at' => $this->test->expect_end_at,
            'district_id' => $this->test->address->district_id,
            'address' => $this->test->address->address,
            'location' => $newLocation->name,
            'maximum_candidates' => $this->test->maximum_candidates,
            'is_public' => $this->test->is_public,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.update',
                ['admission_test' => $this->test]
            ),
            $data
        );
        $data['success'] = 'The admission test update success!';
        $this->test->refresh();
        $response->assertSuccessful();
        $response->assertJson($data);
        $this->assertNotNull(Location::find($locationID));
        $this->assertEquals($newLocation->id, $this->test->location->id);
    }
}
