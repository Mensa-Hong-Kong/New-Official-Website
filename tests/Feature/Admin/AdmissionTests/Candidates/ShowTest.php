<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private $test;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->test = AdmissionTest::factory()
            ->state([
                'testing_at' => now(),
                'expect_end_at' => now()->addHour(),
            ])->create();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test', 'View:User']);
        $this->test->candidates()->attach($this->user->id);
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.admission-tests.candidates.show',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_admission_test_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $this->user->givePermissionTo('View:User');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission_and_user_is_not_proctor()
    {
        $user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exists()
    {
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => 0,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertNotFound();
    }

    public function test_candidate_is_not_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $user,
                    ]
                )
            );
        $response->assertNotFound();
    }

    public function test_before_testing_at_more_than_2_hours()
    {
        $this->test->update(['testing_at' => now()->addHours(3)]);
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertConflict();
    }

    public function test_after_than_expect_end_at_more_than_1_hour()
    {
        $this->test->update(['expect_end_at' => now()->subHours(2)]);
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertGone();
    }

    public function test_happy_case_when_user_only_has_permission()
    {
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_is_proctor()
    {
        $user = User::factory()->create();
        $this->test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_has_permission_and_is_proctor()
    {
        $this->test->proctors()->attach($this->user->id);
        $response = $this->actingAs($this->user)
            ->get(
                route(
                    'admin.admission-tests.candidates.show',
                    [
                        'admission_test' => $this->test,
                        'candidate' => $this->user,
                    ]
                )
            );
        $response->assertSuccessful();
    }
}
