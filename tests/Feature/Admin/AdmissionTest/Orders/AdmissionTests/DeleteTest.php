<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders\AdmissionTests;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\AdmissionTestOrder;
use App\Models\ContactHasVerification;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTestAppointment;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecord;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @var User&Authenticatable */
    private User $user;

    private AdmissionTest $test;

    private AdmissionTestOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->order = AdmissionTestOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'succeeded',
        ]);
        $this->user->givePermissionTo([
            'Edit:Admission Test Order',
            'Edit:Admission Test Candidate',
        ]);
        $this->test = AdmissionTest::factory()->create([
            'testing_at' => now()->subSecond()->subHour(),
            'expect_end_at' => now()->subSecond(),
        ]);
        $this->test->candidates()->attach(
            $this->user->id, [
                'order_id' => $this->order->id,
                'is_present' => true,
            ]
        );
        $contact = UserHasContact::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $this->user->id,
            'creator_ip' => '127.0.0.1',
        ]);
    }

    public function test_have_no_login(): void
    {
        $response = $this->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_admission_test_order_permission()
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Candidate');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertForbidden();
    }

    public function test_have_no_edit_admission_test_candidate_permission(): void
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test Order');
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertForbidden();
    }

    public function test_has_no_edit_admission_test_order_and_candidate_permission()
    {
        /** @var User&Authenticatable */
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNotIn(
                    'name',
                    [
                        'Edit:Admission Test Order',
                        'Edit:Admission Test Candidate',
                    ]
                )->first()
                ->name
        );
        $response = $this->actingAs($user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertForbidden();
    }

    public function test_admission_test_order_is_not_exist(): void
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => 0,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_admission_test_is_not_exist(): void
    {
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    0,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_admission_test_order_has_no_admission_test_is_not_exist(): void
    {
        $this->order->tests()->detach($this->test->id);
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case_when_have_no_test_result(): void
    {
        Notification::fake();
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The admission test delete success!']);
        Notification::assertSentTo(
            [$this->user], CanceledAdmissionTestAppointment::class
        );
    }

    public function test_happy_case_when_has_test_result(): void
    {
        Notification::fake();
        AdmissionTestHasCandidate::where('test_id', $this->test->id)
            ->where('user_id', $this->user->id)
            ->update(['is_passed' => true]);
        $response = $this->actingAs($this->user)->deleteJson(
            route(
                'admin.admission-test.orders.admission-tests.destroy',
                [
                    'order' => $this->order,
                    'admission_test' => $this->test,
                ]
            )
        );
        $response->assertSuccessful();
        $response->assertJson(['success' => 'The admission test delete success!']);
        Notification::assertSentTo(
            [$this->user], RemovedAdmissionTestRecord::class
        );
    }
}
