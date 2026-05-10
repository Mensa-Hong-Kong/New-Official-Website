<?php

namespace Tests\Feature\Admin\AdmissionTest\Types;

use App\Models\AdmissionTestType;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDisplayOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private AdmissionTestType $type1;

    private AdmissionTestType $type2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('Edit:Admission Test');
        $this->type1 = AdmissionTestType::factory()->create();
        $this->type2 = AdmissionTestType::factory()->create();
    }

    public function test_have_no_login(): void
    {
        $response = $this->putJson(
            route('admin.admission-test.types.display-order.update'),
            [
                'display_order' => AdmissionTestType::inRandomOrder()
                    ->get('display_order')
                    ->pluck('display_order')
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
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            [
                'display_order' => AdmissionTestType::inRandomOrder()
                    ->get('display_order')
                    ->pluck('display_order')
                    ->toArray(),
            ]
        );
        $response->assertForbidden();
    }

    public function test_missing_display_order(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update')
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_is_not_array(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            ['display_order' => 'abc']
        );
        $response->assertInvalid(['display_order' => 'The display order field must be an array.']);
    }

    public function test_display_order_size_is_not_match(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            ['display_order' => [$this->type1->id]]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_display_order_have_no_value(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            ['display_order' => []]
        );
        $response->assertInvalid(['display_order' => 'The display order field is required.']);
    }

    public function test_display_order_value_is_not_integer(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            [
                'display_order' => ['abc', $this->type1->id],
            ]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field must be an integer.']);
    }

    public function test_display_order_value_is_duplicate(): void
    {
        AdmissionTestType::factory()->create();
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            [
                'display_order' => [
                    $this->type1->id,
                    $this->type2->id,
                    $this->type1->id,
                ],
            ]
        );
        $response->assertInvalid(['display_order.0' => 'The display_order.0 field has a duplicate value.']);
    }

    public function test_display_order_value_is_not_exists_on_database(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            [
                'display_order' => [0, $this->type2->id],
            ]
        );
        $response->assertInvalid(['message' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.']);
    }

    public function test_happy_case(): void
    {
        $IDs = [
            $this->type2->id,
            $this->type1->id,
        ];
        $response = $this->actingAs($this->user)->putJson(
            route('admin.admission-test.types.display-order.update'),
            ['display_order' => $IDs]
        );
        $response->assertSuccessful();
        $response->assertJson([
            'success' => 'The display order update success!',
            'display_order' => $IDs,
        ]);
    }
}
