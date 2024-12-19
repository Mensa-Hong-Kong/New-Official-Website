<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_view_can_show(): void
    {
        $response = $this->get(route('index'));
        $response->assertOk();
    }
}
