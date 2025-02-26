<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTestAppointment;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
        $this->user->givePermissionTo(['Edit:Admission Test', 'View:User']);
        $this->test = AdmissionTest::factory()
            ->state([
                'testing_at' => now()->subSecond()->subHour(),
                'expect_end_at' => now()->subSecond(),
            ])->create();
        $this->test->candidates()->attach($this->user->id, ['is_present' => true]);
        $contact = UserHasContact::factory()
            ->state([
                'user_id' => $this->user->id,
                'is_default' => true,
            ])->create();
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $this->user->id,
            'creator_ip' => '127.0.0.1',
        ]);
    }

    public function test_have_no_login()
    {
        $response = $this->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
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
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $user,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_have_no_test_result()
    {
        Notification::fake();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The candidate delete success!']);
        Notification::assertSentTo(
            [$this->user], CanceledAdmissionTestAppointment::class
        );
    }

    public function test_happy_case_when_has_test_result()
    {
        Notification::fake();
        AdmissionTestHasCandidate::where('test_id', $this->test->id)
            ->where('user_id', $this->user->id)
            ->update(['is_pass' => true]);
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.candidates.destroy',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The candidate delete success!']);
        Notification::assertSentTo(
            [$this->user], RemovedAdmissionTestRecord::class
        );
    }
}
