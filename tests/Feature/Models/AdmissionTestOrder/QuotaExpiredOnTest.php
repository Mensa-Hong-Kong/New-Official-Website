<?php

namespace Tests\Feature\Models\AdmissionTestOrder;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotaExpiredOnTest extends TestCase
{
    use RefreshDatabase;

    public function test_quota_validity_months_is_null(): void
    {
        $order = AdmissionTestOrder::factory()->create(['quota_validity_months' => null]);
        $this->assertNull($order->quota_expired_on);
    }

    public function test_order_have_no_attended_tests_and_extend_expired_date_config_is_null(): void
    {
        $order = AdmissionTestOrder::factory()->create(['quota_validity_months' => 3]);
        $expectedExpiredDate = (clone $order->created_at)->addMonths(3)->endOfDay();
        $this->assertTrue($order->quota_expired_on->equalTo($expectedExpiredDate));
    }

    public function test_order_has_attended_tests_and_extend_expired_date_config_is_null(): void
    {
        $order = AdmissionTestOrder::factory()->create([
            'quota_validity_months' => 3,
            'created_at' => now()->subMonths(3),
        ]);
        $test = AdmissionTest::factory()->create(['testing_at' => now()->subMonths(2)]);
        $test->type->update(['interval_month' => 2]);
        $user = User::factory()->create();
        $test->candidates()->attach(
            $user->id,
            [
                'order_id' => $order->id,
                'is_present' => true,
            ]
        );
        $expectedExpiredDate = (clone $test->testing_at)->addMonths(5)->endOfDay();
        $this->assertTrue($order->quota_expired_on->equalTo($expectedExpiredDate));
    }

    public function test_expired_date_config_is_between_extend_expired_date_config_when_after_than_and_to(): void
    {
        $expectedExpiredDate = now()->addDays(20);
        config()->set('app.extendAdmissionTestQuotaExpiredDate', [
            'whenAfterThan' => now()->subDay()->toDateString(),
            'to' => $expectedExpiredDate->toDateString(),
        ]);
        $order = AdmissionTestOrder::factory()->create([
            'quota_validity_months' => 1,
            'created_at' => now()->subMonth()->subDay(),
        ]);
        $this->assertEquals(
            $expectedExpiredDate->endOfDay()->toDateString(),
            $order->quota_expired_on->toDateString()
        );
    }

    public function test_calculated_expired_date_is_not_between_extend_expired_date_config_when_after_than_and_to(): void
    {
        config()->set('app.extendAdmissionTestQuotaExpiredDate', [
            'whenAfterThan' => now()->addDays(10)->toDateString(),
            'to' => now()->addDays(20)->toDateString(),
        ]);

        $order = AdmissionTestOrder::factory()->create(['quota_validity_months' => 1]);

        $expectedExpiredDate = (clone $order->created_at)->addMonths(1)->endOfDay();

        $this->assertTrue($order->quota_expired_on->equalTo($expectedExpiredDate));
    }
}
