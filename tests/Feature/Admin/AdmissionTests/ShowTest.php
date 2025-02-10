<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_have_no_view_user_permission_and_user_is_not_proctor()
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
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertForbidden();
    }

    public function test_user_have_no_permission_and_is_proctor_but_no_in_testing_time_range()
    {
        $user = User::factory()->create();
        $this->test->update(['testing_at' => now()->subHours(2)->subSecond()]);
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

    public function test_happy_case_when_user_only_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)
            ->get(
                route(
                    'admin.admission-tests.show',
                    ['admission_test' => $this->test]
                )
            );
        $response->assertSuccessful();
    }

    public function test_happy_case_when_user_only_is_proctor()
    {
        $user = User::factory()->create();
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

    public function test_happy_case_when_user_has_permission_and_is_proctor()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
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
