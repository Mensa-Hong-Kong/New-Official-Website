<?php

namespace Tests\Feature\Models\AdmissionTestOrder;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasUnusedQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_is_not_paid(): void
    {
        $order = AdmissionTestOrder::factory()->create([
            'status' => fake()->randomElement(['pending', 'failed', 'canceled', 'expired']),
        ]);

        $this->assertFalse($order->has_unused_quota);
    }

    public function test_returned_quota_plus_attended_tests_count_is_equal_or_more_than_quota(): void
    {
        $order = AdmissionTestOrder::factory()->state([
            'status' => 'succeeded',
            'quota' => 3,
            'returned_quota' => 2,
        ])->create();

        $test = AdmissionTest::factory()->create();

        $test->candidates()->attach(
            $order->user_id,
            [
                'order_id' => $order->id,
                'is_present' => true,
            ]
        );

        $this->assertFalse($order->has_unused_quota);
    }

    public function test_quota_is_expired(): void
    {
        $order = AdmissionTestOrder::factory()->state([
            'status' => 'succeeded',
            'quota' => 3,
            'quota_validity_months' => 1,
            'created_at' => now()->subMonths(2),
        ])->create();

        $this->assertFalse($order->has_unused_quota);
    }

    public function test_order_status_is_paid_and_returned_quota_plus_attended_tests_count_is_less_than_quota_and_quota_is_not_expired(): void
    {
        $order = AdmissionTestOrder::factory()->state([
            'status' => 'succeeded',
            'quota' => 3,
            'returned_quota' => 1,
            'quota_validity_months' => 1,
            'created_at' => now()->subDays(20),
        ])->create();
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach(
            $order->user_id,
            [
                'order_id' => $order->id,
                'is_present' => true,
            ]
        );
        $this->assertTrue($order->has_unused_quota);
    }
}
