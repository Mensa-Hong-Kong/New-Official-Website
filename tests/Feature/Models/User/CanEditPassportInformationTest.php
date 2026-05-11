<?php

namespace Tests\Feature\Models\User;

use App\Models\AdmissionTest;
use App\Models\NationalMensa;
use App\Models\OtherPaymentGateway;
use App\Models\QualifyingTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanEditPassportInformationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_edit_passport_information_when_user_have_no_any_admission_test_and_prior_evidence_order_and_member_transfer(): void
    {
        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_has_future_admission_test_before_more_than_2_hours(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->addHours(3),
            'expect_end_at' => now()->addHours(4),
        ]);
        $test->candidates()->attach($this->user);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_has_future_admission_test_before_less_than_2_hours_and_no_update_present_status(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->addHours(1),
            'expect_end_at' => now()->addHours(2),
        ]);
        $test->candidates()->attach($this->user);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_has_future_admission_test_before_less_than_2_hours_and_update_present_status_to_absent(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->addHours(1),
            'expect_end_at' => now()->addHours(2),
        ]);
        $test->candidates()->attach($this->user, ['is_present' => false]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_proctor_have_no_update_present_status_and_after_testing_at_before_2_hours(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->subHours(2)->subMinutes(30),
            'expect_end_at' => now()->subMinutes(30),
        ]);
        $test->candidates()->attach($this->user);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_absent_admission_test(): void
    {
        $test = AdmissionTest::factory()->create([
            'testing_at' => now()->subHours(3),
            'expect_end_at' => now()->subHour(),
        ]);
        $test->candidates()->attach($this->user, ['is_present' => false]);
        $this->assertTrue($this->user->canEditPassportInformation);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_in_progress_prior_evidence_order(): void
    {
        $this->user->priorEvidenceOrders()->create([
            'status' => 'pending',
            'expired_at' => now()->addHour(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_expired_prior_evidence_order(): void
    {
        $this->user->priorEvidenceOrders()->create([
            'status' => fake()->randomElement(['pending', 'canceled', 'failed']),
            'expired_at' => now()->subSecond(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_has_prior_evidence_order_with_succeeded_status_and_no_result_and_does_not_refund_and_return(): void
    {
        $this->user->priorEvidenceOrders()->create([
            'status' => 'succeeded',
            'expired_at' => now()->addHour(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_has_prior_evidence_order_with_succeeded_status_and_no_result_and_the_order_returned(): void
    {
        $this->user->priorEvidenceOrders()->create([
            'status' => 'full refunded',
            'expired_at' => now()->subSecond(),
            'is_returned' => true,
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_has_prior_evidence_order_with_succeeded_status_and_has_result_but_in_progress_and_does_not_refund_and_return(): void
    {
        $order = $this->user->priorEvidenceOrders()->create([
            'status' => 'succeeded',
            'expired_at' => now()->subSecond(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $order->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
        ]);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_has_prior_evidence_order_with_succeeded_status_and_has_result_but_in_progress_and_the_order_returned(): void
    {
        $order = $this->user->priorEvidenceOrders()->create([
            'status' => 'full refunded',
            'expired_at' => now()->subSecond(),
            'is_returned' => true,
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $order->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_rejected_prior_evidence(): void
    {
        $order = $this->user->priorEvidenceOrders()->create([
            'status' => 'succeeded',
            'expired_at' => now()->subSecond(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $order->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
            'is_accepted' => false,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_only_has_accepted_prior_evidence_and_have_no_return_the_order(): void
    {
        $order = $this->user->priorEvidenceOrders()->create([
            'status' => 'succeeded',
            'expired_at' => now()->subSecond(),
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $order->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
            'is_accepted' => true,
        ]);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_accepted_prior_evidence_and_the_order_is_returned(): void
    {
        $order = $this->user->priorEvidenceOrders()->create([
            'status' => 'full refunded',
            'expired_at' => now()->subSecond(),
            'is_returned' => true,
            'price' => 400,
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);

        $order->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
            'is_accepted' => true,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_has_member_transfer_in_progress(): void
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'membership_number' => fake()->numberBetween(1, 1000000),
        ]);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_can_edit_passport_information_when_user_only_has_rejected_member_transfer(): void
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'membership_number' => fake()->numberBetween(1, 1000000),
            'is_accepted' => false,
        ]);

        $this->assertTrue($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_only_has_accepted_member_transfer(): void
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'membership_number' => fake()->numberBetween(1, 1000000),
            'is_accepted' => true,
        ]);

        $this->assertFalse($this->user->canEditPassportInformation);
    }

    public function test_user_cannot_edit_passport_information_when_user_is_member(): void
    {
        $this->user->member()->create();

        $this->assertFalse($this->user->canEditPassportInformation);
    }
}
