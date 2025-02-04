<?php

namespace Tests\Feature\Pages;

use App\Models\CustomPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_is_not_exist()
    {
        $response = $this->get(
            route(
                'custom-page',
                ['pathname' => 'abc/xyz']
            )
        );
        $response->assertNotFound();
    }

    public function test_happy_case()
    {
        CustomPage::factory()->state(['pathname' => 'abc/xyz'])->create();
        $response = $this->get(
            route(
                'custom-page',
                ['pathname' => 'abc/xyz']
            )
        );
        $response->assertSuccessful();
    }
}
