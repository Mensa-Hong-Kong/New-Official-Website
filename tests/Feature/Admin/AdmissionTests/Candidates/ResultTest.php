<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\FailAdmissionTest;
use App\Notifications\AdmissionTest\Admin\PassAdmissionTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResultTest extends TestCase
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
        $response = $this->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('View:User');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertForbidden();
    }

    public function test_have_no_view_user_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => 0,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertNotFound();
    }

    public function test_candidate_is_not_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertNotFound();
    }

    public function test_before_than_expect_end_at()
    {
        $this->test->update(['expect_end_at' => now()->addSecond()]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertConflict();
        $response->assertJson(['message' => 'Cannot add result before expect end time.']);
    }

    public function test_candidate_is_absent()
    {
        AdmissionTestHasCandidate::where('test_id', $this->test->id)
            ->where('user_id', $this->user->id)
            ->update(['is_present' => false]);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 1]
        );
        $response->assertConflict();
        $response->assertJson(['message' => 'Cannot add result to absent candidate.']);

    }

    public function test_missing_status()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            )
        );
        $response->assertInvalid(['status' => 'The status field is required. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_status_is_not_boolean()
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => 'abc']
        );
        $response->assertInvalid(['status' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.']);
    }

    public function test_happy_case_when_pass()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => true]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The candidate of {$this->user->adornedName} changed to be pass.",
            'status' => true,
        ]);
        Notification::assertSentTo(
            [$this->user], PassAdmissionTest::class
        );
    }

    public function test_happy_case_when_fail()
    {
        Notification::fake();
        $this->user = User::find($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.result.update',
                [
                    'admission_test' => $this->test,
                    'candidate' => $this->user,
                ]
            ),
            ['status' => false]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The candidate of {$this->user->adornedName} changed to be fail.",
            'status' => false,
        ]);
        Notification::assertSentTo(
            [$this->user], FailAdmissionTest::class
        );
    }
}
