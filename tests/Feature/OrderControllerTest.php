<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Auth like in your ProductControllerTest
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_displays_non_completed_orders(): void
    {
        $nonCompleted = Order::factory()->notCompleted()->count(3)->create();
        $completed    = Order::factory()->completed()->create();

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.index');
        $response->assertViewHas('orders');

        $response->assertSee($nonCompleted->first()->pasutijuma_numurs);
        $response->assertDontSee($completed->pasutijuma_numurs);
    }

    public function test_complete_displays_only_completed_orders(): void
    {
        $completed    = Order::factory()->completed()->count(2)->create();
        $nonCompleted = Order::factory()->notCompleted()->create();

        $response = $this->get(route('orders.complete'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.complete');
        $response->assertViewHas('orders');

        $response->assertSee($completed->first()->pasutijuma_numurs);
        $response->assertDontSee($nonCompleted->pasutijuma_numurs);
    }

    public function test_create_displays_form(): void
    {
        Client::factory()->count(2)->create();
        Product::factory()->count(2)->create();

        $response = $this->get(route('orders.create'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.create');
        $response->assertViewHasAll(['clients', 'products']);
    }

    public function test_store_creates_order_with_existing_client_and_product(): void
    {
        $client  = Client::factory()->create();
        $product = Product::factory()->create();

        $data = [
            'client_id'       => (string) $client->id,
            'klients'         => null,
            'products_id'     => (string) $product->id,
            'produkts'        => null,
            'daudzums'        => 10,
            'izpildes_datums' => now()->addWeek()->toDateString(),
            'prioritāte'      => 'augsta',
            'piezimes'        => 'Test piezīmes',
        ];

        $response = $this->post(route('orders.store'), $data);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order->id));

        $response->assertSessionHas('success', 'Pasūtījums saglabāts veiksmīgi!');

        $this->assertDatabaseHas('orders', [
            'client_id'   => $client->id,
            'klients'     => null,
            'products_id' => $product->id,
            'produkts'    => null,
            'daudzums'    => 10,
        ]);
    }

    public function test_store_creates_one_time_order(): void
    {
        $data = [
            'client_id'       => 'vienreizējs',
            'klients'         => 'Vienreizējais Klients',
            'products_id'     => 'vienreizējs',
            'produkts'        => 'Speciāls Produkts',
            'daudzums'        => 5,
            'izpildes_datums' => now()->addDays(3)->toDateString(),
            'prioritāte'      => 'normāla',
            'piezimes'        => 'One-time order',
        ];

        $response = $this->post(route('orders.store'), $data);
        $order = Order::latest()->first();


        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('success', 'Pasūtījums saglabāts veiksmīgi!');

        $this->assertDatabaseHas('orders', [
            'client_id'   => null,
            'klients'     => 'Vienreizējais Klients',
            'products_id' => null,
            'produkts'    => 'Speciāls Produkts',
            'daudzums'    => 5,
        ]);
    }

    public function test_show_displays_single_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.show');
        $response->assertViewHas('order', function ($o) use ($order) {
            return $o->id === $order->id;
        });
    }

    public function test_edit_displays_edit_form(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('orders.edit', $order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.edit');
        $response->assertViewHasAll(['order', 'clients', 'products']);
    }

    public function test_update_updates_order_fields(): void
    {
        $order = Order::factory()->notCompleted()->create();

        $data = [
            'client_id'       => null,
            'klients'         => 'Jauns Klients',
            'products_id'     => null,
            'produkts'        => 'Jauns Produkts',
            'daudzums'        => 20,
            'izpildes_datums' => now()->addDays(10)->toDateString(),
            'prioritāte'      => 'zema',
            'statuss'         => 'ražošanā',
            'piezimes'        => 'Atjaunināts tests',
        ];

        $response = $this->put(route('orders.update', $order), $data);

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('success', 'Pasūtījums atjaunināts veiksmīgi!');

        $this->assertDatabaseHas('orders', [
            'id'          => $order->id,
            'client_id'   => null,
            'klients'     => 'Jauns Klients',
            'products_id' => null,
            'produkts'    => 'Jauns Produkts',
            'daudzums'    => 20,
            'prioritāte'  => 'zema',
            'statuss'     => 'ražošanā',
        ]);
    }

    public function test_it_can_delete_an_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->delete(route('orders.destroy', $order));

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success', 'Pasūtījums dzēsts veiksmīgi!');

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }

    public function test_print_displays_print_view(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('orders.print', $order));

        $response->assertStatus(200);
        $response->assertViewIs('orders.print');
        $response->assertViewHas('order');
    }
}
