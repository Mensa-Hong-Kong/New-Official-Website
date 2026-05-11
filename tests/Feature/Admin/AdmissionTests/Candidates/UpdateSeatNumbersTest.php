<?php

namespace Tests\Feature\Admin\AdmissionTests\Candidates;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSeatNumbersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private AdmissionTest $test;

    private User $candidate1;

    private User $candidate2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test Candidate');
        $this->candidate1 = User::factory()->create();
        $this->candidate2 = User::factory()->create();
        $this->test = AdmissionTest::factory()->create();
        $this->test->candidates()->attach([$this->candidate1->id, $this->candidate2->id]);
    }

    public function test_have_no_login(): void
    {
        $response = $this->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_number' => AdmissionTestHasCandidate::inRandomOrder()
                    ->get('user_id')
                    ->pluck('user_id')
                    ->toArray(),
            ]
        );
        $response->assertUnauthorized();
    }

    public function test_have_no_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test Candidate')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_numbers' => AdmissionTestHasCandidate::inRandomOrder()
                    ->get('user_id')
                    ->pluck('user_id')
                    ->toArray(),
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_seat_numbers(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            )
        );
        $response->assertInvalid(['seat_numbers' => 'The seat numbers field is required.']);
    }

    public function test_seat_numbers_is_not_array(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            ['seat_numbers' => 'abc']
        );
        $response->assertInvalid(['seat_numbers' => 'The seat numbers field must be an array.']);
    }

    public function test_seat_numbers_size_is_not_match(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            ['seat_numbers' => [$this->candidate1->id]]
        );
        $response->assertInvalid(['message' => 'The ID(s) of seat numbers field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_seat_numbers_have_no_value(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            ['seat_numbers' => []]
        );
        $response->assertInvalid(['seat_numbers' => 'The seat numbers field is required.']);
    }

    public function test_seat_numbers_value_is_not_integer(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_numbers' => ['abc', $this->candidate1->id],
            ]
        );
        $response->assertInvalid(['seat_numbers.0' => 'The seat_numbers.0 field must be an integer.']);
    }

    public function test_seat_numbers_value_is_duplicate(): void
    {
        $this->test->candidates()->attach($this->user->id);
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_numbers' => [
                    $this->candidate1->id,
                    $this->candidate2->id,
                    $this->candidate1->id,
                ],
            ]
        );
        $response->assertInvalid(['seat_numbers.0' => 'The seat_numbers.0 field has a duplicate value.']);
    }

    public function test_seat_numbers_value_is_not_exists_on_database(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_numbers' => [0, $this->candidate2->id],
            ]
        );
        $response->assertInvalid(['message' => 'The ID(s) of seat numbers field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route(
                'admin.admission-tests.candidates.seat-numbers.update',
                ['admission_test' => $this->test]
            ),
            [
                'seat_numbers' => [
                    $this->candidate2->id,
                    $this->candidate1->id,
                ],
            ]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The seat numbers update success!',
            'seat_numbers' => [
                1 => $this->candidate2->id,
                2 => $this->candidate1->id,
            ],
        ]);
    }
}
