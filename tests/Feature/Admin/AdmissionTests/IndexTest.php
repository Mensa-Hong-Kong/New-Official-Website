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

    public function test_have_no_view_user_permission_and_proctor_tests()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertForbidden();
    }

    public function test_happy_case_when_user_only_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
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
        $user->givePermissionTo('Edit:Admission Test');
        $test = AdmissionTest::factory()->create();
        $test->proctors()->attach($user->id);
        $response = $this->actingAs($user)
            ->get(route('admin.admission-tests.index'));
        $response->assertSuccessful();
    }
}
