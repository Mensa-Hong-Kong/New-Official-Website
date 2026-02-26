<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->test = AdmissionTest::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.admission-tests.show',
                ['admission_test' => $this->test]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_any_permission_to_view_admission_tests_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNotIn(
                    'name',
                    [
                        'Edit:Admission Test',
                        'Edit:Admission Test Proctor',
                        'View:Admission Test Candidate',
                        'Edit:Admission Test Candidate',
                        'View:Admission Test Result',
                        'Edit:Admission Test Result',
                    ]
                )->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertForbidden();
    }

    public function test_user_have_no_any_permission_and_is_proctor_but_no_in_testing_time_range()
    {
        $user = User::factory()->create();
        $this->test->update([
            'testing_at' => now()->subHours(2)->subSecond(),
            'expect_end_at' => now()->subHour()->subSecond(),
        ]);
        $this->test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertForbidden();
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $this->test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctors')
                                    ->missing('candidates')
                                    ->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_proctor_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Proctor');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->has(
                                    'proctors.0', function (Assert $page) {
                                        $page->has('id')
                                            ->has('adorned_name');
                                    }
                                )->missing('candidates')
                                ->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_admission_test_candidate_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo('View:Admission Test Candidate');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('has_result');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_candidate_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Candidate');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('has_result');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo('View:Admission Test Result');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('birthday')
                                                ->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Result');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('birthday')
                                                ->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_and_view_admission_test_candidate_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'Edit:Admission Test',
            'View:Admission Test Candidate',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('has_result');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_and_admission_test_candidate_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'Edit:Admission Test',
            'Edit:Admission Test Candidate',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('has_result');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_and_view_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'Edit:Admission Test',
            'View:Admission Test Result',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('birthday')
                                                ->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_and_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'Edit:Admission Test',
            'Edit:Admission Test Result',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('birthday')
                                                ->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_and_edit_admission_test_candidate_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'View:Admission Test Candidate',
            'Edit:Admission Test Candidate',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('has_result');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_admission_test_candidate_and_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'View:Admission Test Candidate',
            'View:Admission Test Result',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_admission_test_candidate_and_edit_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'View:Admission Test Candidate',
            'Edit:Admission Test Result',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('is_free')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_has_view_and_edit_admission_test_result_permission()
    {
        $proctor = User::factory()->create();
        $this->test->proctors()->attach($proctor->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $user = User::factory()->create();
        $user->givePermissionTo([
            'View:Admission Test Result',
            'Edit:Admission Test Result',
        ]);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctor')
                                    ->has(
                                    'candidates.0', function (Assert $page) {
                                            $page->has('birthday')
                                                ->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_pass');
                                                    }
                                                );
                                    }
                                )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_only_is_proctor_and_in_testing_time_range()
    {
        $user = User::factory()->create();
        $this->test->update([
            'testing_at' => now()->subMinutes(30),
            'expect_end_at' => now()->addMinutes(30),
        ]);
        $this->test->proctors()->attach($user->id);
        $candidate = User::factory()->create();
        $this->test->candidates()->attach($candidate->id);
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach(
            $candidate->id,
            ['is_present' => true]
        );
        $test = AdmissionTest::factory()->state([
            'testing_at' => $this->test->testing_at->subMonths($this->test->type->interval_month),
            'expect_end_at' => $this->test->expect_end_at->subMonths($this->test->type->interval_month),
        ])->create();
        $test->candidates()->attach($candidate->id);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful()
            ->assertInertia(
                function (Assert $page) {
                    $page->component('Admin/AdmissionTests/Show')
                        ->has(
                            'test', function (Assert $page) {
                                $page->missing('proctors')
                                    ->has(
                                        'candidates.0', function (Assert $page) {
                                            $page->has('id')
                                                ->has('adorned_name')
                                                ->has(
                                                    'passport_type', function (Assert $page) {
                                                        $page->has('name');
                                                    }
                                                )->has('passport_number')
                                                ->has('birthday')
                                                ->has('has_other_same_passport_user_joined_future_test')
                                                ->has('has_other_same_passport_user_attended_admission_test')
                                                ->has('has_same_passport_already_qualification_of_membership')
                                                ->has(
                                                    'last_attended_admission_test', function(Assert $page) {
                                                        $page->missing('id')
                                                            ->has('testing_at')
                                                            ->has(
                                                                'type', function (Assert $page) {
                                                                    $page->has('interval_month');
                                                                }
                                                            );
                                                    }
                                                )->has(
                                                    'pivot', function(Assert $page) {
                                                        $page->has('seat_number')
                                                            ->has('is_present')
                                                            ->has('has_result');
                                                    }
                                                );
                                        }
                                    )->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_has_permission_and_is_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            'Edit:Admission Test',
            'Edit:Admission Test Proctor',
            'View:Admission Test Candidate',
            'Edit:Admission Test Candidate',
            'View:Admission Test Result',
            'Edit:Admission Test Result',
        ]);
        $this->test->update(['testing_at' => now()]);
        $this->test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful();
    }
}
