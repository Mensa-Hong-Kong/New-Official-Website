<?php

namespace Tests\Feature\Schedules;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\User;
use App\Schedules\GenerateAdmissionTestSeatNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GenerateAdmissionTestSeatNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setup();
        User::factory()->create();
        Notification::fake();
    }

    public function test_has_no_test()
    {
        $this->assertFalse(AdmissionTest::exists());
        $this->assertFalse(AdmissionTestHasCandidate::exists());
        (new GenerateAdmissionTestSeatNumber)();
    }

    public function test_has_one_admission_test_but_not_on_today_and_have_no_candidate()
    {
        $this->expectNotToPerformAssertions();
        AdmissionTest::factory()->state(['testing_at' => now()->addDay()])->create();
        (new GenerateAdmissionTestSeatNumber)();
    }

    public function test_has_one_admission_test_and_has_candidates_but_not_on_today()
    {
        $test = AdmissionTest::factory()->state(['testing_at' => now()->addDay()])->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $test->candidates()->attach([$user1->id, $user2->id]);
        (new GenerateAdmissionTestSeatNumber)();
        $this->assertNull(
            AdmissionTestHasCandidate::where('test_id', $test->id)
                ->where('user_id', $user1->id)
                ->value('seat_number')
        );
        $this->assertNull(
            AdmissionTestHasCandidate::where('test_id', $test->id)
                ->where('user_id', $user2->id)
                ->value('seat_number')
        );
    }

    public function test_has_one_admission_test_on_today_but_have_no_candidate()
    {
        $this->expectNotToPerformAssertions();
        AdmissionTest::factory()->state(['testing_at' => today()])->create();
        (new GenerateAdmissionTestSeatNumber)();
    }

    public function test_has_one_admission_test_and_has_candidates_on_today()
    {
        $test = AdmissionTest::factory()->state(['testing_at' => today()])->create();
        $test->candidates()->attach([
            User::factory()->create()->id,
            User::factory()->create()->id,
        ]);
        (new GenerateAdmissionTestSeatNumber)();
        $this->assertEquals(
            [1, 2],
            AdmissionTestHasCandidate::where('test_id', $test->id)
                ->orderBy('seat_number')
                ->get('seat_number')
                ->pluck('seat_number')
                ->toArray()
        );
    }

    public function test_has_two_tests_has_candidates_but_one_test_not_on_today()
    {
        $test1 = AdmissionTest::factory()->state(['testing_at' => today()])->create();
        $test1->candidates()->attach([
            User::factory()->create()->id,
            User::factory()->create()->id,
        ]);
        $test2 = AdmissionTest::factory()->state(['testing_at' => now()->addDay()])->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $test2->candidates()->attach([$user1->id, $user2->id]);
        (new GenerateAdmissionTestSeatNumber)();
        $this->assertEquals(
            [1, 2],
            AdmissionTestHasCandidate::where('test_id', $test1->id)
                ->orderBy('seat_number')
                ->get('seat_number')
                ->pluck('seat_number')
                ->toArray()
        );
        $this->assertNull(
            AdmissionTestHasCandidate::where('test_id', $test2->id)
                ->where('user_id', $user1->id)
                ->value('seat_number')
        );
        $this->assertNull(
            AdmissionTestHasCandidate::where('test_id', $test2->id)
                ->where('user_id', $user2->id)
                ->value('seat_number')
        );
    }

    public function test_has_two_tests_has_candidates_on_today()
    {
        $test1 = AdmissionTest::factory()->state(['testing_at' => today()])->create();
        $test1->candidates()->attach([
            User::factory()->create()->id,
            User::factory()->create()->id,
        ]);
        $test2 = AdmissionTest::factory()->state(['testing_at' => today()])->create();
        $test2->candidates()->attach([
            User::factory()->create()->id,
            User::factory()->create()->id,
        ]);
        (new GenerateAdmissionTestSeatNumber)();
        $this->assertEquals(
            [1, 2],
            AdmissionTestHasCandidate::where('test_id', $test1->id)
                ->orderBy('seat_number')
                ->get('seat_number')
                ->pluck('seat_number')
                ->toArray()
        );
        $this->assertEquals(
            [1, 2],
            AdmissionTestHasCandidate::where('test_id', $test2->id)
                ->orderBy('seat_number')
                ->get('seat_number')
                ->pluck('seat_number')
                ->toArray()
        );
    }
}
