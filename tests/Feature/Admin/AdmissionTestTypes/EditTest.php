<?php

namespace Tests\Feature\Admin\AdmissionTestTypes;

use App\Models\AdmissionTestType;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.admission-test-types.edit',
                ['admission_test_type' => AdmissionTestType::factory()->create()]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-test-types.edit',
                    ['admission_test_type' => AdmissionTestType::factory()->create()]
                )
            );
        $response->assertForbidden();
    }

    public function test_team_is_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->get(
            route(
                'admin.admission-test-types.edit',
                ['admission_test_type' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-test-types.edit',
                    ['admission_test_type' => AdmissionTestType::factory()->create()]
                )
            );
        $response->assertSuccessful();
    }
}
