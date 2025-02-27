<?php

namespace Tests\Feature\Admin\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\ContactHasVerification;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTest;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecordByQueue;
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
                'admin.admission-tests.destroy',
                ['admission_test' => $this->test]
            )
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-tests.destroy',
                ['admission_test' => $this->test]
            )
        );
        $response->assertForbidden();
    }

    public function test_admission_test_is_not_exist()
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.destroy',
                ['admission_test' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_before_testing_time()
    {
        Notification::fake();
        $this->test->update([
            'testing_at' => now()->addHour(),
            'expect_end_at' => now()->addHour()->addSecond(),
        ]);
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.destroy',
                ['admission_test' => $this->test]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The admission test delete success!']);
        Notification::assertSentTo(
            [$this->user], CanceledAdmissionTest::class
        );
    }

    public function test_happy_case_when_after_testing_time()
    {
        Notification::fake();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-tests.destroy',
                ['admission_test' => $this->test]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The admission test delete success!']);
        Notification::assertSentTo(
            [$this->user], RemovedAdmissionTestRecordByQueue::class
        );
    }
}
