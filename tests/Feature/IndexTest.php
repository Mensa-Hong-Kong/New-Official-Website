<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function testViewCanShow(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
