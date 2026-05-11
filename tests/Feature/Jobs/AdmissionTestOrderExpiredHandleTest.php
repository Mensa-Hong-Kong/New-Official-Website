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

    public function test_pending_order(): void
    {
        $order = AdmissionTestOrder::factory()->create([
            'status' => 'pending',
            'expired_at' => now()->subSecond(),
        ]);
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(0, $test->candidates()->count());
        $this->assertEquals('expired', $order->fresh()->status);
    }

    public function test_non_pending_and_non_succeeded_order(): void
    {
        $status = fake()->randomElement(['canceled', 'failed']);
        $order = AdmissionTestOrder::factory()->create([
            'status' => $status,
            'expired_at' => now()->subSecond(),
        ]);
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(0, $test->candidates()->count());
        $this->assertEquals($status, $order->fresh()->status);
    }

    public function test_succeeded_order(): void
    {
        $order = AdmissionTestOrder::factory()->create([
            'status' => 'succeeded',
            'expired_at' => now()->subSecond(),
        ]);
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($order->user_id, ['order_id' => $order->id]);
        app()->call([new AdmissionTestOrderExpiredHandle($order->id), 'handle']);
        $this->assertEquals(1, $test->candidates()->count());
        $this->assertEquals('succeeded', $order->fresh()->status);
    }
}
