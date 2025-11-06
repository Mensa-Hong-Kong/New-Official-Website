<?php

namespace Tests\Feature\Admin\AdmissionTest\Orders;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\ContactHasVerification;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserHasContact;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateStatusTest extends TestCase
{
    use RefreshDatabase;

    private $order;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->order = AdmissionTestOrder::factory()->state(['status' => 'pending'])->create();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test Order');
    }

    public function test_have_no_login()
    {
        $response = $this->putJson(
            route(
                'admin.admission-test.orders.status.update',
                ['order' => $this->order]
            ),
            ['status' => fake()->randomElement(['canceled', 'succeeded'])]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test Order')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => fake()->randomElement(['canceled', 'succeeded'])]
            );
        $response->assertForbidden();
    }
    
    public function test_missing_status_field()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
            );
        $response->assertInvalid(['status' => 'The status field is required.']);
    }
    
    public function test_status_is_not_string()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => ['abc']]
            );
        $response->assertInvalid(['status' => 'The status field must be a string.']);
    }
    
    public function test_status_is_invalid()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => 'abc']
            );
        $response->assertInvalid(['status' => 'The status field does not exist in canceled, succeeded.']);
    }

    public function test_order_has_been_expected()
    {
        $this->order->update(['expired_at' => now()->subSecond()]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => fake()->randomElement(['canceled', 'succeeded'])]
            );
        $response->assertGone();
        $response->assertJson(['message' => 'The order has been expected.']);
    }

    public function test_order_status_is_not_pending()
    {
        $status = fake()->randomElement(['canceled', 'failed', 'expired', 'succeeded']);
        $this->order->update(['status' => $status]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => fake()->randomElement(['canceled', 'succeeded'])]
            );
        $response->assertGone();
        $response->assertJson(['message' => "The order has been $status, cannot change to succeeded."]);
    }

    public function test_update_to_succeeded_case_when_has_test_but_user_have_no_default_contact()
    {
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($this->order->user_id, ['order_id' => $this->order->id]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => 'succeeded']
            );
        $response->assertConflict();
        $response->assertJson(['message' => 'The selected user must at least has one default contact.']);
    }

    public function test_happy_update_canceled_case()
    {
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => 'canceled']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The order status changed to canceled",
            'status' => 'canceled',
        ]);
    }

    public function test_happy_update_succeeded_case_when_have_no_test()
    {
        Notification::fake();
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => 'succeeded']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The order status changed to succeeded",
            'status' => 'succeeded',
        ]);
        Notification::assertNothingSent();
    }

    public function test_happy_update_succeeded_case_when_has_test()
    {
        Notification::fake();
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($this->order->user_id, ['order_id' => $this->order->id]);
        $contact = UserHasContact::factory()
            ->state([
                'user_id' => $this->order->user_id,
                'is_default' => true,
            ])->create();
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $this->order->user_id,
            'creator_ip' => '127.0.0.1',
        ]);
        $response = $this->actingAs($this->user)
            ->putJson(
                route(
                    'admin.admission-test.orders.status.update',
                    ['order' => $this->order]
                ),
                ['status' => 'succeeded']
            );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => "The order status changed to succeeded",
            'status' => 'succeeded',
        ]);
        Notification::assertSentTo(
            [$this->order->user], ScheduleAdmissionTest::class
        );
    }
}
