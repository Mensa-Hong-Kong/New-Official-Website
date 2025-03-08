<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_case()
    {
        $response = $this->get(route('admission-tests.index'));
        $response->assertSuccessful();
    }
}
