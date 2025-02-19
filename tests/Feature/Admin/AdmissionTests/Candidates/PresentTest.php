<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

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
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertForbidden();
    }

    public function test_not_exist_admission_test()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => 0,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertNotFound();
    }

    public function test_not_exist_candidate()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => 0,
                ]
            ),
            ['status' => 1]
        );
        $response->assertNotFound();
    }

    public function test_before_testing_at_more_than_2_hours()
    {
        $this->test->update(['testing_at' => now()->addHours(3)]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-tests.candidates.present',
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
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertGone();
        $response->assertJson(['message' => 'Could not access after than expect end time 1 hour.']);
    }

    public function test_has_same_passport_already_qualification_of_membership()
    {
        $test = AdmissionTest::factory()->create();
        $user = User::factory()
            ->state([
                'passport_type_id' => $this->user->passport_type_id,
                'passport_number' => $this->user->passport_number,
            ])->create();
        $test->candidates()->attach($user->id, ['is_pass' => true]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertConflict();
        $response->assertJson(['message' => 'The passport of user has already been qualification for membership.']);
    }

    public function test_has_same_passport_tested_within_date_range()
    {
        $test = AdmissionTest::factory()
            ->state([
                'testing_at' => $this->test->testing_at->subMonths(6)->addDay(),
                'expect_end_at' => $this->test->expect_end_at->subMonths(6)->addDay(),
            ])->create();
        $test->candidates()->attach($this->user->id, ['is_present' => true]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertConflict();
        $response->assertJson(['message' => 'The passport of user has admission test record within 6 months(count from testing at of this test sub 6 months to now).']);
    }

    public function test_has_same_passport_tested_two_times()
    {
        foreach (range(1, 2) as $times) {
            $oldTest = AdmissionTest::factory()
                ->state([
                    'testing_at' => $this->test->testing_at->subMonths(6)->subDay(),
                    'expect_end_at' => $this->test->expect_end_at->subMonths(6)->subDay(),
                ])->create();
            $oldTest->candidates()->attach($this->user->id, [
                'is_present' => 1,
                'is_pass' => 0,
            ]);
        }
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertConflict();
        $response->assertJson(['message' => 'The passport of user tested two times admission test.']);
    }

    public function test_missing_status()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertInvalid(['status' => 'The status field is required. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_status_is_not_boolean()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 'abc']
        );
        $response->assertInvalid(['status' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case()
    {
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.present',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => true]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The candidate of {$this->user->name} changed to be present.",
            'status' => true,
        ]);
    }
}
