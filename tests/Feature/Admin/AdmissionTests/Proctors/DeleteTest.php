<?php

namespace Tests\Feature\Admin\AdmissionTests\Proctors;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasProctor;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test Proctor');
        $this->test = AdmissionTest::factory()->create();
        $this->test->proctors()->attach($this->user->id);
    }

    public function test_have_no_login()
    {
        $response = $this->deleteJson(
            route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_proctor_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test Proctor')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => 0,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertNotFound();
    }

    public function test_not_exist_proctor_in_admission_test()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $this->test,
                    'proctor' => $user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson(
                route(
                    'admin.admission-tests.proctors.destroy',
                    [
                        'admission_test' => $this->test,
                        'proctor' => $this->user,
                    ]
                ),
            );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The proctor delete success!']);
        $this->assertNull(
            AdmissionTestHasProctor::where('test_id', $this->test->id)
                ->where('user_id', $this->user->id)
                ->first()
        );
    }
}
