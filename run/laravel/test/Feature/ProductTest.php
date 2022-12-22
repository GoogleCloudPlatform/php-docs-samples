<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    public function test_product_index()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    public function test_product_create_page()
    {
        $response = $this->get('/products/create');

        $response->assertStatus(200);
    }

    public function test_create_product()
    {
        $response = $this->followingRedirects()->post('/products', [
            'name' => 'Test Product',
            'description' => 'Test Description'
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseCount('products', 1);
    }

    public function test_database_seed()
    {
        $this->artisan('db:seed');

        $response = $this->get('/products');
        $response->assertStatus(200);
    }
}
