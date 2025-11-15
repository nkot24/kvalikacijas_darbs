<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a test user for all tests
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_displays_products(): void
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');

        // Check that at least one product name appears in the response
        $response->assertSee($products->first()->nosaukums);
    }

    public function test_it_can_store_a_product(): void
    {
        $data = [
            'svitr_kods' => 1234567890,
            'nosaukums' => 'Test Product',
            'pardosanas_cena' => 9.99,
            'vairumtirdzniecibas_cena' => 7.50,
            'daudzums_noliktava' => 10,
            'svars_neto' => 1.5,
            'nomGr_kods' => 'NOM-001',
            'garums' => 10.5,
            'platums' => 5.3,
            'augstums' => 2.1,
        ];

        $response = $this->post(route('products.store'), $data);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product created successfully.');

        $this->assertDatabaseHas('products', [
            'svitr_kods'   => 1234567890,
            'nosaukums'    => 'Test Product',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $data = [
            // 'svitr_kods' missing
            // 'nosaukums' missing
            'pardosanas_cena' => 9.99,
            'nomGr_kods'      => 'NOM-001',
        ];

        $response = $this
            ->from(route('products.create'))
            ->post(route('products.store'), $data);

        $response->assertSessionHasErrors(['svitr_kods', 'nosaukums']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_it_can_update_a_product(): void
    {
        $product = Product::factory()->create([
            'svitr_kods' => 1111111111,
            'nosaukums'  => 'Old Name',
        ]);

        $data = [
            'svitr_kods' => 1111111111, // keep same to satisfy unique rule
            'nosaukums' => 'New Name',
            'pardosanas_cena' => 19.99,
            'vairumtirdzniecibas_cena' => 15.00,
            'daudzums_noliktava' => 20,
            'svars_neto' => 2.0,
            'nomGr_kods' => 'NOM-002',
            'garums' => 11.5,
            'platums' => 6.0,
            'augstums' => 3.0,
        ];

        $response = $this->put(route('products.update', $product), $data);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product updated successfully.');

        $this->assertDatabaseHas('products', [
            'id'               => $product->id,
            'nosaukums'        => 'New Name',
            'pardosanas_cena'  => 19.99,
        ]);
    }

    public function test_update_validates_unique_svitr_kods(): void
    {
        $product1 = Product::factory()->create([
            'svitr_kods' => 1111111111,
        ]);

        $product2 = Product::factory()->create([
            'svitr_kods' => 2222222222,
        ]);

        $data = [
            'svitr_kods' => 1111111111, // duplicates product1
            'nosaukums' => 'Some Name',
            'pardosanas_cena' => 9.99,
            'vairumtirdzniecibas_cena' => 7.99,
            'daudzums_noliktava' => 5,
            'svars_neto' => 1.0,
            'nomGr_kods' => 'NOM-003',
            'garums' => 10,
            'platums' => 5,
            'augstums' => 2,
        ];

        $response = $this
            ->from(route('products.edit', $product2))
            ->put(route('products.update', $product2), $data);

        $response->assertSessionHasErrors(['svitr_kods']);

        // Make sure product2 still has its original svitr_kods
        $this->assertDatabaseHas('products', [
            'id'         => $product2->id,
            'svitr_kods' => 2222222222,
        ]);
    }

    public function test_it_can_delete_a_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product deleted successfully.');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
