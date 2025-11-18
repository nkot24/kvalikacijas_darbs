<?php

namespace Tests\Feature;

use App\Models\OrderList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderListControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_displays_active_orders(): void
    {
        $user = User::factory()->create();

        // Active orders (let DB default status handle it)
        $active = OrderList::factory()->count(2)->create();

        // Completed orders (should NOT be in index if active() scope excludes them)
        $completed = OrderList::factory()->completed()->count(2)->create();

        $this->actingAs($user);

        $response = $this->get(route('orderList.index'));

        $response->assertStatus(200);

        foreach ($active as $order) {
            $response->assertSeeText($order->name);
        }

        // Optionally assert that at least one completed order is NOT visible
        $response->assertDontSeeText($completed->first()->name);
    }

    #[Test]
    public function completed_displays_only_completed_orders_and_can_be_searched(): void
    {
        $user = User::factory()->create();

        // Completed with specific supplier
        $match = OrderList::factory()
            ->completed()
            ->create([
                'name'          => 'Special Item',
                'supplier_name' => 'Mana Firma',
            ]);

        // Another completed
        $otherCompleted = OrderList::factory()
            ->completed()
            ->create([
                'name'          => 'Cits Produkts',
                'supplier_name' => 'Cita Firma',
            ]);

        // Non-completed (just default factory, no explicit status)
        OrderList::factory()->create();

        $this->actingAs($user);

        // Without search term – should see both completed
        $response = $this->get(route('orderList.completed'));
        $response->assertStatus(200);
        $response->assertSeeText('Special Item');
        $response->assertSeeText('Cits Produkts');

        // With search term – only the matching supplier/order
        $response = $this->get(route('orderList.completed', ['q' => 'Mana Firma']));
        $response->assertStatus(200);
        $response->assertSeeText('Special Item');
        $response->assertDontSeeText('Cits Produkts');
    }

    #[Test]
    public function create_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('orderList.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function store_creates_order_without_photo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'name'     => 'Jauns Iepirkums',
            'quantity' => 5,
        ];

        $response = $this->post(route('orderList.store'), $data);

        $response->assertRedirect(route('orderList.index'));
        $this->assertDatabaseHas('order_list', [
            'name'       => 'Jauns Iepirkums',
            'quantity'   => 5,
            'created_by' => $user->id,
        ]);
    }

    #[Test]
    public function store_validates_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('orderList.store'), []);

        $response->assertSessionHasErrors(['name', 'quantity']);
    }

    #[Test]
    public function store_creates_order_with_photo_and_saves_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('public');

        $photo = UploadedFile::fake()->image('photo.jpg', 1000, 1000);

        $data = [
            'name'     => 'Iepirkums ar bildi',
            'quantity' => 3,
            'photo'    => $photo,
        ];

        $response = $this->post(route('orderList.store'), $data);

        $response->assertRedirect(route('orderList.index'));

        $order = OrderList::first();

        $this->assertNotNull($order->photo_path);
        Storage::disk('public')->assertExists($order->photo_path);
    }

    #[Test]
    public function edit_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = OrderList::factory()->create();

        $response = $this->get(route('orderList.edit', $order));

        $response->assertStatus(200);
        $response->assertSeeText($order->name);
    }

    #[Test]
    public function update_updates_order_fields_without_photo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = OrderList::factory()->create([
            'supplier_name' => null,
            'ordered_at'    => null,
            'expected_at'   => null,
        ]);

        $data = [
            'supplier_name' => 'Jaunais piegādātājs',
            'ordered_at'    => '2025-01-01',
            'expected_at'   => '2025-01-10',
            'arrived_at'    => null,
        ];

        $response = $this->put(route('orderList.update', $order), $data);

        $response->assertRedirect(route('orderList.index'));

        $order->refresh();

        $this->assertSame('Jaunais piegādātājs', $order->supplier_name);
        $this->assertEquals('2025-01-01', $order->ordered_at?->toDateString());
        $this->assertEquals('2025-01-10', $order->expected_at?->toDateString());
    }

    #[Test]
    public function update_replaces_photo_if_new_one_uploaded(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('public');

        $oldPhotoPath = 'purchases/old-photo.jpg';
        Storage::disk('public')->put($oldPhotoPath, 'dummy');

        $order = OrderList::factory()->create([
            'photo_path' => $oldPhotoPath,
        ]);

        $newPhoto = UploadedFile::fake()->image('new.jpg', 800, 800);

        $data = [
            'supplier_name' => 'Piegādātājs',
            'ordered_at'    => '2025-01-01',
            'expected_at'   => '2025-01-02',
            'arrived_at'    => null,
            'photo'         => $newPhoto,
        ];

        $response = $this->put(route('orderList.update', $order), $data);

        $response->assertRedirect(route('orderList.index'));

        $order->refresh();

        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($order->photo_path);
    }

    #[Test]
    public function destroy_deletes_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $order = OrderList::factory()->create();

        $response = $this->delete(route('orderList.destroy', $order));

        $response->assertRedirect(route('orderList.index'));

        $this->assertSoftDeleted('order_list', [
            'id' => $order->id,
        ]);
    }

}

