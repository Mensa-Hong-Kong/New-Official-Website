<?php

namespace Tests\Feature\Pages;

use App\Models\AdmissionTest;
use App\Models\AdmissionTestOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdmissionTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_case_when_user_have_no_login()
    {
        $response = $this->get(route('admission-tests.index'));
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Index')
                    ->where('isReschedule', false)
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('last_admission_test')
                                ->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_user_have_no_scheduled_admission_test_and_unused_quota_admission_test_order()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admission-tests.index'));
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Index')
                    ->where('isReschedule', false)
                    ->has(
                        'user', function (Assert $page) {
                            $page->whereNull('last_admission_test')
                                ->whereNull('has_unused_quota_admission_test_order')
                                ->etc();
                        }
                    );
            }
        );
    }

    public function test_happy_case_when_user_have_scheduled_admission_test_and_unused_quota_admission_test_order()
    {
        $user = User::factory()->create();
        AdmissionTestOrder::factory()->state([
            'user_id' => $user->id,
            'status' => 'succeeded',
        ])->create();
        $test = AdmissionTest::factory()->state(['is_free' => true])->create();
        $test->candidates()->attach($user->id);
        $response = $this->actingAs($user)->get(route('admission-tests.index'));
        $response->assertSuccessful();
        $response->assertInertia(
            function (Assert $page) {
                $page->component('AdmissionTests/Index')
                    ->where('isReschedule', true)
                    ->has(
                        'user', function (Assert $page) {
                            $page->has('last_admission_test.id')
                                ->has('has_unused_quota_admission_test_order.quota_expired_on')
                                ->etc();
                        }
                    );
            }
        );
    }
}
