<?php

namespace Tests\Feature\Models\User;

use App\Models\AdmissionTest;
use App\Models\NationalMensa;
use App\Models\OtherPaymentGateway;
use App\Models\QualifyingTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipQualificationTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setup();
        $this->user = User::factory()->create();
    }

    public function test_user_have_no_any_relation_membership_qualification_record()
    {
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_failed_admission_test_record()
    {
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach($this->user->id, ['is_present' => true]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_passed_admission_test_record()
    {
        $test = AdmissionTest::factory()->create();
        $test->candidates()->attach(
            $this->user->id,
            [
                'is_present' => true,
                'is_pass' => true,
            ]
        );
        $this->assertTrue($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_waiting_handle_member_transfer_record()
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'member_number' => fake()->numberBetween(1, 10000),
        ]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_rejected_member_transfer_record()
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'member_number' => fake()->numberBetween(1, 10000),
            'is_accepted' => false,
        ]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_accepted_member_transfer_record()
    {
        $this->user->memberTransfers()->create([
            'type' => fake()->randomElement(['in', 'guest', 'out']),
            'national_mensa_id' => NationalMensa::inRandomOrder()->first()->id,
            'member_number' => fake()->numberBetween(1, 10000),
            'is_accepted' => true,
        ]);
        $this->assertTrue($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_waiting_handle_prior_evidence_order()
    {
        $this->user->priorEvidenceOrders()->create([
            'price' => 400,
            'status' => 'succeeded',
            'expired_at' => now(),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_waiting_nsp_result_prior_evidence_order()
    {
        $priorEvidenceOrder = $this->user->priorEvidenceOrders()->create([
            'price' => 400,
            'status' => 'succeeded',
            'expired_at' => now(),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $priorEvidenceOrder->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => fake()->numberBetween(0, 180),
            'percent_of_group' => fake()->randomFloat(2, 0, 99.99),
        ]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_failed_prior_evidence_result()
    {
        $priorEvidenceOrder = $this->user->priorEvidenceOrders()->create([
            'price' => 400,
            'status' => 'succeeded',
            'expired_at' => now(),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $priorEvidenceOrder->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => 100,
            'percent_of_group' => 50,
            'is_pass' => false,
        ]);
        $this->assertFalse($this->user->hasQualificationOfMembership);
    }

    public function test_user_only_has_passed_prior_evidence_result()
    {
        $priorEvidenceOrder = $this->user->priorEvidenceOrders()->create([
            'price' => 400,
            'status' => 'succeeded',
            'expired_at' => now(),
            'gateway_type' => OtherPaymentGateway::class,
            'gateway_id' => OtherPaymentGateway::inRandomOrder()->first()->id,
        ]);
        $priorEvidenceOrder->result()->create([
            'test_id' => QualifyingTest::inRandomOrder()->first()->id ?? QualifyingTest::create(['name' => fake()->word()])->id,
            'taken_on' => fake()->date(),
            'score' => 131,
            'percent_of_group' => 2,
            'is_pass' => true,
        ]);
        $this->assertTrue($this->user->hasQualificationOfMembership);
    }
}
