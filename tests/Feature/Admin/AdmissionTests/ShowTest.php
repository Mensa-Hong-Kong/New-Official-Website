<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
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
                                    ->etc();
                            }
                        )->etc();
                }
            );
    }

    public function test_happy_case_when_user_has_permission_and_is_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            fake()->randomElement([
                'Edit:Admission Test',
                'Edit:Admission Test Proctor',
                'View:Admission Test Candidate',
                'Edit:Admission Test Candidate',
                'View:Admission Test Result',
                'Edit:Admission Test Result',
            ])
        );
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
