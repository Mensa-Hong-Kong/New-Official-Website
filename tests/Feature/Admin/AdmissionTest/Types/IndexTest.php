<?php

namespace Tests\Feature\Admin\AdmissionTest\Types;

use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_login(): void
    {
        $response = $this->get(route('admin.admission-test.types.index'));
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_admission_test_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.types.index'));
        $response->assertForbidden();
    }

    public function test_happy_case(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)
            ->get(route('admin.admission-test.types.index'));
        $response->assertSuccessful();
    }
}
