<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    private $happyCase = [
        'family_name' => 'LEE',
        'given_name' => 'Chi Nan',
        'passport_type_id' => 2,
        'passport_number' => 'C668668E',
        'gender' => 'Female',
    ];

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test', 'View:User']);
        $this->test = AdmissionTest::factory()
            ->state([
                'testing_at' => now(),
                'expect_end_at' => now()->addHour(),
            ])->create();
        $this->test->candidates()->attach($this->user->id);
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            $this->happyCase
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            $this->happyCase
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => 0,
                    'candidate' => $this->user,
                ]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_candidate_is_not_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $user,
                ]
            ),
            $this->happyCase
        );
        $response->assertNotFound();
    }

    public function test_before_testing_at_more_than_2_hours()
    {
        $this->test->update(['testing_at' => now()->addHours(3)]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertConflict();
        $response->assertJson(['message' => 'Could not access before than testing time 2 hours.']);
    }

    public function test_after_than_expect_end_at_more_than_1_hour()
    {
        $this->test->update(['expect_end_at' => now()->subHour()->subSecond()]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            $this->happyCase
        );
        $response->assertGone();
        $response->assertJson(['message' => 'Could not access after than expect end time 1 hour.']);
    }

    public function test_missing_family_name()
    {
        $data = $this->happyCase;
        unset($data['family_name']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['family_name' => 'The family name field is required.']);
    }

    public function test_family_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['family_name'] = ['Chan'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['family_name' => 'The family name field must be a string.']);
    }

    public function test_family_name_too_long()
    {
        $data = $this->happyCase;
        $data['family_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['family_name' => 'The family name field must not be greater than 255 characters.']);
    }

    public function test_middle_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['middle_name'] = ['Chan'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['middle_name' => 'The middle name field must be a string.']);
    }

    public function test_middle_name_too_long()
    {
        $data = $this->happyCase;
        $data['middle_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['middle_name' => 'The middle name field must not be greater than 255 characters.']);
    }

    public function test_missing_given_name()
    {
        $data = $this->happyCase;
        unset($data['given_name']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['given_name' => 'The given name field is required.']);
    }

    public function test_given_name_is_not_string()
    {
        $data = $this->happyCase;
        $data['given_name'] = ['Diamond'];
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['given_name' => 'The given name field must be a string.']);
    }

    public function test_given_name_too_long()
    {
        $data = $this->happyCase;
        $data['given_name'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['given_name' => 'The given name field must not be greater than 255 characters.']);
    }

    public function test_missing_passport_type_id()
    {
        $data = $this->happyCase;
        unset($data['passport_type_id']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_type_id' => 'The passport type field is required.']);
    }

    public function test_passport_type_id_is_not_integer()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 'abc';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_type_id' => 'The passport type id field must be an integer.']);
    }

    public function test_passport_type_id_is_not_exist()
    {
        $data = $this->happyCase;
        $data['passport_type_id'] = 0;
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_type_id' => 'The selected passport type is invalid.']);
    }

    public function test_missing_passport_number()
    {
        $data = $this->happyCase;
        unset($data['passport_number']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field is required.']);
    }

    public function test_passport_number_format_not_match()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567$';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field format is invalid.']);
    }

    public function test_passport_number_too_short()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field must be at least 8 characters.']);
    }

    public function test_passport_number_too_long()
    {
        $data = $this->happyCase;
        $data['passport_number'] = '1234567890123456789';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['passport_number' => 'The passport number field must not be greater than 18 characters.']);
    }

    public function test_missing_gender()
    {
        $data = $this->happyCase;
        unset($data['gender']);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['gender' => 'The gender field is required.']);
    }

    public function test_gender_too_long()
    {
        $data = $this->happyCase;
        $data['gender'] = str_repeat('a', 256);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertInvalid(['gender' => 'The gender field must not be greater than 255 characters.']);
    }

    public function test_happy_case_without_middle_name()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $this->happyCase
            );
        $response->assertRedirectToRoute(
            'admin.admission-tests.candidates.show',
            [
                'admission_test' => $this->test,
                'candidate' => $this->user,
            ]
        );
        $this->user->refresh();
        $this->assertEquals($this->happyCase['family_name'], $this->user->family_name);
        $this->assertEquals($this->happyCase['given_name'], $this->user->given_name);
        $this->assertEquals($this->happyCase['passport_type_id'], $this->user->passport_type_id);
        $this->assertEquals($this->happyCase['passport_number'], $this->user->passport_number);
        $this->assertEquals($this->happyCase['gender'], $this->user->gender->name);
    }

    public function test_happy_case_with_middle_name()
    {
        $data = $this->happyCase;
        $data['middle_name'] = 'intelligent';
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.update',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                ),
                $data
            );
        $response->assertRedirectToRoute(
            'admin.admission-tests.candidates.show',
            [
                'admission_test' => $this->test,
                'candidate' => $this->user,
            ]
        );
        $this->user->refresh();
        $this->assertEquals($data['family_name'], $this->user->family_name);
        $this->assertEquals($data['middle_name'], $this->user->middle_name);
        $this->assertEquals($data['given_name'], $this->user->given_name);
        $this->assertEquals($data['passport_type_id'], $this->user->passport_type_id);
        $this->assertEquals($data['passport_number'], $this->user->passport_number);
        $this->assertEquals($data['gender'], $this->user->gender->name);
    }
}
