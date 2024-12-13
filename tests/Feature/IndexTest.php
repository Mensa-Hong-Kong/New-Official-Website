<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexTest extends TestCase
{
    public function testViewCanShow(): void
    {
        $response = $this->get(route('index'));

        $response->assertOk();
    }
}
