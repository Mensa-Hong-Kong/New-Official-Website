<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(route('admin.admission-tests.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_any_permission_to_view_admission_tests_and_proctor_tests()
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
            ->get(route('admin.admission-tests.index'));
        $response->assertForbidden();
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_proctor_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Proctor');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_view_admission_test_candidate_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:Admission Test Candidate');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_candidate_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Candidate');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_view_admission_test_result_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:Admission Test Result');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_edit_admission_test_result_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Result');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_has_proctor_tests()
    {
        $user = User::factory()->create();
        $test = AdmissionTest::factory()->create();
        $test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_has_permission_and_proctor_tests()
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
        $test = AdmissionTest::factory()->create();
        $test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }
}
