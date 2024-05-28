<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_index_accessible()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
