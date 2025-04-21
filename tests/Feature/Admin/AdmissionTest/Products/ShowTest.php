<?php

namespace Tests\Feature\Admin\AdmissionTest\Products;

use App\Models\AdmissionTestProduct;
use App\Models\ModulePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private $product;

    protected function setUp(): void
    {
        parent::setup();
        $this->product = AdmissionTestProduct::factory()->create();
    }

    public function test_have_no_login()
    {
        $response = $this->get(
            route(
                'admin.admission-test.products.show',
                ['product' => $this->product]
            )
        );
        $response->assertRedirectToRoute('login');
    }

    public function test_have_no_edit_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(
            ModulePermission::inRandomOrder()
                ->whereNot('name', 'Edit:Admission Test')
                ->first()
                ->name
        );
        $response = $this->actingAs($user)->get(
            route(
                'admin.admission-test.products.show',
                ['product' => $this->product]
            )
        );
        $response->assertForbidden();
    }

    public function test_product_not_exists()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->get(
            route(
                'admin.admission-test.products.show',
                ['product' => 0]
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('Edit:Admission Test');
        $response = $this->actingAs($user)->get(
            route(
                'admin.admission-test.products.show',
                ['product' => $this->product]
            )
        );
        $response->assertSuccessful();
    }
}
