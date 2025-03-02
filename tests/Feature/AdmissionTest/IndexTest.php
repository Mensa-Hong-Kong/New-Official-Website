<?php

namespace Tests\Feature\AdmissionTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_case()
    {
        $response = $this->get(route('admission-tests.index'));
        $response->assertSuccessful();
    }
}
