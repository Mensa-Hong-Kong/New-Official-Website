<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Orders\AdmissionTestOrderExpiredHandle;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionTestOrderExpiredHandleTest extends TestCase
{
    use RefreshDatabase;

    private $order;

    public function test_pending_order()
    {
        $order = AdmissionTestOrder::factory()
            ->state([
                'status' => 'pending',
                'expired_at' => now()->subSecond(),
            ])->create();
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(0, $test->candidates()->count());
        $this->assertEquals('expired', $order->fresh()->status);
    }

    public function test_non_pending_and_non_succeeded_order()
    {
        $status = fake()->randomElement(['cancelled', 'succeeded']);
        $order = AdmissionTestOrder::factory()
            ->state([
                'status' => 'cancelled',
                'expired_at' => now()->subSecond(),
            ])->create();
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(0, $test->candidates()->count());
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_succeeded_order()
    {
        $order = AdmissionTestOrder::factory()
            ->state([
                'status' => 'succeeded',
                'expired_at' => now()->subSecond(),
            ])->create();
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(1, $test->candidates()->count());
        $this->assertEquals('succeeded', $order->fresh()->status);
    }
}
