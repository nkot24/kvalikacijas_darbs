<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvansaRekinsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** ----------------------------------------------------------
     *  Helper to authenticate a user
     * -----------------------------------------------------------*/
    private function actingAsUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    /** ----------------------------------------------------------
     * 1. Test: Create view loads
     * -----------------------------------------------------------*/
    public function test_create_page_loads()
    {
        $this->actingAsUser();

        $response = $this->get('/avansa-rekini/create');

        $response->assertStatus(200)
                 ->assertViewIs('avansa_rekini.create')
                 ->assertViewHas('clients');
    }

    /** ----------------------------------------------------------
     * 2. Test: Fetch orders for normal client
     * -----------------------------------------------------------*/
    public function test_get_orders_for_client()
    {
        $this->actingAsUser();

        $client = Client::factory()->create();
        $product = Product::factory()->create([
            'pardosanas_cena' => 10.00
        ]);

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'products_id' => $product->id,
        ]);

        $response = $this->get("/api/orders/by-client/{$client->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $order->id,
                 ]);
    }

    /** ----------------------------------------------------------
     * 3. Test: Fetch orders for one-time client
     * -----------------------------------------------------------*/
    public function test_get_orders_for_one_time_client()
    {
        $this->actingAsUser();

        $product = Product::factory()->create([
            'pardosanas_cena' => 9.99
        ]);

        $order = Order::factory()->create([
            'client_id' => null,
            'products_id' => $product->id,
        ]);

        $response = $this->get('/api/orders/by-client/one_time');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $order->id
                 ]);
    }

    /** ----------------------------------------------------------
     * 4. Test: Generate PDF with valid data
     * -----------------------------------------------------------*/
    public function test_generate_invoice_successfully()
    {
        $this->actingAsUser();

        $client = Client::factory()->create();

        $product = Product::factory()->create([
            'pardosanas_cena' => 12.50
        ]);

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'products_id' => $product->id,
            'daudzums' => 2,
        ]);

        $response = $this->post('/avansa-rekini/generate', [
            'client_id' => $client->id,
            'orders' => [$order->id],
            'add_pvn' => '1',
            'order_custom_total' => [],
            'use_advance' => '0',
            'action' => 'download'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }




    /** ----------------------------------------------------------
     * 5. Test: One-time client invoice generation works
     * -----------------------------------------------------------*/
    public function test_generate_invoice_for_one_time_client()
    {
        $this->actingAsUser();

        $product = Product::factory()->create([
            'pardosanas_cena' => 5
        ]);

        $order = Order::factory()->create([
            'client_id' => null,
            'products_id' => $product->id,
            'daudzums' => 3,
        ]);

        $response = $this->post('/avansa-rekini/generate', [
            'client_id' => 'one_time',
            'orders' => [$order->id],
            'add_pvn' => '0',
            'order_custom_total' => [],
            'use_advance' => '0'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** ----------------------------------------------------------
     * 6. Test: generate WITH advance percent
     * -----------------------------------------------------------*/
    public function test_generate_invoice_with_advance_payment()
    {
        $this->actingAsUser();

        $client = Client::factory()->create();

        $product = Product::factory()->create([
            'pardosanas_cena' => 20,
        ]);

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'products_id' => $product->id,
            'daudzums' => 1,
        ]);

        $response = $this->post('/avansa-rekini/generate', [
            'client_id' => $client->id,
            'orders' => [$order->id],
            'add_pvn' => '1',
            'order_custom_total' => [],
            'use_advance' => '1',
            'advance_percent' => 50,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
