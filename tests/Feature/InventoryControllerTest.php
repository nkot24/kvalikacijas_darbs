<?php

namespace Tests\Feature;

use App\Http\Controllers\InventoryController;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and log in a user for all tests (fixes 302/401)
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_scan_view_is_accessible(): void
    {
        $response = $this->get(action([InventoryController::class, 'scanView']));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.scan');
    }

    public function test_handle_scan_returns_404_if_product_not_found(): void
    {
        $response = $this->postJson(action([InventoryController::class, 'handleScan']), [
            'barcode' => 'NON_EXISTENT',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'ok'      => false,
                'message' => 'Produkts ar doto svītrkodu nav atrasts.',
            ]);
    }

    public function test_handle_scan_returns_product_if_found(): void
    {
        $product = Product::factory()->create([
            'svitr_kods' => '1234567890',
        ]);

        $response = $this->postJson(action([InventoryController::class, 'handleScan']), [
            'barcode' => '1234567890',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'ok'      => true,
                'message' => 'Produkts atrasts.',
                'product' => [
                    'id'         => $product->id,
                    'nosaukums'  => $product->nosaukums,
                    'svitr_kods' => $product->svitr_kods,
                ],
            ]);
    }

    public function test_store_transfer_creates_record_and_returns_json(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(action([InventoryController::class, 'storeTransfer']), [
            'product_id' => $product->id,
            'qty'        => 5,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'ok'      => true,
                'message' => 'Pārvietošanas ieraksts pievienots.',
            ])
            ->assertJsonStructure([
                'transfer' => ['id'],
            ]);

        $this->assertDatabaseHas('inventory_transfers', [
            'product_id' => $product->id,
            'qty'        => 5,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_transfer_index_lists_and_filters_transfers(): void
    {
        $product1 = Product::factory()->create([
            'nosaukums'  => 'kaste',
            'svitr_kods' => '111',
        ]);

        $product2 = Product::factory()->create([
            'nosaukums'  => 'Sula',
            'svitr_kods' => '222',
        ]);

        InventoryTransfer::factory()->create([
            'product_id' => $product1->id,
            'qty'        => 1,
            'accounted'  => false,
        ]);

        InventoryTransfer::factory()->create([
            'product_id' => $product2->id,
            'qty'        => 2,
            'accounted'  => true,
        ]);

        // Important: query params must be in the URL, not the 2nd param (headers)
        $url = action([InventoryController::class, 'transferIndex'], [
            'q'                  => 'kaste',
            'only_not_accounted' => 1,
        ]);

        $response = $this->get($url);

        $response
            ->assertStatus(200)
            ->assertViewIs('inventory.transfers')
            ->assertViewHas('transfers');

        /** @var \Illuminate\Pagination\LengthAwarePaginator $transfers */
        $transfers = $response->viewData('transfers');

        // Only the "kaste" + accounted = false record should match
        $this->assertCount(1, $transfers);
        $this->assertTrue($transfers->first()->product->is($product1));
    }

}
