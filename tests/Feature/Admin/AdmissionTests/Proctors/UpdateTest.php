<?php

namespace Tests\Feature\Admin\AdmissionTests\Proctors;

use App\Models\AdmissionTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $test;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['Edit:Admission Test', 'View:User']);
        $this->test = AdmissionTest::factory()->create();
        $this->test->proctors()->attach($this->user->id);
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
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
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
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
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertNotFound();
    }

    public function test_missing_user_id()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
        );
        $response->assertInvalid(['user_id' => 'The user id field is required.']);
    }

    public function test_user_id_is_not_integer()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => 'abc']
        );
        $response->assertInvalid(['user_id' => 'The user id field must be an integer.']);
    }

    public function test_user_id_is_exists_proctor_for_this_admission_test()
    {
        $user = User::factory()->create();
        $this->test->proctors()->attach($user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertInvalid(['user_id' => 'The user id has already been taken.']);
    }

    public function test_user_id_is_not_exists_on_database()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => 0]
        );
        $response->assertInvalid(['user_id' => 'The selected user id is invalid.']);
    }

    public function test_happy_case_when_no_change()
    {
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $this->user->id]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The proctor update success',
            'user_id' => $this->user->id,
            'name' => $this->user->adornedName,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $this->user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            'delete_proctor_url' => route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
        ]);
    }

    public function test_happy_case_when_change()
    {
        $user = User::find(User::factory()->create()->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $this->user,
                ]
            ),
            ['user_id' => $user->id]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The proctor update success',
            'user_id' => $user->id,
            'name' => $user->adornedName,
            'show_user_url' => route(
                'admin.users.show',
                ['user' => $user]
            ),
            'update_proctor_url' => route(
                'admin.admission-tests.proctors.update',
                [
                    'admission_test' => $this->test,
                    'proctor' => $user,
                ]
            ),
            'delete_proctor_url' => route(
                'admin.admission-tests.proctors.destroy',
                [
                    'admission_test' => $this->test,
                    'proctor' => $user,
                ]
            ),
        ]);
    }
}
